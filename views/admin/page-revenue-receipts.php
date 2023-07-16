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

// Get url params
$url_params = arm_get_url_params( $_GET, 'page');

// Limit accessibility
if(!current_user_can('administrator'))
    $url_params['author'] = get_current_user_id();

// Get revenue receipts
$revenue_receipts = arm_get_revenue_receipts( array_merge( $params, $url_params ) );

// Get revenue records
$records = $revenue_receipts['records'];

// Get total records
$count = $revenue_receipts['count'];

// Prepare table header
$table_header = array(
     __('Author', 'rxarm') => '',
     __('Written words', 'rxarm') => 'written_words',
     __('Revenue per word', 'rxarm') => 'revenue_per_word',
     __('Penalities', 'rxarm') => 'total_penalties',
     __('Bonuses', 'rxarm') => 'total_bonuses',
     __('Payable amount', 'rxarm') => 'total_payable_amount',
     __('Issue date', 'rxarm') => 'created_at',
     __('Status', 'rxarm') => 'status',
     __('Actions', 'rxarm') => '',
);

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $receipt) 
    {
        $table_rows[] = array(
            arm_get_user_fullname($receipt->author_id),
            number_format($receipt->written_words),
            arm_format_price( $receipt->revenue_per_word , arm_get_author_locale($receipt->author_id) ),
            arm_format_price( $receipt->total_penalties , arm_get_author_locale($receipt->author_id) ),
            arm_format_price( $receipt->total_bonuses , arm_get_author_locale($receipt->author_id) ),
            arm_format_price( $receipt->total_payable_amount , arm_get_author_locale($receipt->author_id) ),
            arm_handle_datetime($receipt->created_at, false),
            __(ucfirst($receipt->status),'rxarm'),
            '<a href="'.add_query_arg( 'id', $receipt->id ).'">'.__('View','rxarm').'</a>'
        );
    }
?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Revenue receipts', 'rxarm'); ?>
            </h3>
        </div>
        <div class="col-md-9 col-sm-12 text-end">
            <?php echo arm_render_admin_filter( array('per_page', 'author', 'date_range', 'status' => array('paid','unpaid') ) ) ?>
        </div>
    </div>
    
    <div class="arm-content">
        <?php arm_generate_table( $table_rows, $table_header, $count ); ?>
    </div>
</div>
