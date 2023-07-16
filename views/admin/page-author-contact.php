<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load author / accounting
 */

// Import resources
wp_enqueue_script('arm-admin-script');

// Get locale
$locale = current_user_can( 'administrator' ) ? get_locale() : arm_get_author_locale( $author_id );

// Create adjustment button
arm_render_floating_button( array('data-bs-toggle'=>"modal", 'data-bs-target'=>"#arm-add-author-contact-option", 'title'=>__('Add contact option', 'rxarm')), '#');

// Get contact options
$contact_options = arm_get_contact_options();

// Remove contact options which neither required or having data with current user
foreach ($contact_options as $key => $option) 
{
    if ( arm_get_author_metadata($author_id, $option['slug']) == null && $option['is_required'] != 1 )
        unset($contact_options[$key]);
} 

// Update form
function arm_update_form( $author_id )
{
    // Get contact options
    $contact_options = arm_get_contact_options();
    
    $settings_update_result = array('type'=>'success', 'message'=>__('Information updated successfully.', 'rxarm'));

    try 
    {
        // Validate nonce
        if(!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'arm-nonce-profile' ))
            throw new Exception(__('Security check failure.', 'rxarm'), 1);
        

        // Mobile number
        if(!isset($_POST['mobile_number']) || empty($_POST['mobile_number']))
            throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Mobile number', 'rxarm')), 1);
            arm_update_author_metadata( $author_id, 'mobile_number', $_POST['mobile_number']); // Auto sanitize
        
        // Revenue per word
        foreach ($contact_options as $option) 
        {
            arm_update_author_metadata( $author_id, $option['slug'], $_POST[$option['slug']]); // Auto sanitize
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

<?php isset( $_POST['update'] ) ? arm_update_form($author_id, $contact_options) : '' ?>

<form class="arm-form" id="arm-author-general" method="POST">
    <div id="contact-options" class="row g-4 mb-3">
        <!-- Render options -->
        <?php foreach ($contact_options as $option) : ?>
            <div class="col-md-3">
                <label for="<?php echo $option['slug'] ?>" class="form-label"><?php echo $option['title-'.$locale] ?></label>
                <input type="text" class="form-control" name="<?php echo $option['slug'] ?>" id="<?php echo $option['slug'] ?>" value="<?php echo arm_get_author_metadata($author_id, $option['slug']) ?>" <?php echo $option['is_required'] == 1 ? 'required' : '' ?>>
            </div>
        <?php endforeach ?>
    </div>
    <?php wp_nonce_field('arm-nonce-profile', 'nonce',false) ?>
    <?php
        submit_button(__('Update','rxarm'),'btn btn-primary mt-5 me-2', 'update','', false);
    ?>
</form>

<!-- Add contact option Modal -->
<div id="arm-add-author-contact-option" class="modal fade" tabindex="-1" aria-labelledby="armModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title"><?php _e('Add contact option', 'rxarm') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="arm-contact-options-form" class="arm-form">
        <div class="modal-body">
            <div class="row g-4 mb-3">
                <?php         
                    // Get contact options
                    $contact_options = arm_get_contact_options();
                    $field .= '<div class="col-12 mb-2">
                                    <label for="contact_option" class="form-label">'.__('Contact option', 'rxarm') .'</label>
                                    <select class="form-select" id="contact_option" name="contact_option" autocomplete="off" style="max-width: 97%!important">';
                                        $field .= '<option value="" disabled selected>'.__('Select', 'rxarm').'</option>';
                                        if (is_array($contact_options) && !empty($contact_options))
                                        foreach ($contact_options as $option) 
                                        {
                                            $field .= '<option value="'.$option['slug'].'">'.$option['title-'.$locale].'</option>';
                                        }
                    $field .= '</select></div>';

                    echo $field;

                    ?>
            </div>
            <?php wp_nonce_field('arm-nonce-bonus', 'nonce', false) ?>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Cancel', 'rxarm') ?></button>
            <button type="button" class="btn btn-primary" id="add-author-contact-option" data-bs-dismiss="modal"><?php _e('Add', 'rxarm') ?></button>
            </div>
        </div>
        </form>
    </div>
</div>