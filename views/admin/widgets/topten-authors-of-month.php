<?php defined( 'ABSPATH' ) or exit;

/**
 * Top 10 authors of the month
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

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $author) 
    {
        // Get revenues
        $revenues = arm_get_revenue_receipts( array('author'=>$author->ID) )['records'];
        
        // Get current month words written
        $current_month_written_words = arm_count_author_written_words( $author->ID, arm_get_current_month_start_datetime(), arm_get_current_datetime(), '0' );

        $table_rows[] = array(
            arm_get_user_fullname($author->ID),
            '<div class="text-end me-2">'.number_format($current_month_written_words).'</div>',
        );
    } 

// Sort by written words
usort($table_rows, function ($a, $b) { return $b[1] <=> $a[1];});

// Get first 10 
if(count($table_rows) > 10)
$table_rows = array_slice($table_rows, 10);

// Append rank
foreach ($table_rows as $key => $element) 
{
    array_unshift($element,'<b>'.($key+1).'</b>');
    $table_rows[$key] = $element;
}

?>

<div class="arm-dashboard-widget arm-font">
    <?php arm_generate_table( $table_rows, null, null, false, array('first-fit') ) ?>
</div>
