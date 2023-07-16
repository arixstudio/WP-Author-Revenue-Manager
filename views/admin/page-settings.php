<?php defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * The admin area of the plugin to load settings
 */		

// Import resources
wp_enqueue_style('arm-admin-style');
wp_enqueue_script('arm-admin-script');
wp_enqueue_script('validate-script');
arm_enqueue_bootstrap();

// Get locale
$locale = arm_handle_locale();

// Set header menu
$header_menu_items = array(
    array(
        'title' => __( 'English', 'rxarm'),
        'slug' => 'en_US',
        'url' => add_query_arg('section', 'en_US'),
    ),
    array(
        'title' => __( 'Persian', 'rxarm'),
        'slug' => 'fa_IR',
        'url' => add_query_arg('section', 'fa_IR'),
    ),
    array(
        'title' => __( 'General', 'rxarm'),
        'slug' => 'general',
        'url' => add_query_arg('section', 'general'),
    ),
    array(
        'title' => __( 'Contact options', 'rxarm'),
        'slug' => 'contact-options',
        'url' => add_query_arg('section', 'contact-options'),
    )
);

// Update settings
function arm_update_settings( $locale )
{
    
    $settings_update_result = array('type'=>'success', 'message'=>__('Settings updated successfully.', 'rxarm'));

    try 
    {
        // Validate nonce
        if(!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'arm-nonce-settings' ))
            throw new Exception(__('Security check failure.', 'rxarm'), 1);
    
        if($_GET['section'] == 'en_US' || $_GET['section'] == 'fa_IR' || $_GET['section'] == '')
        {
            // Currency
            if(!isset($_POST['arm_currency_'.$locale]) || empty($_POST['arm_currency_'.$locale]))
                throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Currency', 'rxarm')), 1);
                update_option('arm_currency_'.$locale, sanitize_text_field( $_POST['arm_currency_'.$locale] ));
    
            // Currency position
            if(!isset($_POST['arm_currency_position_'.$locale]) || empty($_POST['arm_currency_position_'.$locale]))
                throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Currency position', 'rxarm')), 1);
                update_option('arm_currency_position_'.$locale, sanitize_text_field( $_POST['arm_currency_position_'.$locale] ));
    
            // Thousand seperator
            if(!isset($_POST['arm_thousand_seperator_'.$locale]) || empty($_POST['arm_thousand_seperator_'.$locale]))
                throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Thousand seperator', 'rxarm')), 1);
                update_option('arm_thousand_seperator_'.$locale,  mb_substr(sanitize_text_field( $_POST['arm_thousand_seperator_'.$locale] ), 0, 1));
    
            // Decimal seperator
            if(!isset($_POST['arm_decimal_seperator_'.$locale]) || empty($_POST['arm_decimal_seperator_'.$locale]))
                throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Decimal seperator', 'rxarm')), 1);
                update_option('arm_decimal_seperator_'.$locale,  mb_substr(sanitize_text_field( $_POST['arm_decimal_seperator_'.$locale] ), 0, 1));
    
            // Number of decimals
            update_option('arm_number_of_decimals_'.$locale, absint( $_POST['arm_number_of_decimals_'.$locale] ));
            
            // Default revenue per word
            if(!isset($_POST['arm_default_revenue_per_word_'.$locale]) || empty($_POST['arm_default_revenue_per_word_'.$locale]))
                throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Default revenue per word', 'rxarm')), 1);
                update_option('arm_default_revenue_per_word_'.$locale, floatval( $_POST['arm_default_revenue_per_word_'.$locale] ));
            
            // Excluded words
            update_option('arm_excluded_words_'.$locale, sanitize_text_field( $_POST['arm_excluded_words_'.$locale] ));
        } 
        else if ($_GET['section'] == 'general')
        {
            // Payday
            if(!isset($_POST['arm_payday']) || empty($_POST['arm_payday']))
                throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Payday', 'rxarm')), 1);
                update_option('arm_payday', sanitize_text_field( $_POST['arm_payday'] ));
            
            // Auto adjustment rules
                update_option('arm_auto_bonus_rules', sanitize_text_field( $_POST['arm_auto_bonus_rules'] ));
                update_option('arm_auto_penalty_rules', sanitize_text_field( $_POST['arm_auto_penalty_rules'] ));
        }
        else if ($_GET['section'] == 'contact-options')
        {
            // Options
            update_option('arm_contact_options', $_POST['option']);
        }

        
    } catch (\Throwable $ex) 
    {
        $settings_update_result = array('type'=>'warning', 'message'=>$ex->getMessage());
    }

    arm_set_alert($settings_update_result['type'], $settings_update_result['message'], 'rxarm'); 

    // Redirection
    arm_redirect( arm_get_url_params($_GET) );
}

// Restore default settings
function arm_restore_default_settings( $locale )
{
    arm_init_settings($locale);

    arm_set_alert('success', __('Default settings restored.', 'rxarm'));

    // Redirection
    arm_redirect( arm_get_url_params($_GET) );
}

?>

<div class="wrap arm-font <?php echo is_rtl() ? 'rtl' : '' ?>">    
    <div class="arm-header row">
        <div class="col-md-3 col-sm-12">
            <h3 class="arm-font">
                <?php _e( 'Settings', 'rxarm'); ?>
            </h3>
        </div>
        <div class="col-md-9 col-sm-12 text-end">
            <?php arm_render_admin_header_section_menu($header_menu_items, 'section', false, $locale); ?>
        </div>
    </div>

    <!-- Alert -->
    <?php arm_show_alert() ?>

    <?php isset( $_POST['update'] ) ? arm_update_settings($locale) : '' ?>
    <?php isset( $_POST['restore'] ) ? arm_restore_default_settings($locale) : '' ?>

    <div class="arm-content">
            <form class="arm-form" id="arm-settings-form" method="POST">
                <!-- General -->
                <?php if(isset($_GET['section']) && $_GET['section'] == 'general') : ?>
                    <div class="row mt-4 mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_payday" class="form-label"><?php _e( 'Payday (Day of month)', 'rxarm'); ?></label>
                            <div class="form-text"><?php _e('Day of month','rxarm') ?></div>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <select name="arm_payday" id="arm_payday" autocomplete="off">
                                <option value="" disabled selected><?php _e('Select', 'rxarm') ?>...</option>
                                <?php for ($i = 28 ; $i >= 1 ; $i--) : ?>
                                    <option value="<?php echo $i ?>" <?php echo get_option( 'arm_payday' ) == $i ? 'selected' : '' ?>><?php echo $i ?></option>
                                <?php endfor ?>
                            </select>                         
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_auto_bonus_rules" class="form-label"><?php _e( 'Auto bonus rules', 'rxarm'); ?></label>                            
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <textarea name="arm_auto_bonus_rules" class="me-2 ltr" id="arm_auto_bonus_rules" autocomplete="off"><?php echo get_option( 'arm_auto_bonus_rules' ) ?></textarea>                         
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_auto_penalty_rules" class="form-label"><?php _e( 'Auto penalty rules', 'rxarm'); ?></label>                            
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <textarea name="arm_auto_penalty_rules" class="me-2 ltr" id="arm_auto_penalty_rules" autocomplete="off"><?php echo get_option( 'arm_auto_penalty_rules' ) ?></textarea>                         
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="" class="form-label"><?php _e( 'Help', 'rxarm'); ?></label>                            
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <div class="form-text ltr"><?php _e('Ex: 1000,2000,1','rxarm') ?></div>
                            <div class="form-text ltr"><?php _e('i.e: Range min, Range max, Percent','rxarm') ?></div>
                            <div class="form-text ltr"><?php _e('Seperate rules with semicolon ( ; )','rxarm') ?></div>
                            <div class="form-text ltr"><?php _e('Ex: 1000,2000,1;2000,3000,2','rxarm') ?></div>                        
                        </div>
                    </div>
                    <br>
                <!-- Contact options -->
                <?php elseif(isset($_GET['section']) && $_GET['section'] == 'contact-options') : ?>
                    <?php 
                        // Get contact options
                        $contact_options = arm_get_contact_options();

                        // Nodata table display
                        $nodata_display = '';
                        
                        // Create adjustment button
                        arm_render_floating_button( array('id'=>"add-admin-contact-option", 'title'=>__('Add contact option', 'rxarm')));

                        // Render options
                        echo '<div id="contact-options">';
                        if (is_array($contact_options) && !empty($contact_options))
                        {
                            foreach ($contact_options as $key => $option) 
                            {
                                echo '<div class="row contact-option mt-4 mb-4">
                                        <div class="col-md-3 col-sm-12">
                                            <label class="form-label">'.__('Slug', 'rxarm').'</label>
                                            <input type="text" class="me-2" name="option['.$key.'][slug]" id="slug" value="'.$option['slug'].'">                       
                                        </div>
                                        <div class="col-md-3 col-sm-12">
                                            <label class="form-label">'.__('English title', 'rxarm').'</label>
                                            <input type="text" class="me-2" name="option['.$key.'][title-en_US]" id="title-en_US" value="'.$option['title-en_US'].'">                       
                                        </div>
                                        <div class="col-md-3 col-sm-12">
                                            <label class="form-label">'.__('Persian title', 'rxarm').'</label>
                                            <input type="text" class="me-2" name="option['.$key.'][title-fa_IR]" id="title-fa_IR" value="'.$option['title-fa_IR'].'">                       
                                        </div>
                                        <div class="col-md-2 col-sm-12">
                                            <br>
                                            <input class="form-check-input" name="option['.$key.'][is_required]" type="checkbox" value="1" id="is_required-'.$key.'" '.($option['is_required'] == 1 ? 'checked' : '').'>
                                            <label class="form-check-label" for="is_required-'.$key.'">
                                            '.__('Required', 'rxarm').'
                                            </label>
                                        </div>
                                        <div class="col-md-1 col-sm-12">
                                            <br>
                                            <span role="button" class="remove-option"><i class="fa fa-trash-alt"></i> </span>
                                        </div>
                                    </div>';
                            }
                            $nodata_display = 'none';
                        }
                        echo '<table class="arm-table table table-striped table-hover" style="display:'.$nodata_display.'">';
                        echo '<tr class="bg-white"><td class="nodata-image" class="text-center"><img src="'.ARM_ASSETS.'/admin/img/nodata.png"></td></tr>';
                        echo '<tr><td class="text-center arm-text-lightgrey">'.__('No data found.', 'rxarm' ).'</td></tr>';
                        echo '</table>';
                        echo '</div>';                        
                    ?>
                    <br>
                <!-- Language based -->
                <?php else : ?> 
                    <div class="row mt-4 mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_currency_<?php echo $locale ?>" class="form-label"><?php _e( 'Currency', 'rxarm'); ?></label>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <select name="arm_currency_<?php echo $locale ?>" id="arm_currency_<?php echo $locale ?>" autocomplete="off">
                                <option value="" disabled selected><?php _e('Select', 'rxarm') ?>...</option>
                                <?php 
                                    // Get currencies
                                    $currencies = arm_get_currencies();
                                    foreach ($currencies as $currency_code => $currency) : ?>
                                    <option value="<?php echo $currency_code ?>" <?php echo get_option( 'arm_currency_'.$locale ) == $currency_code ? 'selected' : '' ?> ><?php echo $currency['title'].' ('.$currency['symbol'].')' ?></option>

                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_currency_position_<?php echo $locale ?>" class="form-label"><?php _e( 'Currency position', 'rxarm'); ?></label>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <select name="arm_currency_position_<?php echo $locale ?>" id="arm_currency_position_<?php echo $locale ?>" autocomplete="off">
                                <option value="" disabled selected><?php _e('Select', 'rxarm') ?>...</option>
                                <option value="left" <?php echo get_option( 'arm_currency_position_'.$locale ) == "left" ? 'selected' : '' ?> ><?php _e('Left','rxarm') ?></option>
                                <option value="right" <?php echo get_option( 'arm_currency_position_'.$locale ) == "right" ? 'selected' : '' ?> ><?php _e('Right','rxarm') ?></option>
                                <option value="left-space" <?php echo get_option( 'arm_currency_position_'.$locale ) == "left-space" ? 'selected' : '' ?> ><?php _e('Left with space','rxarm') ?></option>
                                <option value="right-space" <?php echo get_option( 'arm_currency_position_'.$locale ) == "right-space" ? 'selected' : '' ?> ><?php _e('Right with space','rxarm') ?></option>
                            </select> 
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_thousand_seperator_<?php echo $locale ?>" class="form-label"><?php _e( 'Thousand seperator', 'rxarm'); ?></label>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" class="me-2" name="arm_thousand_seperator_<?php echo $locale ?>" id="arm_thousand_seperator_<?php echo $locale ?>" value="<?php echo get_option( 'arm_thousand_seperator_'.$locale ) ?>">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_decimal_seperator_<?php echo $locale ?>" class="form-label"><?php _e( 'Decimal seperator', 'rxarm'); ?></label>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" class="me-2" name="arm_decimal_seperator_<?php echo $locale ?>" id="arm_decimal_seperator_<?php echo $locale ?>" value="<?php echo get_option( 'arm_decimal_seperator_'.$locale ) ?>">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_number_of_decimals_<?php echo $locale ?>" class="form-label"><?php _e( 'Number of decimals', 'rxarm'); ?></label>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" class="me-2" name="arm_number_of_decimals_<?php echo $locale ?>" id="arm_number_of_decimals_<?php echo $locale ?>" value="<?php echo get_option( 'arm_number_of_decimals_'.$locale ) ?>">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_default_revenue_per_word_<?php echo $locale ?>" class="form-label"><?php _e( 'Default revenue per word', 'rxarm'); ?></label>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" class="me-2" name="arm_default_revenue_per_word_<?php echo $locale ?>" id="arm_default_revenue_per_word_<?php echo $locale ?>" value="<?php echo get_option( 'arm_default_revenue_per_word_'.$locale ) ?>">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 col-sm-6">
                            <label for="arm_excluded_words_<?php echo $locale ?>" class="form-label"><?php _e( 'Excluded words from counting', 'rxarm'); ?></label>
                            <div class="form-text"><?php _e('Seperate words with comma (,)', 'rxarm') ?></div>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <textarea class="me-2 <?php echo $locale == 'en_US' ? 'ltr"' : '' ?>" name="arm_excluded_words_<?php echo $locale ?>" id="arm_excluded_words_<?php echo $locale ?>"><?php echo get_option( 'arm_excluded_words_'.$locale ) ?></textarea>
                        </div>
                    </div>
                <?php endif ?>
                <?php wp_nonce_field('arm-nonce-settings', 'nonce',false) ?>
                <?php
                    submit_button(__('Update settings','rxarm'),'btn btn-primary mt-5 me-2', 'update','', false);
                    submit_button(__('Restore defaults','rxarm'), 'btn btn-secondary mt-5 ', 'restore', '', false);
                ?>
            </form>
    </div>
</div>