<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load author / accounting
 */

// Get currency symbol
$currency_symbol = arm_get_author_currency_symbol( $author_id );

// Get locale
$author_locale = arm_get_author_locale( $author_id );

// Update form
function arm_update_form( $author_id, $author_locale )
{
    
    $settings_update_result = array('type'=>'success', 'message'=>__('Information updated successfully.', 'rxarm'));

    try 
    {
        // Validate nonce
        if(!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'arm-nonce-profile' ))
            throw new Exception(__('Security check failure.', 'rxarm'), 1);
        
        // Revenue per word
        arm_update_author_metadata( $author_id, 'revenue_per_word', $_POST['revenue_per_word']); // Auto sanitize
        
        // Minimum words per month
        arm_update_author_metadata( $author_id, 'minimum_words', $_POST['minimum_words']); // Auto sanitize

        if ($author_locale == 'fa_IR')
        {
            if (!current_user_can('administrator'))
            {
                // Card number
                if(!isset($_POST['card_number']) || empty($_POST['card_number']))
                    throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Card number', 'rxarm')), 1);
                    arm_update_author_metadata( $author_id, 'card_number', $_POST['card_number']); // Auto sanitize
    
                // Sheba number
                if(!isset($_POST['sheba_number']) || empty($_POST['sheba_number']))
                    throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Sheba number', 'rxarm')), 1);
                    arm_update_author_metadata( $author_id, 'sheba_number', $_POST['sheba_number']); // Auto sanitize
            }
        }
        else
        {
            if (!current_user_can('administrator'))
            {
                // SWIFT / BIC
                if(!isset($_POST['swift_bic']) || empty($_POST['swift_bic']))
                    throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('SWIFT / BIC', 'rxarm')), 1);
                    arm_update_author_metadata( $author_id, 'swift_bic', $_POST['swift_bic']); // Auto sanitize

                // IBAN
                if(!isset($_POST['iban_number']) || empty($_POST['iban_number']))
                    throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('IBAN', 'rxarm')), 1);
                    arm_update_author_metadata( $author_id, 'iban_number', $_POST['iban_number']); // Auto sanitize
            }
        }
    
        
    } catch (\Throwable $ex) 
    {
        $settings_update_result = array('type'=>'warning', 'message'=>$ex->getMessage());
    }

    arm_set_alert($settings_update_result['type'], $settings_update_result['message'], 'rxarm'); 

    // Redirection
    arm_redirect( arm_get_url_params($_GET) );
}
?>

<!-- Alert -->
<?php arm_show_alert() ?>

<?php isset( $_POST['update'] ) ? arm_update_form($author_id, $author_locale) : '' ?>

<form class="arm-form" id="arm-author-general" method="POST">
    <div class="row g-4 mb-3">
        <div class="col-md-3">
            <label for="revenue_per_word" class="form-label"><?php _e('Revenue per word', 'rxarm') ?> (<?php _e($currency_symbol, 'rxarm') ?>)</label>
            <input type="text" class="form-control" name="revenue_per_word" id="revenue_per_word" value="<?php echo arm_get_author_revenue_per_word($author_id) ?>" <?php echo !current_user_can( 'administrator' ) ? 'disabled' : '' ?>>
        </div>
        <div class="col-md-3">
            <label for="minimum_words" class="form-label"><?php _e('Minimum words per month', 'rxarm') ?></label>
            <input type="text" class="form-control" name="minimum_words" id="minimum_words" value="<?php echo arm_get_author_metadata( $author_id, 'minimum_words'); ?>" <?php echo !current_user_can( 'administrator' ) ? 'disabled' : '' ?>>
        </div>
        <?php if($author_locale == 'fa_IR') :?>
        <div class="col-md-3">
            <label for="card_number" class="form-label"><?php _e('Card number', 'rxarm') ?></label>
            <input type="text" class="form-control" name="card_number" id="card_number" value="<?php echo arm_get_author_metadata( $author_id, 'card_number') ?>">
        </div>
        <div class="col-md-3">
            <label for="sheba_number" class="form-label"><?php _e('Sheba number', 'rxarm') ?></label>
            <input type="text" class="form-control" name="sheba_number" id="sheba_number" value="<?php echo arm_get_author_metadata( $author_id, 'sheba_number') ?>">
        </div>
        <?php else : ?>
        <div class="col-md-3">
            <label for="swift_bic" class="form-label"><?php _e('SWIFT / BIC', 'rxarm') ?></label>
            <input type="text" class="form-control" name="swift_bic" id="swift_bic" value="<?php echo arm_get_author_metadata( $author_id, 'swift_bic') ?>">
        </div>
        <div class="col-md-3">
            <label for="iban_number" class="form-label"><?php _e('IBAN', 'rxarm') ?></label>
            <input type="text" class="form-control" name="iban_number" id="iban_number" value="<?php echo arm_get_author_metadata( $author_id, 'iban_number') ?>">
        </div>
        <?php endif ?>
    </div>
    <?php wp_nonce_field('arm-nonce-profile', 'nonce',false) ?>
    <?php
        submit_button(__('Update','rxarm'),'btn btn-primary mt-5 me-2', 'update','', false);
    ?>
</form>