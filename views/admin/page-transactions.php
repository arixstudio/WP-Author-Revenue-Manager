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

// Get transactions
$transactions = arm_get_transactions( array_merge( $params, $url_params ) );

// If single result
if (is_object($transactions))
{
    $transactions_arr = $transactions;
    $transactions = array();
    $transactions['records'][] = $transactions_arr;
}

// Get revenue records
$records = $transactions['records'];

// Get total records
$count = $transactions['count'];

// Prepare table header
$table_header = array(
    __('Author', 'rxarm') => '',
    __('Amount', 'rxarm') => 'amount',
    __('Revenue number', 'rxarm') => '',
    __('Ref number', 'rxarm') => '',
    __('Date', 'rxarm') => 'created_at',
    __('Actions', 'rxarm') => '',
);

// Prepare table rows
$table_rows = array();
if (is_array($records))
    foreach ($records as $transaction) 
    {
        $table_rows[] = array(
            arm_get_user_fullname($transaction->author_id),
            arm_format_price( $transaction->amount , arm_get_author_locale($transaction->author_id)),
            $transaction->revenue_number,
            $transaction->ref_number ? $transaction->ref_number : '-',
            arm_handle_datetime($transaction->created_at, false),
            '<a href="'.add_query_arg( 'id', $transaction->id ).'">'.__('View','rxarm').'</a>'
        );
    }
?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Transactions', 'rxarm'); ?>
            </h3>
        </div>
        <div class="col-md-9 col-sm-12 text-end">
            <?php echo arm_render_admin_filter( array('per_page', 'author', 'date_range', 'revenue_number' ) ) ?>
        </div>
    </div>
    
    <div class="arm-content">
        <?php arm_generate_table( $table_rows, $table_header, $count ); ?>
    </div>
</div>
