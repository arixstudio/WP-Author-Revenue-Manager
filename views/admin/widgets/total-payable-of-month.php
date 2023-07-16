<?php defined( 'ABSPATH' ) or exit;

/**
 * Total payable amount of the month
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Set current month start datetime
$current_month_start_datetime = arm_get_current_month_start_datetime();

// Set current datetime (today)
$current_datetime = arm_get_current_datetime();

// Get current month's revenues of all the authors
$current_month_revenues = arm_get_current_month_revenues( array('calculation_end_datetime' => arm_get_current_datetime()))['records'];

// Calculate total current month's revenues of all the authors
$total_current_month_revenues = is_array($current_month_revenues) ? array_sum(array_column($current_month_revenues,'payable_amount')) : 0;

// Get unpaid revenue receipts
$unpaid_revenue_receipts = arm_get_revenue_receipts( array('status'=>'unpaid') )['records'];

// Calculate total unpaid revenue receipts of all the authors
$total_unpaid_revenue_receipts = is_array($unpaid_revenue_receipts) ? array_sum(array_column($unpaid_revenue_receipts,'total_payable_amount')) : 0;

// Total payable amount
$total_payable_amount = (floatval($total_current_month_revenues) + floatval($total_unpaid_revenue_receipts));

?>

<div class="arm-dashboard-widget arm-font">
    <div class="text-center pt-3 pb-4">
        <div class="arm-text-darkgrey fs-1">
            <?php echo arm_format_price($total_payable_amount, get_locale()) ?>
        </div>
        <div class="arm-text-lightgrey">
            <?php echo arm_handle_datetime($current_month_start_datetime, false).' - '.arm_handle_datetime($current_datetime, false) ?>
        </div>
    </div>
</div>