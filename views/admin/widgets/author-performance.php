<?php defined( 'ABSPATH' ) or exit;

/**
 * Author's performance in compare with previous month 
 */

 // Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Get current author id
$author_id = get_current_user_id();

// Set current month start datetime
$current_month_start_datetime = arm_get_current_month_start_datetime();

// Set current datetime (today)
$current_datetime = arm_get_current_datetime();

// Get revenues
$revenues = arm_get_revenue_receipts( array('author'=>$author_id) )['records'];
        
// Get author's written words of the previous month
$previous_month_written_words = $revenues ? end($revenues)->written_words : '0';

// Get current month words written
$current_month_written_words = arm_count_author_written_words( $author_id, $current_month_start_datetime, $current_datetime, '0' );

// Performance diff
$performance_diff = ($current_month_written_words - $previous_month_written_words);

// Performance percent diff
$performance_percent_diff = arm_percent_diff($previous_month_written_words,$current_month_written_words);

?>

<div class="arm-dashboard-widget arm-font">
    <div class="arm-text-lightgrey">
        <?php echo arm_handle_datetime($current_month_start_datetime, false).' - '.arm_handle_datetime($current_datetime, false) ?>
    </div>
    <div class="text-center pt-3 pb-2">
        <div class="arm-text-darkgrey py-3 fs-1">
            <span class="<?php echo ($performance_diff > 1 ? 'arm-text-green' : 'arm-text-red') ?>"><?php echo $performance_percent_diff ?></span>
        </div>
        <div class="arm-text-lightgrey mt-3">
            <span class="me-4"><?php _e('Previous month', 'rxarm') ?>: <b><?php echo number_format($previous_month_written_words).'</b> '.__('words', 'rxarm') ?></span>
            <span><?php _e('This month', 'rxarm') ?>: <b><?php echo number_format($current_month_written_words).'</b> '.__('words', 'rxarm') ?></span>
        </div>
    </div>
</div>