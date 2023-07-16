<?php defined( 'ABSPATH' ) or exit;

/*
* API Functions
*
*/

// Register get revenue receipts route
add_action( 'rest_api_init', function() {
    register_rest_route( 
        'arm/v1', 
        '/revenue-receipts/', 
        array(
            'methods' => 'GET',
            'callback' =>  'arm_api_get_revenue_receipts',
        ) 
    );
} );

// Register submit bonus route
add_action( 'rest_api_init', function() {
    register_rest_route( 
        'arm/v1', 
        '/submit-bonus/', 
        array(
            'methods' => 'POST',
            'callback' =>  'arm_api_submit_bonus',
        ) 
    );
} );

// Register repeal bonus route
add_action( 'rest_api_init', function() {
    register_rest_route( 
        'arm/v1', 
        '/repeal-bonus/', 
        array(
            'methods' => 'GET',
            'callback' =>  'arm_api_repeal_bonus',
        ) 
    );
} );

// Get revenue receipts
function arm_api_get_revenue_receipts()
{
    if(!is_user_logged_in())
        return array (
            'success' => false,
            'message' => __('You are not allowed to perform this action.', 'rxarm'),
        );
        
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

    $result = array(
        'receipts' => $records,
        'count' => $count,
    );
    
    return rest_ensure_response( $result );
}

// Submit bonus
function arm_api_submit_bonus()
{
    $result = arm_submit_bonus( true );
    
    return rest_ensure_response( $result );
}

// Repeal bonus
function arm_api_repeal_bonus()
{
    $result = arm_repeal_bonus( true );
    
    return rest_ensure_response( $result );
}
