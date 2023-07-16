<?php defined( 'ABSPATH' ) or exit;

/**
 * Author's revenue receipts
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Set params
$params = array(
    'limit' => arm_get_per_page() ? arm_get_per_page() : 10,
    'offset' => arm_get_pagination_offset(),
    'author' => get_current_user_id()
);

// Get url params
$url_params = arm_get_url_params( $_GET, 'page');

// Unset author form url params if any
unset($url_params['author']);

// Get revenue receipts
$revenue_receipts = arm_get_revenue_receipts( array_merge( $params, $url_params ) );

// Get revenue records
$records = $revenue_receipts['records'];

// Get total records
$count = $revenue_receipts['count'];

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $receipt) 
    {
        $table_rows[] = array(
            arm_format_price( $receipt->total_payable_amount , arm_get_author_locale($receipt->author_id) ),
            arm_handle_datetime($receipt->start_datetime, false).' - '.arm_handle_datetime($receipt->end_datetime, false),
            '<div class="text-end me-2">'.__(ucfirst($receipt->status),'rxarm').'</div>',
        );
    }

?>

<div class="arm-dashboard-widget arm-font">
    <?php arm_generate_table( $table_rows, $table_header, null, false ) ?>
</div>
