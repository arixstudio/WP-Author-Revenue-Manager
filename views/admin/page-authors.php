<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Set params
$params = array(
    'limit' => arm_get_per_page() ? arm_get_per_page() : 10,
    'offset' => arm_get_pagination_offset(),
);

// Get authors
$authors = arm_get_authors( array_merge( $params, arm_get_url_params( $_GET, 'page')) );

// Get records
$records = $authors['records'];

// Get total records
$count = $authors['count'];


// Prepare table header
$table_header = array(
     __('Full name', 'rxarm') => 'user_nicename',
     __('Revenue per word', 'rxarm') => 'revenue_per_word',
     __('Registered at', 'rxarm') => 'registered_at',
     __('Total revenues', 'rxarm') => 'total_revenues',
     __('Actions', 'rxarm') => '',
);

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $author) 
    {
        // Get revenues
        $revenues = arm_get_revenue_receipts( array('author'=>$author->ID) );

        $revenue_records = '';

        if($revenues)
            $revenue_records = $revenues['records'];

        $table_rows[] = array(
            arm_get_user_fullname($author->ID),
            arm_format_price( arm_get_author_revenue_per_word($author->ID) , arm_get_author_locale($author->ID) ),
            arm_handle_datetime($author->data->user_registered, false),
            arm_format_price(is_array($revenue_records) ? array_sum(array_column($revenue_records,'total_payable_amount')) : 0, arm_get_author_locale($author->ID) ),
            '<a href="'.add_query_arg( 'id', $author->ID ).'">'.__('View','rxarm').'</a>'
        );
    }

// Create author button
if (current_user_can( 'create_users'))
    arm_render_floating_button( array('title'=>__('New Author', 'rxarm')), admin_url('user-new.php') );

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Authors', 'rxarm'); ?>
            </h3>
        </div>
        <div class="col-md-9 col-sm-12 text-end">
            <?php echo arm_render_admin_filter( array('per_page', 'author' ) ) ?>
        </div>
    </div>
    
    <div class="arm-content">
        <?php arm_generate_table( $table_rows, $table_header, $count ); ?>
    </div>
</div>
