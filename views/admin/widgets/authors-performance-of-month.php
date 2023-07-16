<?php defined( 'ABSPATH' ) or exit;

/**
 * Author's performance of the month
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Set current month start datetime
$current_month_start_datetime = arm_get_current_month_start_datetime();

// Set current datetime (today)
$current_datetime = arm_get_current_datetime();

// Get authors
$authors = arm_get_authors();

// Get records
$records = $authors['records'];

// Prepare table header
$table_header = array(
    __('Author', 'rxarm') => null,
    __('Previous month', 'rxarm') => null,
    __('This month', 'rxarm') => null,
    __('Difference', 'rxarm') => null,
    __('Performance', 'rxarm') => null,
);

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $author) 
    {
        // Get revenues
        $revenues = arm_get_revenue_receipts( array('author'=>$author->ID) )['records'];
        
        // Get author's written words of the previous month
        $previous_month_written_words = $revenues ? end($revenues)->written_words : '0';

        // Get current month words written
        $current_month_written_words = arm_count_author_written_words( $author->ID, arm_get_current_month_start_datetime(), arm_get_current_datetime(), '0' );

        // Performance diff
        $performance_diff = ($current_month_written_words - $previous_month_written_words);

        // Performance percent diff
        $performance_percent_diff = arm_percent_diff($previous_month_written_words,$current_month_written_words);

        $table_rows[] = array(
            arm_get_user_fullname($author->ID),
            number_format($previous_month_written_words),
            number_format($current_month_written_words),
            number_format($current_month_written_words - $previous_month_written_words),
            '<span class="'.($performance_diff > 1 ? 'arm-text-green' : 'arm-text-red').'">'.$performance_percent_diff.'</span>',
        );
    } 

?>

<div class="arm-dashboard-widget arm-font">
    <?php arm_generate_table( $table_rows, $table_header, null, false ) ?>
</div>
