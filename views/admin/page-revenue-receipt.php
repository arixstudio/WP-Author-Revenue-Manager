<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */

// Import resources
wp_enqueue_style('arm-admin-style');
wp_enqueue_script('arm-admin-script');
wp_enqueue_script('validate-script');
arm_enqueue_bootstrap();

// Get author id
$author_id = get_current_user_id();

// Get revenue receipt
$revenue_receipt = arm_get_revenue_receipts( arm_get_url_params( $_GET, 'page') );

// If revenue id does not exist
if (!$revenue_receipt || ( !current_user_can('administrator') && !arm_is_accessible( $author_id, 'revenue_receipts', $_GET['id'] )))
    wp_redirect( admin_url('admin.php?page=arm-revenues') );

// Get adjustments
$adjustments_ids = unserialize( $revenue_receipt->adjustment_ids );
$adjustments = [];
if(is_array($adjustments_ids) && !empty($adjustments_ids))
    foreach ($adjustments_ids as $adjustments_id) 
    $adjustments[] = arm_get_adjustments( array('id' => $adjustments_id) );

// Submit transaction
function arm_submit_transaction_form( $author_id, $revenue_number, $amount )
{
  $result = arm_submit_transaction( $author_id, $revenue_number, $amount );

  arm_set_alert($result['type'], $result['message'], 'rxarm'); 

  // Redirection
  arm_redirect( arm_get_url_params($_GET) );
}

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Revenue receipt', 'rxarm'); ?>
            </h3>
            <p class="arm-text-lightgrey">
                <strong><?php echo arm_get_user_fullname( $revenue_receipt->author_id ) ?></strong> - 
                <?php echo arm_handle_datetime($revenue_receipt->start_datetime, false) . ' - ' . arm_handle_datetime($revenue_receipt->end_datetime, false) ?>
            </p>
        </div>
        <div class="col-md-9 col-sm-12">
            <div class="col-md-3 col-sm-12 float-end">
                <div class="row">
                    <div class="col-6 text-end"><i>#<?php echo $revenue_receipt->id ?></i></div>
                    <div class="col-6 text-end"><b><i><?php echo __(ucfirst($revenue_receipt->status),'rxarm') ?></i></b></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert -->
    <?php arm_show_alert() ?>
    
    <?php isset($_POST['submit']) ?  arm_submit_transaction_form( absint($revenue_receipt->author_id), absint( $_GET['id'] ), absint($revenue_receipt->total_payable_amount) ) : '' ?>

    <div class="arm-content">
        <!-- Net revenue -->
        <div class="row arm-pill pill-grey mb-4">
            <div class="col-4 text-start">
                <div class="pill-title"><?php _e('Revenue per word', 'rxarm') ?></div>
                <div class="pill-value"><?php echo arm_format_price( $revenue_receipt->revenue_per_word , arm_get_author_locale($revenue_receipt->author_id) ) ?></div>
            </div>
            <div class="col-4 text-center">
                <div class="pill-title"><?php _e('Words written', 'rxarm') ?></div>
                <div class="pill-value"><?php echo number_format($revenue_receipt->written_words) ?></div>
            </div>
            <div class="col-4 text-end">
                <div class="pill-title"><?php _e('Gross revenue', 'rxarm') ?></div>
                <div class="pill-value fw-bold"><?php echo arm_format_price( $revenue_receipt->total_revenue_amount , arm_get_author_locale($revenue_receipt->author_id) ) ?></div>
            </div>
        </div>
        <!-- Adjustments -->
        <?php 
            if (is_array($adjustments) && !empty($adjustments))
            {
                // Bonuses / Penalties
                usort($adjustments, function($a, $b) {return strcmp($a->adjustment, $b->adjustment );});

                // Render adjustments
                foreach ($adjustments as $adjustment)
                    arm_render_adjustment( $adjustment );
            }
        ?>
        <!-- Total -->
        <div class="row arm-pill">
            <div class="col-8 text-left">
                <?php if ( 'unpaid' == $revenue_receipt->status ) : ?>
                    <a href="#" class="btn btn-primary btn-lg pay mt-3" data-bs-toggle="modal" data-bs-target="#arm-create-transaction"><?php _e('Submit payment', 'rxarm') ?></a>                   
                <?php else : 
                        $transaction_id = arm_get_transactions( array('revenue_number'=>$revenue_receipt->id) )->id ?>
                        <a href="<?php echo add_query_arg( array('page'=>'arm-transactions', 'id'=>$transaction_id) , admin_url('admin.php') ) ?>" class="btn btn-primary btn-lg pay mt-3"><?php _e('Payment detail', 'rxarm') ?></a>                   
                <?php endif ?>
            </div>
            <div class="col-4 text-end">
                <div><?php _e('Total payable', 'rxarm') ?></div>
                <div class="pill-value fw-bold"><?php echo arm_format_price( $revenue_receipt->total_payable_amount , arm_get_author_locale($revenue_receipt->author_id) ) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Create transaction (Pay the receipt) Modal -->
    <div id="arm-create-transaction" class="modal fade" tabindex="-1" aria-labelledby="armModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php _e('Submit Transaction', 'rxarm') ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="arm-transaction-form" class="arm-form" method="POST" action="" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="row g-4 mb-3">
                    <div class="col-md-6">
                        <label for="revenue_number" class="form-label"><?php _e('Revenue number', 'rxarm') ?></label>
                        <input type="text" class="form-control" id="revenue_number" name="revenue_number" value="<?php echo absint($_GET['id']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label"><?php _e('Amount', 'rxarm') ?></label>
                        <input type="text" class="form-control" id="amount" name="" value="<?php echo arm_format_price( $revenue_receipt->total_payable_amount , arm_get_author_locale($revenue_receipt->author_id) ) ?>" disabled>
                    </div>
                </div>
                <div class="row g-4 mb-6">
                    <div class="col-md-6">
                        <select class="w-100" name="method" id="method" autocomplete="off">
                            <option value="" disabled selected><?php _e('Select', 'rxarm') ?>...</option>
                            <option value="sheba"><?php _e('Sheba','rxarm') ?></option>
                            <option value="cart transfer"><?php _e('Cart transfer','rxarm') ?></option>
                            <option value="swift"><?php _e('Swift','rxarm') ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text">#</span>
                            <input type="text" class="form-control" name="ref_number" placeholder="<?php _e('Ref number', 'rxarm') ?> (<?php _e('Optional', 'rxarm') ?>)" aria-label="<?php _e('Ref number', 'rxarm') ?>" aria-describedby="">
                        </div>
                    </div>
                </div>
                <div class="row g-4 mt-2 mb-6">
                    <div class="mb-3">
                      <label for="file" class="form-label"><?php _e('Attachment', 'rxarm') ?></label>
                      <input type="file" class="form-control" name="file" id="file" aria-describedby="file-help">
                      <small id="file-help" class="form-text text-muted d-block"><?php _e('Allowed formats: .jpg, .jpeg, .png', 'rxarm') ?></small>
                    </div>
                </div>
              </div>
              <?php wp_nonce_field('arm-nonce-transaction', 'nonce', false) ?>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Cancel', 'rxarm') ?></button>
                <button type="submit" class="btn btn-primary" name="submit"><?php _e('Submit', 'rxarm') ?></button>
              </div>
            </div>
          </form>
      </div>
    </div>
</div>



