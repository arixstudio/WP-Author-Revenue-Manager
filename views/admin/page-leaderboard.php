<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
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
    $month_number = absint( $_GET['month']);
else
    $month_number = arm_get_date_month_number( $current_date, $is_jalali );

// Set current month start / end datetime
$month_start_datetime = arm_get_month_first_day_date( $month_number, $is_jalali);
$month_end_datetime = arm_get_month_last_day_date( $month_number, $is_jalali);

// Get leaderboard months (having data)
$months = arm_get_leaderboard_months( $is_jalali, true );

// Set menu default month
$menu_default_month = date('m', strtotime($month_start_datetime));

$args = array();

// Apply limit
if (isset($_GET['per_page']))
    $args['limit'] = arm_get_per_page();
else
    $args['limit'] = 10;

// Apply offset
if (isset($_GET['paged']))
    $args['offset'] = arm_get_pagination_offset();

// Get authors
$authors = arm_get_authors($args);

// Get records
$records = $authors['records'];

// Get total authors
$count = $authors['count'];

// Prepare data
$data = array();
if (is_array($records))
    foreach ($records as $author) 
    {
        // Get author id
        $author_id = $author->ID;

        // Get current month words written
        $month_written_words = arm_count_author_written_words( $author_id, $month_start_datetime, $month_end_datetime );

        $data[] = array(
            'author_id' => $author_id,
            'author' => arm_get_user_fullname($author_id),
            'rgistered_at' => arm_handle_datetime($author->data->user_registered, false),
            'month_written_words' => $month_written_words,
        );
    } 

// Sort by written words
$written_words = array_column($data, 'month_written_words');
array_multisort($written_words, SORT_DESC, $data);

// Append rank
foreach ($data as $key => $element) 
{
    $data[$key] = array_merge($element, array( 'rank' => ($key + arm_get_pagination_offset() + 1)));
}

// Prepare table header
$table_header = array(
    __('Rank', 'rxarm') => '',
    __('Full name', 'rxarm') => '',
    __('Registered at', 'rxarm') => '',
    __('Words written', 'rxarm') => '',
    __('Actions', 'rxarm') => '',
);

// Prepare table rows
$table_rows = array();
foreach ($data as $item) 
{
    $table_rows[] = array(
        '<b>'.$item['rank'].'</b>',
        $item['author'],
        $item['rgistered_at'],
        number_format($item['month_written_words']),            
        '<a href="'.add_query_arg( 'id', $item['author_id'], admin_url('admin.php?page=arm-authors') ).'">'.__('View','rxarm').'</a>'
    );
} 

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Leaderboard', 'rxarm'); ?>
            </h3>
            <p class="arm-text-lightgrey"><?php echo arm_get_month_name( $month_number, $is_jalali ) ?></p>
        </div>
        <div class="col-md-9 col-sm-12 text-end">
            <?php echo arm_render_admin_filter( array('per_page','month'=>arm_get_leaderboard_months( $is_jalali, true ))) ?>
        </div>
    </div>

    <div class="arm-content">
        <?php arm_generate_table( $table_rows, $table_header, $count ); ?>
    </div>
</div>
