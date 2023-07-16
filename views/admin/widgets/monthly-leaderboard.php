<?php defined( 'ABSPATH' ) or exit;

/**
 * Monthly leaderboard
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();

// Get calendar
$is_jalali = arm_get_author_locale( get_current_user_id()) == 'fa_IR' ? true : false;

// Get current date
$current_date = arm_get_current_datetime( false );

// Set month start / end datetime
if(isset($_GET['month']))
    $month_number = sanitize_text_field( $_GET['month']);
else
    $month_number = arm_get_date_month_number( $current_date, $is_jalali );

// Set current month start / end datetime
$month_start_datetime = arm_get_month_first_day_date( $month_number, $is_jalali);
$month_end_datetime = arm_get_month_last_day_date( $month_number, $is_jalali);

// Get leaderboard months (having data)
$months = arm_get_leaderboard_months( $is_jalali, true );

// Set menu default month
$menu_default_month = $month_number;

// Prepare menu items
$header_menu_items = array();
if (is_array($months) && !empty($months))
    foreach ($months as $month) {
        $header_menu_items[] = array(
            'title' => $month['name'],
            'slug' => $month['number'],
            'url' => add_query_arg('month', $month['number']),
        );
    }

// Get authors
$authors = arm_get_authors();

// Get records
$records = $authors['records'];

// Prepare data
$data = array();
if (is_array($records))
    foreach ($records as $author) 
    {
        // Get author id
        $author_id = $author->ID;

        // Get revenues
        $revenues = arm_get_revenue_receipts( array('author'=>$author_id) )['records'];
        
        // Get current month words written
        $month_written_words = arm_count_author_written_words( $author_id, $month_start_datetime, $month_end_datetime );

        $data[] = array(
            'author_id' => $author_id,
            'author' => arm_get_user_fullname($author_id),
            'month_written_words' => $month_written_words,
        );
    } 

// Sort by written words
$written_words = array_column($data, 'month_written_words');
array_multisort($written_words, SORT_DESC, $data);

// Append rank
foreach ($data as $key => $element) 
{
    $data[$key] = array_merge($element, array( 'rank' => ($key+1)));
}

// Prepare table rows
$table_rows = array();
foreach ($data as $item) 
{
    $table_rows[] = array(
        '<b>'.$item['rank'].'</b>',
        $item['author'],
        '<div class="text-end me-2">'.number_format($item['month_written_words']).'</div>',
    );
} 

?>

<div class="arm-dashboard-widget arm-font">
    <?php 
        if (is_array($header_menu_items) && !empty($header_menu_items))
            arm_render_admin_header_section_menu( $header_menu_items, 'month', false, $menu_default_month ); 
    ?>
    <?php arm_generate_table( $table_rows, null, null, false, array('first-fit') ) ?>
</div>
