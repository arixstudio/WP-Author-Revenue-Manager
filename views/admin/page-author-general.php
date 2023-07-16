<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load author / general info
 */

// Import resources
arm_enqueue_datepicker();

// Get author
$author = get_userdata( $author_id );

// Update form
function arm_update_form( $author_id )
{
    
    $settings_update_result = array('type'=>'success', 'message'=>__('Information updated successfully.', 'rxarm'));

    try 
    {
        // Validate nonce
        if(!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'arm-nonce-profile' ))
            throw new Exception(__('Security check failure.', 'rxarm'), 1);
    
        // Firstname
        if(!isset($_POST['firstname']) || empty($_POST['firstname']))
            throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('First name', 'rxarm')), 1);             
    
        // Lastname
        if(!isset($_POST['lastname']) || empty($_POST['lastname']))
            throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Last name', 'rxarm')), 1);             
    
        // Email
        if(!isset($_POST['email']) || empty($_POST['email']))
            throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Email', 'rxarm')), 1);             

        // Update user data
        wp_update_user([
            'ID' => $author_id,
            'first_name' => sanitize_text_field( $_POST['firstname'] ),
            'last_name' => sanitize_text_field( $_POST['lastname'] ),
            'user_email' => sanitize_text_field( $_POST['email'] ),
            'display_name' => sanitize_text_field( $_POST['firstname'] ).' '.sanitize_text_field( $_POST['lastname'] ),
        ]);
    
        // Date of birth
        // if(!isset($_POST['date_of_birth']) || empty($_POST['date_of_birth']))
            // throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Date of birth', 'rxarm')), 1);
            arm_update_author_metadata( $author_id, 'date_of_birth', $_POST['date_of_birth']); // Auto sanitize
        
        // Recommender
        if (current_user_can( 'administrator' ))
        {
            // if(!isset($_POST['recommender']) || empty($_POST['recommender']))
                // throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Recommender', 'rxarm')), 1);
                arm_update_author_metadata( $author_id, 'recommender', $_POST['recommender']); // Auto sanitize
        }

        // Locale
        if (current_user_can( 'administrator' ))
        {
            if(!isset($_POST['locale']) || empty($_POST['locale']))
                throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Locale', 'rxarm')), 1);
                arm_update_author_metadata( $author_id, 'locale', $_POST['locale']); // Auto sanitize
        }
    
        // Background
        arm_update_author_metadata( $author_id, 'background', $_POST['background']); // Auto sanitize
    
        // Scope of profession
        arm_update_author_metadata( $author_id, 'scope_of_profession', $_POST['scope_of_profession']); // Auto sanitize
    
        // Address
        arm_update_author_metadata( $author_id, 'address', $_POST['address']); // Auto sanitize
    
        
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

<?php isset( $_POST['update'] ) ? arm_update_form($author_id) : '' ?>

<form class="arm-form" id="arm-author-general" method="POST">
    <div class="row g-4 mb-3">
        <div class="col-md-3">
            <label for="firstname" class="form-label"><?php _e('First name', 'rxarm') ?></label>
            <input type="text" class="form-control" name="firstname" id="firstname" value="<?php echo $author->first_name ?>">
        </div>
        <div class="col-md-3">
            <label for="lastname" class="form-label"><?php _e('Last name', 'rxarm') ?></label>
            <input type="text" class="form-control" name="lastname" id="lastname" value="<?php echo $author->last_name ?>">
        </div>
        <div class="col-md-3">
            <label for="email" class="form-label"><?php _e('Email', 'rxarm') ?></label>
            <input type="text" class="form-control" name="email" id="email" value="<?php echo $author->user_email ?>">
        </div>
        <div class="col-md-3">
            <label for="date_of_birth" class="form-label"><?php _e('Date of birth', 'rxarm') ?></label>
            <input type="text" class="form-control arm-datepicker" name="date_of_birth" id="date_of_birth" value="<?php echo arm_get_author_metadata( $author_id, 'date_of_birth' ) ?>">
        </div>
    </div>
    <div class="row g-4 mb-3">
        <?php if (current_user_can( 'administrator' )) : ?>
        <div class="col-md-3">
            <label for="recommender" class="form-label"><?php _e('Recommender', 'rxarm') ?></label>
            <input type="text" class="form-control" name="recommender" id="recommender" value="<?php echo arm_get_author_metadata( $author_id, 'recommender') ?>">
        </div>
        <?php endif ?>
        <div class="col-md-3">
            <label for="locale" class="form-label"><?php _e('Locale', 'rxarm') ?></label>
            <select id="locale" name="locale" class="form-select" <?php echo !current_user_can( 'administrator' ) ? 'disabled' : '' ?>>
                <option value="" selected><?php _e("Site's default language", 'rxarm') ?></option>
                <option value="en_US" <?php echo arm_get_author_metadata( $author_id, 'locale') == "en_US" ? 'selected' : '' ?>><?php _e('English', 'rxarm') ?></option>
                <option value="fa_IR" <?php echo arm_get_author_metadata( $author_id, 'locale') == "fa_IR" ? 'selected' : '' ?>><?php _e('Persian', 'rxarm') ?></option>
            </select>
        </div>
    </div>
    <div class="row g-4 mt-3">
        <div class="col-md-6">
            <label for="background" class="form-label"><?php _e('Background', 'rxarm') ?></label>
            <textarea class="form-control" name="background" id="background"><?php echo arm_get_author_metadata( $author_id, 'background') ?></textarea>
        </div>
        <div class="col-md-6">
            <label for="scope_of_profession" class="form-label"><?php _e('Scope of profession', 'rxarm') ?></label>
            <textarea class="form-control" name="scope_of_profession" id="scope_of_profession"><?php echo arm_get_author_metadata( $author_id, 'scope_of_profession') ?></textarea>
        </div>
    </div>
    <div class="row g-4 mt-3">
        <div class="col-md-6">
            <label for="address" class="form-label"><?php _e('Address', 'rxarm') ?></label>
            <textarea class="form-control" name="address" id="address"><?php echo arm_get_author_metadata( $author_id, 'address') ?></textarea>
        </div>
    </div>
    <?php wp_nonce_field('arm-nonce-profile', 'nonce',false) ?>
    <?php
        submit_button(__('Update','rxarm'),'btn btn-primary mt-5 me-2', 'update','', false);
    ?>
</form>