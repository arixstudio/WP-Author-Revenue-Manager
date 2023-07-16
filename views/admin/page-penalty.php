<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Get author id
$author_id = get_current_user_id();

// Set params
$params = array(
    'adjustment' => 'penalty'
);

// Append url params
$params = array_merge( $params, arm_get_url_params( $_GET, 'page'));

// Get penalty
$penalty = arm_get_adjustments( $params );

// If penalty id does not exist
if (!$penalty || ( !current_user_can('administrator') && !arm_is_accessible( $author_id, 'adjustments', $_GET['id'] )))
    wp_redirect( admin_url('admin.php?page=arm-penalties') );

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Penalty', 'rxarm'); ?>
            </h3>
            <p class="arm-text-lightgrey">
                <strong><?php echo arm_get_user_fullname( $penalty->author_id ) ?></strong> - 
                <?php echo arm_handle_datetime($penalty->created_at, false) ?>
            </p>
        </div>
        <div class="col-md-9 col-sm-12">
            <div class="col-md-4 col-sm-12 float-md-end">
                <div class="row">
                    <div class="col-6 text-end"><i>#<?php echo $penalty->id ?></i></div>
                    <div class="col-6 text-end"><b><i><?php echo $penalty->is_accounted == '0' ? __( 'Not accounted','rxarm') : __( 'Accounted','rxarm') ?></i></b></div>
                </div>
            </div>                
        </div>
    </div>
    
    <div class="arm-content">
        <!-- Adjustments -->
        <?php 
            arm_render_adjustment( $penalty );
        ?>
    </div>
</div>
