<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */

// Import resources
wp_enqueue_style('arm-admin-style');
arm_enqueue_bootstrap();


// Set current datetime (today)
$current_datetime = arm_get_current_datetime();

// Set params
$params = array(
    'calculation_end_datetime' => $current_datetime,
    'limit' => arm_get_per_page() ? arm_get_per_page() : 10,
    'offset' => arm_get_pagination_offset(),
);

// Get current month's revenues of all the authors
$current_month_revenues = arm_get_current_month_revenues( array_merge( $params, arm_get_url_params( $_GET, 'page')) );

// Get revenue records
$records = $current_month_revenues['records'];

// Get total records
$count = $current_month_revenues['count'];

// Set current month start datetime
$current_month_start_datetime = arm_get_current_month_start_datetime();

// Prepare table header
$table_header = array(
     __('Author', 'rxarm') => '',
     __('Written words', 'rxarm') => 'written_words',
     __('Revenue per word', 'rxarm') => 'revenue_per_word',
     __('Penalties', 'rxarm') => 'total_penalties',
     __('Bonuses', 'rxarm') => 'total_bonuses',
     __('Payable amount', 'rxarm') => 'payable_amount',
);

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $revenue) 
    {
        // Get author locale
        $author_locale = arm_get_author_locale($revenue->author_id);

        $table_rows[] = array(
            arm_get_user_fullname($revenue->author_id),
            number_format($revenue->written_words),
            arm_format_price( $revenue->revenue_per_word , $author_locale ),
            $revenue->total_penalties ? arm_format_price($revenue->total_penalties, $author_locale) : '-',
            $revenue->total_bonuses ? arm_format_price($revenue->total_bonuses, $author_locale) : '-',
            arm_format_price( $revenue->payable_amount , $author_locale ),
        );
    }

// Manual accounting
if (isset($_GET['do']))
{
    // Get range start date time
    $range_start_datetime = arm_get_current_month_start_datetime();

    // Get range end date time
    $range_end_datetime = arm_get_current_month_end_datetime();

    // Get authors
    $authors = arm_get_authors()['records'];

    // Do accounting
    arm_do_accounting( $authors, $range_start_datetime, $range_end_datetime);

    // Update last payday date
    update_option('arm_last_payday', arm_get_current_datetime(false));
}
?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( "Month's revenues", 'rxarm'); ?>
            </h3>
            <p class="arm-text-lightgrey"><?php echo arm_handle_datetime($current_month_start_datetime).' - '.arm_handle_datetime($current_datetime) ?></p>
        </div>
        <div class="col-md-9 col-sm-12 text-end">
            <?php echo arm_render_admin_filter( array('per_page', 'author') ) ?>
        </div>
    </div>
    
    <div class="arm-content">
        <?php arm_generate_table( $table_rows, $table_header, $count ); ?>
    </div>
</div>
