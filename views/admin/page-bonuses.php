<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */

// Import resources
wp_enqueue_style('arm-admin-style');
wp_enqueue_script('arm-admin-script');
wp_enqueue_script('validate-script');
wp_enqueue_script('money-format-script');
arm_enqueue_bootstrap();

// Create adjustment button
if (current_user_can( 'administrator'))
    arm_render_floating_button( array('data-bs-toggle'=>"modal", 'data-bs-target'=>"#arm-create-adjustment", 'title'=>__('Submit Bonus', 'rxarm')), '#');

// Set params
$params = array(
    'adjustment' => 'bonus',
    'limit' => arm_get_per_page() ? arm_get_per_page() : 10,
    'offset' => arm_get_pagination_offset(),
);

// Get url params
$url_params = arm_get_url_params( $_GET, 'page');

// Limit accessibility
if(!current_user_can('administrator'))
  $url_params['author'] = get_current_user_id();

// Do action
isset($_GET['action']) && $_GET['action'] == 'repeal' ?  arm_repeal_bonus_form() : '';

// Append url params
$params = array_merge( $params, $url_params );

// Get bonuses
$bonuses = arm_get_adjustments( $params );

// Get bonus records
$records = $bonuses['records'];

// Get total records
$count = $bonuses['count'];


// Prepare table header
$table_header = array(
     __('Author', 'rxarm') => '',
     __('Amount', 'rxarm') => 'amount',
     __('Reason', 'rxarm') => 'reason',
     __('Type', 'rxarm') => 'is_auto',
     __('Issue Date', 'rxarm') => 'created_at',
     __('Status', 'rxarm') => 'is_accounted',
     __('Actions', 'rxarm') => '',
);

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $bonus) 
    {
        // Actions
        $actions = '<a href="'.add_query_arg( 'id', $bonus->id ).'">'.__('View','rxarm').'</a> ';
        if ($bonus->is_accounted != '1' && current_user_can( 'administrator' ))
          $actions .= '<a href="'.add_query_arg( array('action'=>'repeal','id'=> $bonus->id, '_wpnonce'=> wp_create_nonce( 'arm-repeal-bonus' )) ).'" class="needs-confirmation ms-2">'.__('Repeal','rxarm').'</a>';

        $table_rows[] = array(
            arm_get_user_fullname($bonus->author_id),
            arm_format_price( $bonus->amount , arm_get_author_locale($bonus->author_id) ),
            $bonus->reason,
            ($bonus->is_auto == '1' ? __( 'Auto generated', 'rxarm') : __( 'Custom', 'rxarm')),
            arm_handle_datetime($bonus->created_at, false),
            ($bonus->is_accounted == '1' ? __( 'Accounted', 'rxarm') : __( 'Not accounted', 'rxarm')),
            $actions,
        );
    }

// Submit bonus
function arm_form_submit_bonus()
{
  $result = arm_submit_bonus();

  arm_set_alert($result['type'], $result['message'], 'rxarm'); 

  // Redirection
  arm_redirect( arm_get_url_params($_GET) );
}

// Repeal bonus
function arm_repeal_bonus_form()
{
    $result = arm_repeal_bonus();
  
    arm_set_alert($result['type'], $result['message'], 'rxarm'); 
  
    // Redirection
    arm_redirect( arm_get_url_params($_GET, 'action') );
}

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">  
  <div class="arm-header row">
    <div class="col-md-3 col-sm-12">
      <h3 class="arm-font">
        <?php _e( 'Bonuses', 'rxarm'); ?>
      </h3>
    </div>
    <div class="col-md-9 col-sm-12 text-end">
      <?php echo arm_render_admin_filter( array('per_page', 'author', 'date_range' ) ) ?>
    </div>
  </div>
  
  <!-- Alert -->
  <?php arm_show_alert() ?>
  
  <?php isset($_POST['submit']) ?  arm_form_submit_bonus() : '' ?>
    
    <div class="arm-content">
        <?php arm_generate_table( $table_rows, $table_header, $count ); ?>
    </div>

    <!-- Create adjustment Modal -->
    <div id="arm-create-adjustment" class="modal fade" tabindex="-1" aria-labelledby="armModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php _e('Submit Bonus', 'rxarm') ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="arm-bonus-form" class="arm-form" method="POST" action="">
            <div class="modal-body">
                <div class="row g-4 mb-3">
                    <?php         
                      // Get authors
                      $authors = arm_get_authors()['records'];
    
                      $field .= '<div class="col-8 mb-2">
                                      <label for="author" class="form-label">'.__('Author', 'rxarm') .'</label>
                                      <select class="form-select" id="author" name="author" autocomplete="off">';
                                          $field .= '<option value="" disabled selected>'.__('Author', 'rxarm').'</option>';
                                          if (is_array($authors) && !empty($authors))
                                          foreach ($authors as $author) 
                                          {
                                              // $selected = $_GET['author'] == $author->ID ? 'selected' : '';
                                              $field .= '<option value="'.$author->ID.'" '. $selected .'>'.$author->first_name.' '.$author->last_name.' ['.$author->user_login.'] - ('.arm_get_author_currency_symbol($author->ID).')</option>';
                                          }
                      $field .= '</select></div>';
    
                      echo $field;
    
                      ?>
                    <div class="col-md-4">
                        <label for="amount" class="form-label"><?php _e('Amount', 'rxarm') ?></label>
                        <input type="text" class="form-control money-format ltr" id="amount" name="amount">
                    </div>
                </div>
                <div class="row g-4 mb-6">
                    <div class="col">
                        <label for="reason" class="form-label"><?php _e('Reason', 'rxarm') ?></label>
                        <input type="text" class="form-control" id="reason" name="reason">
                    </div>
                </div>
              </div>
              <?php wp_nonce_field('arm-nonce-bonus', 'nonce', false) ?>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Cancel', 'rxarm') ?></button>
                <button type="submit" class="btn btn-primary" name="submit"><?php _e('Submit', 'rxarm') ?></button>
              </div>
            </div>
          </form>
      </div>
    </div>

    <!-- Confirm Modal -->
    <div id="arm-repeal-adjustment" class="modal fade" tabindex="-1" aria-labelledby="armModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php _e('Repeal bonus', 'rxarm') ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h6><?php _e('Are you sure?','rxarm') ?></h6>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Nope', 'rxarm') ?></button>
            <a href="#" class="btn btn-primary arm-action-button"><?php _e('Just do it', 'rxarm') ?></a>
          </div>
      </div>
    </div>
    
</div>



