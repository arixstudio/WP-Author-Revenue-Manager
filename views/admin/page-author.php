<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Get author id
$author_id = $_GET['id'] ? absint($_GET['id']) : get_current_user_id();

// Get author
$author = get_userdata( $author_id )->data;

// Get author locale
$author_locale = arm_get_author_metadata( $author_id, 'locale');

// Set header menu
$header_menu_items = array(
    array(
        'title' => __( 'General info', 'rxarm'),
        'slug' => 'general-info',
        'url' => add_query_arg('section', 'general-info'),
    ),
    array(
        'title' => __( 'Accounting', 'rxarm'),
        'slug' => 'accounting',
        'url' => add_query_arg('section', 'accounting'),
    ),
    array(
        'title' => __( 'Contact options', 'rxarm'),
        'slug' => 'contact-options',
        'url' => add_query_arg('section', 'contact-options'),
    ),
);

// Get view section
$section = sanitize_text_field($_GET['section']);

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php echo arm_get_user_fullname( $author_id ) ?>
            </h3>
            <p class="arm-text-lightgrey"><?php _e( 'Registered at', 'rxarm'); ?>: <?php echo arm_handle_datetime( $author->user_registered , false) ?></p>
        </div>
        <div class="col-md-9 col-sm-12 text-end">
            <?php echo arm_render_admin_header_section_menu( $header_menu_items ) ?>
        </div>
    </div>
    
    <div class="arm-content">
        <?php 
            // Get view
            if (!$section || $section == 'general-info') 
                include_once( ARM_PLUGIN_PATH.'views/admin/page-author-general.php' );
            elseif ($section == 'accounting')
                include_once( ARM_PLUGIN_PATH.'views/admin/page-author-accounting.php' );
            elseif ($section == 'contact-options')
                include_once( ARM_PLUGIN_PATH.'views/admin/page-author-contact.php' );
        ?>
    </div>
</div>
