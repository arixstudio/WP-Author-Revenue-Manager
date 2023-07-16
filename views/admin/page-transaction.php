<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();
wp_enqueue_script('lightbox-script');

// Get author id
$author_id = get_current_user_id();

// Get transaction
$transaction = arm_get_transactions( arm_get_url_params( $_GET, 'page') );

// If transaction id does not exist
if (!$transaction || ( !current_user_can('administrator') && !arm_is_accessible( $author_id, 'transactions', $_GET['id'] )))
    wp_redirect( admin_url('admin.php?page=arm-transactions') );

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Transaction', 'rxarm'); ?>
            </h3>
            <p class="arm-text-lightgrey">
                <strong><?php echo arm_get_user_fullname( $transaction->author_id ) ?></strong> - 
                <?php echo arm_handle_datetime($transaction->created_at, false) ?>
            </p>
        </div>
        <div class="col-md-9 col-sm-12">
            <div class="col-md-3 col-sm-12 float-md-end">
                <div class="row">
                    <div class="col text-end"><i><?php _e( 'Ref number', 'rxarm'); ?> #<?php echo $transaction->ref_number ? $transaction->ref_number : '----------------------' ?></i></div>
                </div>
            </div>                
        </div>
    </div>
    
    <div class="arm-content">
        <div class="row arm-pill pill-outline mb-4">
            <div class="col-4 text-start">
                <div class="pill-title"><?php _e('Amount', 'rxarm') ?></div>
                <div class="pill-value fw-bold"><?php echo arm_format_price( $transaction->amount , arm_get_author_locale($transaction->author_id) ) ?></div>
            </div>
            <div class="col-4 text-center">
                <div class="pill-title"><?php _e('Method', 'rxarm') ?></div>
                <div class="pill-value"><?php _e(ucfirst($transaction->method), 'rxarm') ?></div>
            </div>
            <div class="col-4 text-end">
                <div class="pill-title"><?php _e('Attachment', 'rxarm') ?></div>
                <div class="pill-value fw-bold"><a href="<?php echo ARM_UPLOADS_URL.$transaction->attachment_id ?>" class="btn btn-primary" data-toggle="lightbox"><i class="fa fa-sticky-note" aria-hidden="true"></i></a></div>
            </div>
        </div>
    </div>
</div>
