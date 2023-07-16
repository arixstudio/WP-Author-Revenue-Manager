<?php
/**
 * Plugin Name: Author Revenue Management
 * Plugin URI: https://YOURDOMAIN.com/
 * Description: Manage authors' revenues based on their performance
 * Author: arixstudio
 * Author URI: http://www.arixstudio.com/
 * Version: 1.0
 * Text Domain: rxarm
 * Domain Path: /language
 *
 * Copyright: (c) 2005-2022 arixstudio, Inc. (info@arixstudio.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    arixstudio
 * @category  Admin
 * @copyright Copyright (c) 2005-2022, arixstudio, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */
 
defined( 'ABSPATH' ) or exit;

/**
 * Constants
 *
 */

// Plugin Dir
define ( 'ARM_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Assets Dir
define ( 'ARM_ASSETS', plugins_url( '/assets', __FILE__ ));

// Uploads Dir
define ( 'ARM_UPLOADS', wp_upload_dir()['path'].'/arm-attachments/');

// Uploads url
define ( 'ARM_UPLOADS_URL', home_url('wp-content/uploads/arm-attachments/'));

/**********************************************
 *
 * Inlcuding modules
 *
 **********************************************/

foreach ( glob( plugin_dir_path( __FILE__ ) . "includes/*.php" ) as $file ) 
{
    include_once $file;
}

/**
 * Loads the plugin's text domain for localization.
 */
function arm_load_plugin_textdomain()
{
	load_plugin_textdomain('rxarm', false, plugin_basename(dirname(__FILE__)) . '/language');
}

add_action('plugins_loaded', 'arm_load_plugin_textdomain');

/**
 * Actions on plugin activation
 */
register_activation_hook(__FILE__, function() 
{

	// Create Database tables on plugin activation

	global $wpdb;
 
	$table_prefix = $wpdb->prefix;

	// Design author' post logs table
	$author_post_logs = "CREATE TABLE `{$table_prefix}arm_author_post_logs` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`author_id` int(11) NOT NULL,
	`post_id` int(11) NOT NULL,
	`words_count` int(11) NOT NULL,
	`is_adjusted` int(1) NOT NULL,
	`is_accounted` int(1) NOT NULL,
	`created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";

	// Design revenue receipts table
	$revenue_receipts = "CREATE TABLE `{$table_prefix}arm_revenue_receipts` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`author_id` int(11) NOT NULL,
	`start_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`end_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`written_words` int(11) NOT NULL,
	`revenue_per_word` varchar(10) NOT NULL,
	`total_revenue_amount` varchar(12) NOT NULL,
	`total_bonuses` int(11),
	`total_penalties` int(11),
	`adjustment_ids` varchar(100) NOT NULL,
	`total_payable_amount` varchar(8) NOT NULL,
	`status` varchar(20) NOT NULL,
	`created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";

	// Design revenue transactions table
	$transactions = "CREATE TABLE `{$table_prefix}arm_transactions` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`author_id` int(11) NOT NULL,
	`revenue_number` int(11) NOT NULL,
	`amount` int(11) NOT NULL,
	`method` varchar(25),
	`ref_number` varchar(20),
	`attachment_id` varchar(25),
	`created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";

	// Design adjustments table
	$adjustments = "CREATE TABLE `{$table_prefix}arm_adjustments` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`adjustment` varchar(20) NOT NULL,
	`author_id` int(11) NOT NULL,
	`amount` int(11) NOT NULL,
	`reason` varchar(100) NOT NULL,
	`is_auto` int(11) NOT NULL,
	`minimum_words` int(11),
	`written_words` int(11),
	`is_accounted` int(11) NOT NULL,
	`created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";


	// For calling dbDelta function
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Create author' post logs table    
	dbDelta($author_post_logs);
	
	// Create revnue receipts table    
	dbDelta($revenue_receipts);
	
	// Create revnue transactions table    
	dbDelta($transactions);
	
	// Create adjustments table    
	dbDelta($adjustments);
	
	// Initialize settings
	if (get_option('arm_init_settings') != 'done')
	{
		update_option('arm_init_settings', 'done');

		arm_init_settings('en_US');
		arm_init_settings('fa_IR');
	}

	// Set the cron job for the monthly cron
	if( ! wp_next_scheduled ( 'arm_daily_cron' ) ) 
	{
		// This will trigger an action that will fire the "arm_daily_cron" action on the pay day of each month at 4:00 am UTC
		wp_schedule_event( strtotime('03:00:00'), 'daily', 'arm_daily_cron');
	}

});


/**
 * Initialize settings
 */
function arm_init_settings($locale)
{
	if ($locale == 'en_US')
	{
		update_option('arm_currency_en_US', 'USD');
		update_option('arm_currency_en_US', 'USD');
		update_option('arm_currency_position_en_US', 'left');
		update_option('arm_thousand_seperator_en_US', ',');
		update_option('arm_decimal_seperator_en_US', '.');
		update_option('arm_number_of_decimals_en_US', '2');
		update_option('arm_default_revenue_per_word_en_US', '0.1');
		update_option('arm_excluded_words_en_US', 'to, a, an, the, and, or, but, for, etc..');
	} else
	if ($locale == 'fa_IR')
	{
		update_option('arm_currency_fa_IR', 'IRT');
		update_option('arm_currency_position_fa_IR', 'right-space');
		update_option('arm_thousand_seperator_fa_IR', ',');
		update_option('arm_decimal_seperator_fa_IR', '.');
		update_option('arm_number_of_decimals_fa_IR', '0');
		update_option('arm_default_revenue_per_word_fa_IR', '20');
		update_option('arm_excluded_words_fa_IR', 'به ,از , را, در, می, که');
	}

	update_option('arm_payday', '28');
	update_option('arm_contact_options', array(array('slug' => 'mobile_number','title-en_US' => 'Mobile number','title-fa_IR' => 'شماره موبایل','is_required' => '1')));
}


/**
 * Enqueue admin scripts and stylesheets
 */

function arm_register_admin_resources()
{
	// Register admin resources 
	wp_register_style('arm-admin-style', ARM_ASSETS . '/admin/css/admin-style.css', array(), '1.0', 'all');
	wp_register_script('arm-admin-script', ARM_ASSETS . '/admin/js/admin-scripts.js', array('jquery'));
	
	// Localization for ajax 
	wp_localize_script('arm-admin-script', 'arm_ajaxhandler', array('ajaxurl' => admin_url('admin-ajax.php'), 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
    wp_localize_script('arm-admin-script', 'arm_translate_handler', array( 
    
        'is_required' => __('This field is required.', 'rxarm'),
        'invalid_number' => __('Please enter a valid number.', 'rxarm'),
        'invalid_file' => __('Please select valid input file format.', 'rxarm'),
        'required' => __('Required', 'rxarm'),
        'slug' => __('Slug', 'rxarm'),
        'english_title' => __('English title', 'rxarm'),
        'persian_title' => __('Persian title', 'rxarm'),
    
    ));
}

add_action('admin_init', 'arm_register_admin_resources');


/**
 * Enqueue common scripts and stylesheets
 */

function arm_register_common_resources()
{
    // Register common resources
	wp_register_style('bootstrap-style', ARM_ASSETS . '/common/libs/bootstrap/css/bootstrap.min.css', array(), '1.0', 'all');
	wp_register_style('bootstrap-rtl-style', ARM_ASSETS . '/common/libs/bootstrap/css/bootstrap.rtl.min.css', array(), '1.0', 'all');
	wp_register_script('bootstrap-script', ARM_ASSETS . '/common/libs/bootstrap/js/bootstrap.min.js', array('jquery'));
	wp_register_script('bootstrap-bundle-script', ARM_ASSETS . '/common/libs/bootstrap/js/bootstrap.bundle.min.js', array('jquery'));
	wp_register_script('popper-script', ARM_ASSETS . '/common/libs/bootstrap/js/popper.min.js', array('jquery'));
	wp_register_style('vanilla-datepicker-style', ARM_ASSETS . '/common/libs/vanilla-datepicker/vanilla-datepicker.min.css', array(), '1.0', 'all');
	wp_register_style('vanilla-datepicker-custom-style', ARM_ASSETS . '/common/libs/vanilla-datepicker/vanilla-datepicker-custom.css', array(), '1.0', 'all');
	wp_register_script('vanilla-datepicker-script', ARM_ASSETS . '/common/libs/vanilla-datepicker/vanilla-datepicker.min.js', array('jquery'));
	wp_register_script('vanilla-datepicker-custom-script', ARM_ASSETS . '/common/libs/vanilla-datepicker/vanilla-datepicker-custom.js', array('jquery'));
	wp_register_style('persian-datepicker-style', ARM_ASSETS . '/common/libs/persian-datepicker/mds.bs.datetimepicker.css', array(), '1.0', 'all');
	wp_register_style('persian-datepicker-custom-style', ARM_ASSETS . '/common/libs/persian-datepicker/mds.bs.datetimepicker-custom.css', array(), '1.0', 'all');
	wp_register_script('persian-datepicker-script', ARM_ASSETS . '/common/libs/persian-datepicker/mds.bs.datetimepicker.js', array('jquery'));
	wp_register_script('persian-datepicker-custom-script', ARM_ASSETS . '/common/libs/persian-datepicker/mds.bs.datetimepicker-custom.js', array('jquery'));
	wp_register_script('lightbox-script', ARM_ASSETS . '/common/libs/lightbox/lightbox.min.js', array('jquery'));
	wp_register_script('validate-script', ARM_ASSETS . '/common/libs/validate/jquery.validate.min.js', array('jquery'));
	wp_register_style('select2-style', ARM_ASSETS . '/common/libs/select2/css/select2.min.css', array(), '1.0', 'all');
	wp_register_script('select2-script', ARM_ASSETS . '/common/libs/select2/js/select2.full.min.js', array('jquery'));
	wp_register_script('select2-custom-script', ARM_ASSETS . '/common/libs/select2/js/select2-custom.js', array('jquery'));
	wp_register_script('money-format-script', ARM_ASSETS . '/common/libs/money-format/money-format.js', array('jquery'));
	
}

add_action('init', 'arm_register_common_resources');


/**
 * Plugin's admin menus
 */
function arm_add_plugin_admin_menu() {
	
	// Revenue receipts notification count
	if(current_user_can('administrator'))
	{
		$revenue_receipts = arm_get_revenue_receipts( array('status'=>'unpaid') );
		if(!empty($revenue_receipts))
			$notification_count = $revenue_receipts['count'];
		else
			$notification_count = '';
	}

	// Revenue receipts (Main)
	add_menu_page( 
		__( 'Revenue receipts', 'rxarm' ), //page title
		$notification_count ? sprintf( __( 'Revenues', 'rxarm' ).' <span class="awaiting-mod">%d</span>', $notification_count ) : __( 'Revenues', 'rxarm' ),
		'edit_posts', //capability
		'arm-revenues', //menu_slug,
		'arm_load_revenue_receipts_page', //callback
		'dashicons-money-alt'
	);
		
	// Month's revenues
	add_submenu_page( 
		'arm-revenues', //parent_slug,
		__( "Month's revenues", 'rxarm' ), //page title
		__( "Month's revenues", 'rxarm' ), //menu title
		'manage_options', //capability
		'arm-month-revenues', //menu_slug,
		'arm_load_month_revenues_page' //callback,
	);

	// Leaderboard
	add_submenu_page( 
		'arm-revenues', //parent_slug,
		__( 'Leaderboard', 'rxarm' ), //page title
		__( 'Leaderboard', 'rxarm' ), //menu title
		'manage_options', //capability
		'arm-leaderboard', //menu_slug,
		'arm_load_leaderboard_page' //callback
	);
	
	// Authors
	add_submenu_page( 
		'arm-revenues', //parent_slug,
		__( 'Authors', 'rxarm' ), //page title
		__( 'Authors', 'rxarm' ), //menu title
		'manage_options', //capability
		'arm-authors', //menu_slug,
		'arm_load_authors_page' //callback
	);
	
	// Bonuses
	add_submenu_page( 
		'arm-revenues', //parent_slug,
		__( 'Bonuses', 'rxarm' ), //page title
		__( 'Bonuses', 'rxarm' ), //menu title
		'edit_posts', //capability
		'arm-bonuses', //menu_slug,
		'arm_load_bonuses_page' //callback
	);

	// Penalties
	add_submenu_page( 
		'arm-revenues', //parent_slug,
		__( 'Penalties', 'rxarm' ), //page title
		__( 'Penalties', 'rxarm' ), //menu title
		'edit_posts', //capability
		'arm-penalties', //menu_slug,
		'arm_load_penalties_page' //callback
	);

	// Transactions
	add_submenu_page( 
		'arm-revenues', //parent_slug,
		__( 'Transactions', 'rxarm' ), //page title
		__( 'Transactions', 'rxarm' ), //menu title
		'edit_posts', //capability
		'arm-transactions', //menu_slug,
		'arm_load_transactions_page' //callback
	);

	// Settings
	add_submenu_page( 
		'arm-revenues', //parent_slug,
		__( 'Settings', 'rxarm' ), //page title
		__( 'Settings', 'rxarm' ), //menu title
		'manage_options', //capability
		'arm-settings', //menu_slug,
		'arm_load_settings_page' //callback
	);

	
	// Author profile
	if (!current_user_can( 'administrator' ))
		add_submenu_page( 
			'arm-revenues', //parent_slug,
			__( 'Profile', 'rxarm' ), //page title
			__( 'Profile', 'rxarm' ), //menu title
			'edit_posts', //capability
			'arm-author', //menu_slug,
			'arm_load_author_page' //callback
		);

	// // Playground for debugging
	// add_submenu_page( 
	// 	'arm-revenues', //parent_slug,
	// 	__( 'Playground', 'rxarm' ), //page title
	// 	__( 'Playground', 'rxarm' ), //menu title
	// 	'administrator', //capability
	// 	'arm-playground', //menu_slug,
	// 	'arm_load_playground', //callback
	// );
}

add_action( 'admin_menu', 'arm_add_plugin_admin_menu');

/**
 * Plugin's pages
 */
// Month's revenues (Main)
function arm_load_month_revenues_page()
{
	// render
	include_once( 'views/admin/page-month-revenues.php' );
}
// Revenue receipts
function arm_load_revenue_receipts_page()
{
	// render
	if (isset($_GET['id']) && !empty($_GET['id']))
		include_once( 'views/admin/page-revenue-receipt.php' ); // Single
	else
		include_once( 'views/admin/page-revenue-receipts.php' ); // List
}
// Authors
function arm_load_authors_page()
{
	// render
	if (isset($_GET['id']) && !empty($_GET['id']))
		include_once( 'views/admin/page-author.php' ); // Single
	else
		include_once( 'views/admin/page-authors.php' ); // List
}
// Author
function arm_load_author_page()
{
	// render
		include_once( 'views/admin/page-author.php' ); // Single
}
// Bonuses
function arm_load_bonuses_page()
{
	// render
	if (isset($_GET['id']) && !empty($_GET['id'] && !isset($_GET['action'])))
		include_once( 'views/admin/page-bonus.php' ); // Single
	else
		include_once( 'views/admin/page-bonuses.php' ); // List
}
// Penalties
function arm_load_penalties_page()
{
	// render
	if (isset($_GET['id']) && !empty($_GET['id'] && !isset($_GET['action'])))
		include_once( 'views/admin/page-penalty.php' ); // Single
	else
		include_once( 'views/admin/page-penalties.php' ); // List
}
// transactions
function arm_load_transactions_page()
{
	// render
	if (isset($_GET['id']) && !empty($_GET['id']))
		include_once( 'views/admin/page-transaction.php' ); // Single
	else
		include_once( 'views/admin/page-transactions.php' ); // List
}
// Leaderboard
function arm_load_leaderboard_page()
{
	// render
	include_once( 'views/admin/page-leaderboard.php' );
}
// Settings
function arm_load_settings_page()
{
	// render
	include_once( 'views/admin/page-settings.php' );
}
// Playground
function arm_load_playground()
{
	// render
	include_once( 'views/admin/playground.php' );
}





