<?php defined( 'ABSPATH' ) or exit;

/*
* Functions
*
*/

// Handle locale
function arm_handle_locale()
{
    // Set current locale
    if (isset($_GET['section']) && in_array( $_GET['section'], array('fa_IR', 'en_US')))
        $locale = $_GET['section'];
    else
        $locale = get_locale();

    return $locale;
}

// Enqueue bootstrap resources based on locale direction
function arm_enqueue_bootstrap()
{
    // Import style
    if (is_rtl())
    {
        wp_enqueue_style('bootstrap-rtl-style');

    } else {
        wp_enqueue_style('bootstrap-style');
    }

    // Import scripts
    wp_enqueue_script('bootstrap-bundle-script');
    wp_enqueue_script('popper-script');
    wp_enqueue_script('bootstrap-script');
}

// Enqueue datepicker resources based on locale direction
function arm_enqueue_datepicker()
{
    // Import resources
    if (arm_get_author_locale( get_current_user_id()) == 'fa_IR')
    {
        wp_enqueue_style('persian-datepicker-style');
        wp_enqueue_style('persian-datepicker-custom-style');
        wp_enqueue_script('persian-datepicker-script');
        wp_enqueue_script('persian-datepicker-custom-script');

    } else {
        wp_enqueue_style('vanilla-datepicker-style');
        wp_enqueue_style('vanilla-datepicker-custom-style');
        wp_enqueue_script('vanilla-datepicker-script');
        wp_enqueue_script('vanilla-datepicker-custom-script');
    }

}

// Render admin header section menu
function arm_render_admin_header_section_menu( $items, $arg_name = 'section', $active_first = true, $active_slug = null ) 
{
    echo '<div class="arm-section-menu">';
    echo '<ul>';
    foreach ($items as $key => $item) 
    {
        // Set class
        $class = '';

        if (isset($_GET[$arg_name]))
        {
            if ( $item['slug'] == $_GET[$arg_name] )
            {
                $class = 'arm-active-link';
            }

        } else {
            if (($key == 0 && $active_first) || $item['slug'] == $active_slug)
                $class = 'arm-active-link';
        }
        echo '<li><a href="'.$item['url'].'" class="'.$class.'">'.$item['title'].'</a></li>';
    }
    echo '</ul>';
    echo '</div>';
}

// Render admin filter
function arm_render_admin_filter( $items ) 
{
    // Open filter container
    $filter = '<div class="arm-filter">';
    
    // Append form
    $filter .= '<form class="row justify-content-end" method="GET" action="">';
    
    // Append current page
    $filter .= '<input type="hidden" name="page" value="'.$_GET["page"].'" autocomplete="off">';
    
    // Append per page
    if (in_array('per_page', $items))
    $filter .= '<div class="col-auto mb-2">
                    <select class="form-select" name="per_page" autocomplete="off">
                        <option value="" disabled selected>10</option>
                        <option value="20" '.(isset($_GET['per_page']) && $_GET['per_page'] == 20 ? 'selected' : '').'>20</option>
                        <option value="50" '.(isset($_GET['per_page']) && $_GET['per_page'] == 50 ? 'selected' : '').'>50</option>
                        <option value="100" '.(isset($_GET['per_page']) && $_GET['per_page'] == 100 ? 'selected' : '').'>100</option>
                    </select>
                </div>';

    // Append revenue number
    if (in_array('revenue_number', $items))
    $filter .= '<div class="col-md-2 col-sm-6 mb-2"><input class="form-control" type="text" name="revenue_number" value="'.(isset($_GET["revenue_number"]) ? $_GET["revenue_number"] : '').'" autocomplete="off" placeholder="'.__('Revenue number', 'rxarm').'"></div>';
    
    // Append author
    if (in_array('author', $items) && current_user_can('administrator'))
    {
        // Import resources
        wp_enqueue_style('select2-style');
        wp_enqueue_script('select2-script');
        wp_enqueue_script('select2-custom-script');

        // Get authors
        $authors = arm_get_authors()['records'];

        $filter .= '<div class="col-2 mb-2">
                        <select id="filter-author" class="form-select" name="author" autocomplete="off">';
                            $filter .= '<option value="" disabled selected>'.__('Author', 'rxarm').'</option>';
                            if (is_array($authors) && !empty($authors))
                            foreach ($authors as $author) 
                            {
                                $selected = isset($_GET['author']) && $_GET['author'] == $author->ID ? 'selected' : '';
                                $filter .= '<option value="'.$author->ID.'" '. $selected .'>'.$author->first_name.' '.$author->last_name.' ('.$author->user_login.')'.'</option>';
                            }
        $filter .= '</select></div>';
    }
    
    // Append status
    if (isset($items['status']))
    {
        $filter .= '<div class="col-md-2 col-sm-6 mb-2">
        <select class="form-select" name="status" autocomplete="off">';
        $filter .= '<option value="" disabled selected>'.__('Status', 'rxarm').'</option>';
        if (is_array($items['status']) && !empty($items['status']))
                            foreach ($items['status'] as $status) 
                            {
                                $selected = isset($_GET['status']) && $_GET['status'] == $status ? 'selected' : '';
                                $filter .= '<option value="'.$status.'" '. $selected .'>'.__(ucfirst($status), 'rxarm').'</option>';
                            }
        $filter .= '</select></div>';
    }
    
    // Append month
    if (isset($items['month']))
    {
        $filter .= '<div class="col-md-2 col-sm-6 mb-2">
        <select class="form-select" name="month" autocomplete="off">';
        $filter .= '<option value="" disabled selected>'.__('Month', 'rxarm').'</option>';
        if (is_array($items['month']) && !empty($items['month']))
                            foreach ($items['month'] as $month) 
                            {
                                $selected = isset($_GET['month']) && $_GET['month'] == $month['number'] ? 'selected' : '';
                                $filter .= '<option value="'.$month['number'].'" '. $selected .'>'.__($month['name'], 'rxarm').'</option>';
                            }
        $filter .= '</select></div>';
    }
    
    // Append search
    if (in_array('search', $items))
        $filter .= '<div class="col-md-2 col-sm-6 mb-2"><input class="form-control" type="text" name="s" value="'.$_GET["s"].'" autocomplete="off" placeholder="'.__('Search', 'rxarm').'"></div>';
    
    // Append date range
    if (in_array('date_range', $items))
    {
        arm_enqueue_datepicker();

        // Append start date time
        $filter .= '<div class="col-md-2 col-sm-6 mb-2"><input class="form-control arm-datepicker" type="text" name="start_datetime" id="start_datetime" value="'.(isset($_GET["start_datetime"]) && !empty($_GET["start_datetime"]) ? $_GET["start_datetime"] : '') .'" autocomplete="off" placeholder="'.__('Start from', 'rxarm').'"></div>';
        
        // Append end date time
        $filter .= '<div class="col-md-2 col-sm-6 mb-2"><input class="form-control arm-datepicker" type="text" name="end_datetime" id="end_datetime" value="'.(isset($_GET["end_datetime"]) && !empty($_GET["end_datetime"]) ? $_GET["end_datetime"] : '') .'" autocomplete="off" placeholder="'.__('End from', 'rxarm').'"></div>';

    }

    // Append apply button
    $filter .= '<div class="col-auto"><button type="submit" class="btn btn-primary">'.__('Apply', 'rxarm').'</button></div>';
    
    // Close form
    $filter .= '</form>';

    // Close the row
    $filter .= '</div>';

    if($items && !empty($items))
    echo $filter;
}

// Get all the parameters from url and sanitize them
function arm_get_url_params($get_params, $exclude = null)
{
    // Set params
    $params = array();

    foreach ($get_params as $key => $value) 
    {
        $params[$key] = sanitize_text_field( $value );
    }

    // Remove empty values
    foreach ($params as $key => $param) 
    {
        if ($param == '')
            unset($params[$key]);
    }

    // Exclude a value
    if($exclude)
    {
        if( is_array($exclude) && !empty($exclude) )
        {
            foreach ($exclude as $key => $item) 
            {
                unset($params[$key]);
            }
        
        } else {
            unset($params[$exclude]);
        }

    }

    return $params;
}

// Count number of words of a post content
function arm_count_post_words( $post_id )
{
    // Get author's locale
    $author_locale = arm_get_author_locale(get_current_user_id());

    // Get post content
    $content_post = get_post( $post_id );
    $content = $content_post->post_content;

    // Exclude prepositions
    $propositions = explode(',', get_option( 'arm_excluded_words_'.$author_locale ) );
    foreach ($propositions as $word) // Remove space if any
        $propositions[$word] = trim($word);

    $content = str_replace($propositions, "", $content);

    // Exclude html tags
    $content = preg_replace('/<[^>]*>/', '', $content);
    
    // Exclude html space code
    $content = preg_replace(array('/&nbsp;/'), ' ', $content);
    
    // Exclude multiple spaces with single
    $content = trim(preg_replace('!\s+!', ' ', $content));
    
    return count(explode(' ', $content));
}

// Add author post log
function arm_add_author_post_log( $post_id )
{
    // Set params
    $params = array(
        'table_name' => 'arm_author_post_logs',
        'col_arr' => array(
            'author_id' => get_post_field( 'post_author', $post_id ),
            'post_id' => $post_id,
            'words_count' => arm_count_post_words( $post_id ),
            'is_adjusted' => '0',
            'is_accounted' => '0',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ),
        'col_format_arr' => array(
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
        )
    );

    $result = arm_insert( $params );

    if (!is_wp_error( $result ))
        return true;
    
}

// Get author post log
function arm_get_author_post_log( $post_id, $is_adjusted = null , $is_accounted = null )
{
    // Set params
    $params = array(
        'table_name' => 'arm_author_post_logs',
        'where_col_arr' => array(
            array(
                'key' => 'post_id',
                'val' => $post_id,
                'format' => '%d'
            )
        )
    );

    // Adjustment status if set
    if ($is_adjusted != null)
        $params['where_col_arr'][] = array( 'key' => 'is_adjusted', 'val' => $is_adjusted, 'format' => '%d' );

    // Accounting status if set
    if ($is_accounted != null)
        $params['where_col_arr'][] = array( 'key' => 'is_accounted', 'val' => $is_accounted, 'format' => '%d' );
    
    // Get log
    $result = arm_read( $params );

    if (!is_wp_error( $result ))
        return $result;
    
}

// Update author post log
function arm_update_author_post_log( $post_id, $is_adjusted = null , $is_accounted = null )
{
    // Set params
    $params = array(
        'table_name' => 'arm_author_post_logs',
        'col_arr' => array(
            'words_count' => arm_count_post_words( $post_id ),
            'updated_at' => current_time('mysql'),
        ),
        'where_col_arr' => array(
            'post_id'  => $post_id
        ),
    );

    // Adjustment status if set
    if ($is_adjusted != null)
        $params['col_arr'] = array_merge( $params['col_arr'], array( 'is_adjusted' => $is_adjusted )  );

    // Accounting status if set
    if ($is_accounted != null)
        $params['col_arr'] = array_merge( $params['col_arr'], array( 'is_accounted' => $is_accounted )  );

    $result = arm_update( $params );

    // Update existing log
    if (!is_wp_error( $result ))
        return $result;
    
}

// Auto update author post log
function arm_auto_update_author_post_log( $post_id )
{
    // Get post data
    $post = get_post( $post_id );

    if ( $post->post_type == 'post' && ( $post->post_status == 'pending' || $post->post_status == 'publish' ) )
    {
        // Get logs
        $result = arm_get_author_post_log( $post_id );

        if ($result)
        {
            if ($result[0]->is_adjusted == '0' && $result[0]->is_accounted == '0')
                // Update existing log
                arm_update_author_post_log( $post_id );
        }
        else
        {
            // Add new log
            arm_add_author_post_log( $post_id );
        }
    }
}

add_action( 'save_post', 'arm_auto_update_author_post_log');

// Get author's written words
function arm_get_author_written_words_logs( $author_id, $start_datetime, $end_datetime, $is_adjusted = null, $is_accounted = null )
{
    // Get logs
    $params = array(
        'table_name' => 'arm_author_post_logs',
        'where_col_arr' => array(
            array(
                'key' => 'author_id',
                'val' =>  $author_id,
                'format' => '%d',
            ),
            array(
                'key' => 'created_at',
                'val' =>  $start_datetime,
                'format' => '%s',
                'operation' => '>='
            ),array(
                'key' => 'created_at',
                'val' =>  $end_datetime,
                'format' => '%s',
                'operation' => '<='
            )
        )
    );
    
    // Adjustment status if set
    if ($is_adjusted != null)
        $params['where_col_arr'][] = array( 'key' => 'is_adjusted', 'val' => $is_adjusted, 'format' => '%d' );
    
    // Accounting status if set
    if ($is_accounted != null)
        $params['where_col_arr'][] = array( 'key' => 'is_accounted', 'val' => $is_accounted, 'format' => '%d' );

    $result = arm_read($params);

    if (!is_wp_error( $result ))
        return $result;
}

// Count author's written words
function arm_count_author_written_words( $author_id, $start_datetime, $end_datetime, $is_accounted = null )
{
    // Get logs
    $result = arm_get_author_written_words_logs( $author_id, $start_datetime, $end_datetime, $is_accounted );

    // Get total words count
    $total_words_count =  is_array($result) ? array_sum(array_column($result,'words_count')) : 0;

    return $total_words_count;
}

// Insert revenue receipt
function arm_insert_revenue_receipt( $params )
{
    // Set params
    $params = array(
        'table_name' => 'arm_revenue_receipts',
        'col_arr' => array(
            'author_id' => $params['author_id'],
            'start_datetime' => $params['start_datetime'],
            'end_datetime' => $params['end_datetime'],
            'written_words' => $params['written_words'],
            'revenue_per_word' => $params['revenue_per_word'],
            'total_revenue_amount' => $params['total_revenue_amount'],
            'total_bonuses' => $params['total_bonuses'],
            'total_penalties' => $params['total_penalties'],
            'adjustment_ids' => $params['adjustment_ids'],
            'total_payable_amount' => $params['total_payable_amount'],
            'status' => $params['status'],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ),
        'col_format_arr' => array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
        )
    );
    
    $result = arm_insert( $params );

    if (!is_wp_error( $result ))
        return true;
}

// Update revenue receipt
function arm_update_revenue_receipt( $params )
{
    // Set params
    $params = array(
        'table_name' => 'arm_revenue_receipts',
        'col_arr' => array(
            'status' => $params['status'],
            'updated_at' => current_time('mysql'),
        ),
        'where_col_arr' => array(
            'id'  => $params['id']
        ),
    );

    $result = arm_update( $params );

    if (!is_wp_error( $result ))
        return true;
}

// Create revenue receipt
function arm_create_revenue_receipt( $author_id, $start_datetime, $end_datetime )
{
    // Get author written words logs
    $author_written_words_logs = arm_get_author_written_words_logs( $author_id, $start_datetime, $end_datetime, '', '0' );

    // If author has un-accounted written words logs
    if (is_array($author_written_words_logs) && !empty($author_written_words_logs))
    {
        // Get author written words
        $author_written_words = is_array($author_written_words_logs) ? array_sum(array_column($author_written_words_logs,'words_count')) : 0;
    
        // Get author revenue per word
        $author_revenue_per_word = arm_get_author_revenue_per_word( $author_id );
    
        // Calculate author's gross revenue
        $author_gross_revenue = $author_written_words * $author_revenue_per_word;
    
        // Set adjustment date filters
        $adjustment_start_datetime = $start_datetime;
        $adjustment_end_datetime = date('Y-m-d', strtotime(arm_get_current_datetime(). '+ 1 day')); // Adding one day will include auto generated adjustments too.

        // Normalize datetime
        if (get_locale() == 'fa_IR')
        {
            $adjustment_start_datetime = arm_convert_gregorian_to_jalali($adjustment_start_datetime);
            $adjustment_end_datetime = arm_convert_gregorian_to_jalali($adjustment_end_datetime);
        }

        // Get author total bonuses
        $bonuses = arm_get_adjustments( array('author' => $author_id, 'adjustment' => 'bonus', 'start_datetime' => $adjustment_start_datetime, 'end_datetime' => $adjustment_end_datetime, 'is_accounted' => '0' ));
        if(is_array($bonuses) && !empty($bonuses))
        {
            $total_bonuses = $bonuses['sum'];
            $bonus_records = $bonuses['records'];
        }

        // Get author total penalties
        $penalties = arm_get_adjustments( array('author' => $author_id, 'adjustment' => 'penalty', 'start_datetime' => $adjustment_start_datetime, 'end_datetime' => $adjustment_end_datetime, 'is_accounted' => '0' ));
        if(is_array($penalties) && !empty($penalties))
        {
            $total_penalties = $penalties['sum'];
            $penalty_records = $penalties['records'];
        }

        // Set adjustment ids
        $adjustment_ids = array();

        // Set bonuses as accounted
        if (is_array($bonus_records) && !empty($bonus_records))
            foreach ($bonus_records as $bonus) 
            {
                // Add adjustment id to array
                $adjustment_ids[] = $bonus->id;
            }

        // Set penalties as accounted
        if (is_array($penalty_records) && !empty($penalty_records))
            foreach ($penalty_records as $penalty) 
            {
                // Add adjustment id to array
                $adjustment_ids[] = $penalty->id;
            }
        
        // Calculate payable amount
        $payable_amount = ($author_gross_revenue + $total_bonuses) - $total_penalties;
        
        // Set params
        $params = array(
            'author_id' => $author_id,
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime,
            'written_words' => $author_written_words,
            'revenue_per_word' => $author_revenue_per_word,
            'total_revenue_amount' => $author_gross_revenue,
            'total_bonuses' => $total_bonuses,
            'total_penalties' => $total_penalties,
            'adjustment_ids' => serialize($adjustment_ids),
            'total_payable_amount' => $payable_amount,
            'status' => 'unpaid',
        );
        
        // Create revenue receipt
        if ($payable_amount > 0)
            $result = arm_insert_revenue_receipt( $params ); 
    
        if ( true === $result )
        {
            // Set log as accounted
            foreach ($author_written_words_logs as $log) 
            {
                arm_update_author_post_log( $log->post_id, '', '1' );
            }

            // Set adjustment ids
            $adjustment_ids = array();

            // Set bonuses as accounted
            if (is_array($bonus_records) && !empty($bonus_records))
                foreach ($bonus_records as $bonus) 
                {
                    // Set adjustment as accounted
                    arm_update_adjustment( array('id' => $bonus->id, 'is_accounted' => '1') );
                }

            // Set penalties as accounted
            if (is_array($penalty_records) && !empty($penalty_records))
                foreach ($penalty_records as $penalty) 
                {
                    // Set adjustment as accounted
                    arm_update_adjustment( array('id' => $penalty->id, 'is_accounted' => '1') );
                }
        }

    }

}


// Get revenue receipts
function arm_get_revenue_receipts( $params = null )
{
    // Get calendar
    $calendar = arm_get_calendar();

    // Set params
    $args = array(
        'table_name' => 'arm_revenue_receipts',
        'where_col_arr' => array()
    );

    // Order by
    if (isset($params['order_by']))
        $args['orderby'] = $params['order_by'];

    // Order
    if (isset($params['order']))
        $args['order'] = $params['order'];

    // Append ID
    if (isset($params['id']))
        $args['where_col_arr'][] = array(
            'key' => 'id',
            'val' => $params['id'],
            'format' => '%d'
        );

    // Append args
    if (isset($params['author']))
        $args['where_col_arr'][] = array(
            'key' => 'author_id',
            'val' => $params['author'],
            'format' => '%d'
        );

    // Append start datetime
    if (isset($params['start_datetime']))
        $args['where_col_arr'][] = array(
                'key' => 'created_at',
                'val' =>  arm_datetime_sql_format( $params['start_datetime'], $calendar ),
                'format' => '%s',
                'operation' => '>='
        );

    // Append end datetime
    if (isset($params['end_datetime']))
        $args['where_col_arr'][] = array(
                'key' => 'created_at',
                'val' =>  arm_datetime_sql_format( $params['end_datetime'], $calendar ),
                'format' => '%s',
                'operation' => '<='
        );

    // Append status
    if (isset($params['status']))
        $args['where_col_arr'][] = array(
            'key' => 'status',
            'val' => $params['status'],
            'format' => '%s'
        );

    // Count total records 
    $result['count'] = arm_count( $args );

    // Append limit
    if(isset($params['limit']))
        $args['limit'] = $params['limit'];

    // Append offset
    if(isset($params['offset']))
        $args['offset'] = $params['offset'];

    // Get specific column(s)
    if(isset($params['fields_arr']))
    {
        $args['fields_arr'] = $params['fields_arr'];

        // Get column(s)
        $result['records'] = arm_read_col( $args );
    }
    else
    {
        // Get revenue rceipts
        $result['records'] = arm_read( $args );
    }
    
    if(!is_wp_error( $result['records'] ))
    {
        // Return single result if id is set
        if (isset($params['id']))
            $result = $result['records'][0];

        return $result;
    }
}

// Get current month's revenue estimation
function arm_get_current_month_revenues( $params )
{
    // Set args (wp_user_query)
    $args = array();
    if (isset($params['author']))
        $args['author'] = absint($params['author']);

    // Apply limit
    if (isset($params['limit']))
        $args['limit'] = $params['limit'];

    // Apply offset
    if (isset($params['offset']))
        $args['offset'] = $params['offset'];
        
    // Get authors
    $authors = arm_get_authors($args);
    
    // Set result records
    $result['records'] = array();

    // Set total records
    $result['count'] = $authors['count'];
    
    foreach ($authors['records'] as $author) 
    {
        // Get author id
        $author_id = $author->data->ID;

        // Get current month start date time
        $calculation_start_datetime = arm_get_current_month_start_datetime();
        
        // Get calculation end date time
        $calculation_end_datetime = $params['calculation_end_datetime'];

        $result['records'][] = (object) array(

            // Get author id
            'author_id' => $author_id,
    
            // Get number of words written by the author
            'written_words' => $written_words = arm_count_author_written_words( $author_id, $calculation_start_datetime, $calculation_end_datetime, '0' ),
            
            // Get author's revenue per word
            'revenue_per_word' => $revenue_per_word = arm_get_author_revenue_per_word( $author_id ),
            
            // Get total penalties
            'total_penalties' => $total_penalties = arm_get_adjustments( array('author' => $author_id, 'adjustment' => 'penalty', 'start_datetime' => $calculation_start_datetime, 'end_datetime' => $calculation_end_datetime, 'is_accounted' => '0', 'calendar' => 'gregorian' ))['sum'],

            // Get total bonuses
            'total_bonuses' => $total_bonuses = arm_get_adjustments( array('author' => $author_id, 'adjustment' => 'bonus', 'start_datetime' => $calculation_start_datetime, 'end_datetime' => $calculation_end_datetime, 'is_accounted' => '0', 'calendar' => 'gregorian' ))['sum'],
        
            // Calculate payable amount
            'payable_amount' => ((absint($written_words) * floatval($revenue_per_word)) + absint($total_bonuses)) - absint($total_penalties),
        );
    }

    // Apply order by
    if (isset($params['order_by']) && !empty($params['order_by']))
    {
        $order_by = $params['order_by'];

        if ($params['order'] && !empty($params['order']))
        {
            define( 'ARM_FACTOR', ($params['order'] == 'ASC' ? 1 : -1) );
        }

        switch ($order_by) {
            case 'written_words':
                usort($result['records'], function($a, $b) {return ARM_FACTOR * ($a->written_words - $b->written_words);});
                break;
            case 'revenue_per_word':
                usort($result['records'], function($a, $b) {return ARM_FACTOR * ($a->revenue_per_word - $b->revenue_per_word);});
                break;
            case 'total_penalties':
                usort($result['records'], function($a, $b) {return ARM_FACTOR * ($a->total_penalties - $b->total_penalties);});
                break;
            case 'total_bonuses':
                usort($result['records'], function($a, $b) {return ARM_FACTOR * ($a->total_bonuses - $b->total_bonuses);});
                break;
            case 'payable_amount':
                usort($result['records'], function($a, $b) {return ARM_FACTOR * ($a->payable_amount - $b->payable_amount);});
                break;
        }

    }

    return $result;
}

// Get authors
function arm_get_authors( $params = null )
{
    $args = array(
        'role'    => 'author',
        'orderby' => isset($params['order_by']) ? $params['order_by'] : '',
        'order'   => isset($params['order']) ? $params['order'] : '',
    );

    // Get total records
    $result['count'] = count(get_users( $args ));

    // Append limit
    if(isset($params['limit']))
        $args['number'] = $params['limit'];
    else
        $args['number'] = 999999;

    // Append offset
    if(isset($params['offset']))
        $args['offset'] = $params['offset'];

    // Filter
    if (isset($params['author']))
    {
        // Get single author by ID
        $authors[] = get_userdata( $params['author'] );

    } else 
    {
        // Get authors list
        $authors = get_users( $args );
    }

    // Set records
    $result['records'] = $authors;

    return $result;
}

// Get author's revenue per word
function arm_get_author_revenue_per_word( $author_id )
{
    // Get author's custom revenue per word if any
    $author_revenue = arm_get_author_metadata( $author_id, 'revenue_per_word' );

    // If no custom revenue is set, get the default revenue per word
    if (!$author_revenue)
    {
        // Get author's locale
        $author_locale = arm_get_author_locale( $author_id );

        // Get default revenue per word
        $author_revenue = get_option('arm_default_revenue_per_word_'.$author_locale);
    }

    return $author_revenue;

}

// Get author metadata
function arm_get_author_metadata( $author_id, $field )
{
    return get_user_meta( $author_id, 'arm_'.$field, true );
}

// Update author metadata
function arm_update_author_metadata( $author_id, $field, $value )
{
    return update_user_meta( $author_id, 'arm_'.$field, sanitize_text_field( $value ) );
}


// Get transactions
function arm_get_transactions( $params )
{
    // Get calendar
    $calendar = arm_get_calendar();

    // Set params
    $args = array(
        'table_name' => 'arm_transactions',
        'where_col_arr' => array()
    );

    // Order by
    if (isset($params['order_by']))
        $args['orderby'] = $params['order_by'];

    // Order
    if (isset($params['order']))
        $args['order'] = $params['order'];

    // Append ID
    if (isset($params['id']))
        $args['where_col_arr'][] = array(
            'key' => 'id',
            'val' => $params['id'],
            'format' => '%d'
        );

    // Append args
    if (isset($params['author']))
        $args['where_col_arr'][] = array(
            'key' => 'author_id',
            'val' => $params['author'],
            'format' => '%d'
        );

    // Append start datetime
    if (isset($params['start_datetime']))
        $args['where_col_arr'][] = array(
                'key' => 'created_at',
                'val' =>  arm_datetime_sql_format( $params['start_datetime'], $calendar ),
                'format' => '%s',
                'operation' => '>='
        );

    // Append end datetime
    if (isset($params['end_datetime']))
        $args['where_col_arr'][] = array(
                'key' => 'created_at',
                'val' =>  arm_datetime_sql_format( $params['end_datetime'], $calendar ),
                'format' => '%s',
                'operation' => '<='
        );

    // Append revenue number
    if (isset($params['revenue_number']))
        $args['where_col_arr'][] = array(
            'key' => 'revenue_number',
            'val' => $params['revenue_number'],
            'format' => '%d'
        );

    // Count total records 
    $result['count'] = arm_count( $args );

    // Append limit
    if(isset($params['limit']) && $params['limit'])
        $args['limit'] = $params['limit'];

    // Append offset
    if(isset($params['offset']) && $params['offset'])
        $args['offset'] = $params['offset'];

    // Get revenue rceipts
    $result['records'] = arm_read( $args );
    
    if(!is_wp_error( $result['records'] ))
    {
        // Return single result if id is set
        if (isset($params['id']) || isset($params['revenue_number']))
        $result = $result['records'][0];

        return $result;
    }
}

// Table generator
function arm_generate_table( $rows, $headers = null, $total_records = null, $striped_style = true, $classes = null )
{
    echo '<div class="table-responsive">';
        echo '<table class="arm-table table '.($striped_style ? 'table-striped' : '').(is_array($classes) && !empty($classes) ? implode(" ",$classes) : '').' table-hover">';
            echo '<thead>';
            if (is_array($headers) && !empty($headers))
            {
                echo '<tr>';

                foreach ($headers as $title => $slug) // slug makes header sortable 
                if ($slug)
                    echo '<th scope="col"><i class="arm-sort fa '.arm_handle_sort_icon_class( $slug ).' me-1" aria-hidden="true"></i><a href="'.arm_handle_sortable_table_url( $slug ).'" class="sort-link">'.$title.'</a></th>';
                else
                    echo '<th scope="col">'.$title.'</th>';
                
                echo '</tr>';

            }
            echo '</thead>';

            echo '<tbody>';
            if (is_array($rows) && !empty($rows))
                foreach ($rows as $row) 
                {
                    echo '<tr>';
                        foreach ($row as $col) 
                        {
                            echo '<td>'.$col.'</td>';
                        }
                    echo '</tr>';
                }
            else
            {
                echo '<tr class="bg-white"><td class="nodata-image" colspan="'.(is_array($headers) ? count($headers) : '3').'" class="text-center"><img src="'.ARM_ASSETS.'/admin/img/nodata.png"></td></tr>';
                echo '<tr><td colspan="'.(is_array($headers) ? count($headers) : '3').'" class="text-center arm-text-lightgrey">'.__('No data found.', 'rxarm' ).'</td></tr>';
            }

            echo '</tbody>';
        echo '</table>';
    echo '</div>';

    // Render pagination
    if ($total_records)
        arm_pagination( $total_records );
}

// Get user's fullname
function arm_get_user_fullname( $user_id )
{

    // Get user
    $user = get_userdata($user_id);

    if ($user)
    {
        // Get user's username
        $username = $user->user_login;
    
        // Get user's first name
        $display_name = $user->display_name;
    
        if ($display_name)
        {
    
            return $display_name;
    
        } else if( $username )
        {
    
            // Get user
            return $username;
    
        }
    } 
    else
    {
        return __('DELETED', 'rxarm');
    }

}

// Convert datetime format to SQL format
function arm_datetime_sql_format( $datetime, $calendar = null )
{
    if ($calendar == 'jalali')
    {
        // Get time
        $time = date( 'H:i:s', strtotime( $datetime));

        // Convert jalali to gregorean date
        $datetime = arm_convert_jalali_to_gregorian( $datetime ).' '.$time;
    } 
    else
    {
        $datetime = date('Y-m-d H:i:s', strtotime($datetime));
    }

    return $datetime;
}

// Convert jalali date to gregorian date
function arm_convert_gregorian_to_jalali( $gregorian_date )
{
    // Split the datetime
    $jy = date('Y', strtotime( $gregorian_date ));
    $jm = date('m', strtotime( $gregorian_date ));
    $jd = date('d', strtotime( $gregorian_date ));

    // Include jdf library
    require_once("libs/jdf.php");

    // Convert gregorean to jalali date
    $jalali_date = gregorian_to_jalali( $jy, $jm, $jd, '-');

    return $jalali_date;
}

// Convert jalali date to gregorian date
function arm_convert_jalali_to_gregorian( $jalali_date )
{
    // Split the datetime
    $jy = date('Y', strtotime( $jalali_date ));
    $jm = date('m', strtotime( $jalali_date ));
    $jd = date('d', strtotime( $jalali_date ));

    // Include jdf library
    require_once("libs/jdf.php");

    // Convert jalali to gregorean date
    $gregorian_date = jalali_to_gregorian( $jy, $jm, $jd , '-');

    return $gregorian_date;
}

// Convert raw datetime to proper locale date or datetime
function arm_handle_datetime( $datetime, $with_time = true )
{
    // Get site's date format
    $date_format = get_option('date_format');

    // If persian
    if(arm_get_author_locale( get_current_user_id()) == 'fa_IR')
    {        
        if ($with_time)
        {
            // Get site's time format
            $time_format = get_option('time_format');        
            
            $formatted = arm_convert_gregorian_to_jalali( $datetime ).' '.date( $time_format );

        } else 
        {
            $formatted = arm_convert_gregorian_to_jalali( $datetime );
        }
        
    } else 
    {
        if ($with_time)
        {
            // Get site's time format
            $time_format = get_option('time_format');        
            
            $formatted = date( $date_format.' '.$time_format, strtotime($datetime) );

        } else 
        {
            $formatted = date( $date_format, strtotime($datetime) );
        }
    }

    return $formatted;
}

// Check if author can access an entity
function arm_is_accessible( $author_id, $entities, $entity_id )
{
    // Set params
    $params = array(
        'table_name' => 'arm_'.$entities,
        'where_col_arr' => array(
            array(
                'key' => 'id',
                'val' => $entity_id,
                'format' => '%d',
            ),
            array(
                'key' => 'author_id',
                'val' => $author_id,
                'format' => '%d',
            ),
        )
    );

    // Get log
    $result = arm_read( $params );

    if(!is_wp_error( $result ))
    {
        return true;
    }
}

// Activate redirect command in page content (after header sent)
function arm_output_buffer() 
{
    ob_start();
}

add_action('init', 'arm_output_buffer');

// Format price based on author locale and settings
function arm_format_price( $amount, $locale )
{
    // Get currency
    $currency = get_option('arm_currency_'.$locale);
    
    // Get thousand seperator
    $thousand_seperator = get_option('arm_thousand_seperator_'.$locale);
    
    // Get decimal seperator
    $decimal_seperator = get_option('arm_decimal_seperator_'.$locale);
    
    // Get decimal numbers
    $number_of_decimals = get_option('arm_number_of_decimals_'.$locale);
    
    // Get currency symbol
    $currency_symbol = arm_get_currency_symbol( $currency );

    // Get currency position
    $currency_position = get_option('arm_currency_position_'.$locale);

    // Format
    $amount = number_format(floatval($amount), intval($number_of_decimals), $decimal_seperator, $thousand_seperator);


    // Append symbol
    if ($currency_position == 'left')
        $formatted_price = $currency_symbol.$amount;

    if ($currency_position == 'left-space')
        $formatted_price = $currency_symbol.' '.$amount;

    if ($currency_position == 'right')
        $formatted_price = $amount.$currency_symbol;

    if ($currency_position == 'right-space')
        $formatted_price = $amount.' '.$currency_symbol;

    return $formatted_price;
}

// Get currencies
function arm_get_currencies()
{
    // Currencies
    $currencies = array(
        'USD' => array(
            'title' => __('United States (US) dollar','rxarm'),
            'symbol' => '$',
        ),
        'EUR' => array(
            'title' => __('Euro','rxarm'),
            'symbol' => '€',
        ),
        'IRR' => array(
            'title' => __('Iranian Rial','rxarm'),
            'symbol' => __('IRR','rxarm'),
        ),
        'IRT' => array(
            'title' => __('Iranian Toman','rxarm'),
            'symbol' => __('IRT','rxarm'),
        ),
    );

    return $currencies;
}

// Get currency symbol
function arm_get_currency_symbol( $currency )
{
    $symbol = arm_get_currencies()[$currency]['symbol'];
    return $symbol;
}

// Get author locale
function arm_get_author_locale( $author_id )
{
    $author_locale = arm_get_author_metadata( $author_id, 'locale' );

    // If author locale is not set
    if (!$author_locale)
        $author_locale = get_locale();

    return $author_locale;
}

// Insert adjustment ( Penalty / Bonus )
function arm_insert_adjustment( $params )
{
    // Set params
    $params = array(
        'table_name' => 'arm_adjustments',
        'col_arr' => array(
            'adjustment' => $params['adjustment'],
            'author_id' => $params['author_id'],
            'amount' => $params['amount'],
            'reason' => $params['reason'],
            'is_auto' => $params['is_auto'],
            'minimum_words' => $params['minimum_words'],
            'written_words' => $params['written_words'],
            'is_accounted' => $params['is_accounted'],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ),
        'col_format_arr' => array(
            '%s',
            '%d',
            '%d',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
        )
    );

    $result = arm_insert( $params );

    if (!is_wp_error( $result ))
        return true;
}

// Create automatic adjustment
function arm_create_automatic_adjustment( $author_id, $start_datetime, $end_datetime )
{
    // Get minimum words author must write if set
    $author_minimum_words = arm_get_author_metadata( $author_id, 'minimum_words');

    if ( $author_minimum_words )
    {
        // Get author revenue per word
        $revenue_per_word = arm_get_author_revenue_per_word( $author_id );
        
        // Get author written words logs
        $author_written_words_logs = arm_get_author_written_words_logs( $author_id, $start_datetime, $end_datetime, '0', '0' );

        // Get author written words
        $author_written_words = is_array($author_written_words_logs) ? array_sum(array_column($author_written_words_logs,'words_count')) : 0;

        // Calculate the gross revenue
        $gross_revenue = $revenue_per_word * $author_written_words;

        // Set the adjustment
        $adjustment = $author_written_words > $author_minimum_words ? 'bonus' : 'penalty';

        // Set reason
        $reason = $adjustment == 'bonus' ? __('High performance', 'rxarm') : __('Low performance', 'rxarm'); 

        // Calculate the performance difference
        $diff = absint($author_written_words - $author_minimum_words);

        // Get rule percentage
        $percent = arm_get_rule_percent( $adjustment, $diff );

        // Calculate the adjustment amount
        if ( $percent )
        {
            $amount = ($percent / 100) * $gross_revenue;
        }
        
        $params = array(
            'adjustment' => $adjustment,
            'author_id' => $author_id,
            'amount' => $amount,
            'reason' => $reason,
            'is_auto' => '1',
            'minimum_words' => $author_minimum_words,
            'written_words' => $author_written_words,
            'is_accounted' => '0',
        );

        if ($amount > 1)
            $result =  arm_insert_adjustment( $params );

        if ( true === $result )
        {
            // Set log as adjusted
            foreach ($author_written_words_logs as $log) 
            {
                arm_update_author_post_log( $log->post_id, '1' );
            }
        }
    }

    return true;
}

// Insert transactions
function arm_insert_transaction( $params )
{
    // Set params
    $params = array(
        'table_name' => 'arm_transactions',
        'col_arr' => array(
            'author_id' => $params['author_id'],
            'revenue_number' => $params['revenue_number'],
            'amount' => $params['amount'],
            'method' => $params['method'],
            'ref_number' => $params['ref_number'],
            'attachment_id' => $params['attachment_id'],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ),
        'col_format_arr' => array(
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
        )
    );

    $result = arm_insert( $params );

    if (!is_wp_error( $result ))
        return true;
}

// Update adjustment
function arm_update_adjustment( $params )
{
    // Set args
    $args = array(
        'table_name' => 'arm_adjustments',
        'col_arr' => array(
            'updated_at' => current_time('mysql'),
        ),
        'where_col_arr' => array(
            'id'  => $params['id']
        ),
    );

    // Append amount
    if ($params['amount'])
       $args['col_arr'] = array_merge( $args['col_arr'], array( 'amount' => $params['amount'] ));

    // Append reason
    if ($params['reason'])
       $args['col_arr'] = array_merge( $args['col_arr'], array( 'reason' => $params['reason'] ));

    // Append is_accounted
    if ($params['is_accounted'])
       $args['col_arr'] = array_merge( $args['col_arr'], array( 'is_accounted' => $params['is_accounted'] ));

    // Update
    $result = arm_update( $args );

    if (!is_wp_error( $result ))
        return $args;
}

// Delete adjustment
function arm_delete_adjustment( $adjustment_id )
{
    // Set params
    $params = array(
        'table_name' => 'arm_adjustments',
        'where_col_arr' => array(
            'id'  => $adjustment_id
        ),
        'col_format_arr' => array(
            '%d',
        )
    );

    $result = arm_delete( $params );

    if (!is_wp_error( $result ))
        return true;
}

// Get adjustments
function arm_get_adjustments( $params )
{
    // Get calendar
    $calendar = isset($params['calendar']) ? $params['calendar'] : arm_get_calendar();

    // Set params
    $args = array(
        'table_name' => 'arm_adjustments',
        'where_col_arr' => array()
    );
    
    // Order by
    if (isset($params['order_by']))
        $args['orderby'] = $params['order_by'];

    // Order
    if (isset($params['order']))
        $args['order'] = $params['order'];

    // Append ID
    if (isset($params['id']))
        $args['where_col_arr'][] = array(
            'key' => 'id',
            'val' => $params['id'],
            'format' => '%d'
        );

    // Append author
    if (isset($params['author']))
        $args['where_col_arr'][] = array(
            'key' => 'author_id',
            'val' => $params['author'],
            'format' => '%d'
        );

    // Append adjustment
    if (isset($params['adjustment']))
        $args['where_col_arr'][] = array(
            'key' => 'adjustment',
            'val' => $params['adjustment'],
            'format' => '%s'
        );

    // Append is accounted
    if (isset($params['is_accounted']) && !empty($params['is_accounted']))
        $args['where_col_arr'][] = array(
            'key' => 'is_accounted',
            'val' => $params['is_accounted'],
            'format' => '%s'
        );

    // Append start datetime
    if (isset($params['start_datetime']))
        $args['where_col_arr'][] = array(
                'key' => 'created_at',
                'val' =>  arm_datetime_sql_format( $params['start_datetime'], $calendar ),
                'format' => '%s',
                'operation' => '>='
        );

    // Append end datetime
    if (isset($params['end_datetime']))
        $args['where_col_arr'][] = array(
                'key' => 'created_at',
                'val' =>  arm_datetime_sql_format( $params['end_datetime'], $calendar ),
                'format' => '%s',
                'operation' => '<='
        );

    // Append type
    if (!empty($params['type']) && isset( $params['type']))
        $args['where_col_arr'][] = array(
            'key' => 'is_auto',
            'val' => $params['is_auto'],
            'format' => '%s'
        );

    // Count total records 
    $result['count'] = arm_count( $args );

    // Append limit
    if(isset($params['limit']))
        $args['limit'] = $params['limit'];

    // Append offset
    if(isset($params['offset']))
        $args['offset'] = $params['offset'];

    // Get adjustments
    $result['records'] = arm_read( $args );

    // Init sum
    $result['sum'] = '0';

    if(!is_wp_error( $result['records'] ))
    {
        // Return single result if id is set
        if (isset($params['id']))
            $result = $result['records'][0];
        else 
        {            
            // Get adjustment ids
            $adjustment_ids = array();
            if(is_array($result['records']) && !empty($result['records']))
                foreach ($result['records'] as $item)
                    $adjustment_ids[] = $item->id;
                    
            $result['ids'] = $adjustment_ids;
        
            // Get total amount
                $result['sum'] =  is_array($result['records']) && !empty($result['records']) ? array_sum(array_column($result['records'],'amount')) : 0;
        }

    }
    
    return $result;

}

// Handle sortable table item query args 
function arm_handle_sortable_table_url( $item_slug )
{
    // Set args
    $args = array(
        'order_by' => $item_slug,
    );

    if ( !isset($_GET['order_by']) || $_GET['order_by'] != $item_slug  )
    {
        $args['order'] = 'ASC';
        $url = add_query_arg( $args );
        
    } else
    {
        if ( isset($_GET['order']) && $_GET['order'] == 'ASC' )
        {
            $args['order'] = 'DESC';
            $url = add_query_arg( $args );
        }
        else
        {
            $url = remove_query_arg( array('order_by', 'order') );
        }
    }
    
    // Remove paged
    $url = remove_query_arg('paged', $url);
    
    return $url;
}

// Handle sort icon 
function arm_handle_sort_icon_class( $item_slug )
{
    if ( isset($_GET['order_by']) && $_GET['order_by'] == $item_slug  )
    {
        if ( isset($_GET['order']) && $_GET['order'] == 'ASC' )
        {
            $icon_class = 'fa-sort-up arm-sort-active';
        }
        else
        {
            $icon_class = 'fa-sort-down arm-sort-active';
        }

    } else 
    {
        $icon_class = 'fa-sort arm-sort-inactive';
    }
    
    return $icon_class;
    
}


/**
 * Register dashboard widgets.
 */
function arm_register_dashboard_widgets() 
{
    $filter = '';
    
    // Admin - total payable amounth
    if (current_user_can( 'administrator' ))
        wp_add_dashboard_widget( 'widget_total_payable_of_month', __('Total payable of the month', 'rxarm'). $filter, 'arm_render_widget_total_payable_of_month' );
    
    // Admin - authors performance
    if (current_user_can( 'administrator' ))
        wp_add_dashboard_widget( 'widget_authors_performance', __("Authors' performance / Words", 'rxarm'). $filter, 'arm_render_widget_authors_performance' );
    
    // Admin - top 10 authors of month
    if (current_user_can( 'administrator' ))
        wp_add_dashboard_widget( 'widget_topten_authors', __('Top 10 authors of month / Words', 'rxarm'). $filter, 'arm_render_widget_topten_authors' );
    
    // Admin / Author - monthly leaderboard
    if (current_user_can( 'edit_posts' ))
        wp_add_dashboard_widget( 'widget_monthly_leaderboard', __('Monthly leaderboard / Words', 'rxarm'). $filter, 'arm_render_widget_monthly_leaderboard' );
    
    // Author - performance
    if (current_user_can( 'edit_posts' ) && !current_user_can( 'administrator'))
        wp_add_dashboard_widget( 'widget_author_performance', __('My performance / Previous and current month', 'rxarm'). $filter, 'arm_render_widget_author_performance' );
    
    // Author - revenue receipts
    if (current_user_can( 'edit_posts' ) && !current_user_can( 'administrator'))
        wp_add_dashboard_widget( 'widget_author_revenues', __('Revenues', 'rxarm'). $filter, 'arm_render_widget_author_revenues' );
    
    // Author - top 3 authors of month
    if (current_user_can( 'edit_posts' ) && !current_user_can( 'administrator'))
        wp_add_dashboard_widget( 'widget_topthree_authors', __('Top 3 authors of month / Words', 'rxarm'). $filter, 'arm_render_widget_topthree_authors' );
}
add_action( 'wp_dashboard_setup', 'arm_register_dashboard_widgets' );

// Set dashboard widget order
function arm_set_dashboard_order() 
{
    $user_id = get_current_user_id();
    
    // Get the widget setting current status
    $widget_set = get_user_meta( $user_id, 'arm-dashboard-widget-order', true);

    // If it's not set already
    if ($widget_set != 'set')
    {
        // Set administrator widgets order
        if(current_user_can('administrator'))
        $meta_value = array(
            'normal'  => 'widget_total_payable_of_month,widget_authors_performance,dashboard_site_health,dashboard_right_now,dashboard_activity', //first key/value pair from the above serialized array
            'side'    => 'widget_topten_authors,widget_monthly_leaderboard,dashboard_quick_press,dashboard_primary', //second key/value pair from the above serialized array
        );
    
        // Set author widgets order
        if(!current_user_can('administrator') && current_user_can('edit_posts'))
        $meta_value = array(
            'normal'  => 'widget_author_performance,widget_topthree_authors', //first key/value pair from the above serialized array
            'side'    => 'widget_author_revenues,widget_monthly_leaderboard,dashboard_quick_press,dashboard_primary', //second key/value pair from the above serialized array
        );
    
        // Save the order
        update_user_meta( $user_id, 'meta-box-order_dashboard', $meta_value ); 
    
        // Save setting status
        update_user_meta( $user_id, 'arm-dashboard-widget-order', 'set' );
    }

}

add_action( 'admin_init', 'arm_set_dashboard_order' );


/**
 * Render total payable of the month dashboard widget content
 */
function arm_render_widget_total_payable_of_month( $post, $callback_args ) 
{
    // Include view
    include_once( ARM_PLUGIN_PATH. 'views/admin/widgets/total-payable-of-month.php' );
}

/**
 * Render authors' performance dashboard widget content
 */
function arm_render_widget_authors_performance( $post, $callback_args ) 
{
    // Include view
    include_once( ARM_PLUGIN_PATH. 'views/admin/widgets/authors-performance-of-month.php' );
}

/**
 * Render Top 10 authors of month dashboard widget content
 */
function arm_render_widget_topten_authors( $post, $callback_args ) 
{
    // Include view
    include_once( ARM_PLUGIN_PATH. 'views/admin/widgets/topten-authors-of-month.php' );
}

/**
 * Render monthly leaderboard dashboard widget content
 */
function arm_render_widget_monthly_leaderboard( $post, $callback_args ) 
{
    // Include view
    include_once( ARM_PLUGIN_PATH. 'views/admin/widgets/monthly-leaderboard.php' );
}

/**
 * Render author performance dashboard widget content
 */
function arm_render_widget_author_performance( $post, $callback_args ) 
{
    // Include view
    include_once( ARM_PLUGIN_PATH. 'views/admin/widgets/author-performance.php' );
}

/**
 * Render author revenues dashboard widget content
 */
function arm_render_widget_author_revenues( $post, $callback_args ) 
{
    // Include view
    include_once( ARM_PLUGIN_PATH. 'views/admin/widgets/author-revenues.php' );
}

/**
 * Render Top 3 authors of month dashboard widget content
 */
function arm_render_widget_topthree_authors( $post, $callback_args ) 
{
    // Include view
    include_once( ARM_PLUGIN_PATH. 'views/admin/widgets/topthree-authors-of-month.php' );
}

// Render floating button
function arm_render_floating_button( $params = null, $href = 'javascript:void(0)' ) 
{
    // Convert array to html attributes
    if (is_array($params) && !empty($params))
    {
        $attrs = str_replace("=", '="', http_build_query( $params, null, '" ', PHP_QUERY_RFC3986 )).'"';
        $attrs = str_replace("%23", '#', $attrs);
        $attrs = str_replace("%20", ' ', $attrs);
    }

    // Apply params
    $button = '<a href="'.$href.'" '.$attrs.' class="arm-floating-button" style="'.(is_rtl() ? 'left: 40px' : 'right: 40px').'"><span>+</span></a>';

    // Output
    echo $button;
}

// Get current month start datetime (based on payday)
function arm_get_current_month_start_datetime()
{
    // Set start time
    $start_time = '00:00:00';
    
    // Get payday
    $payday = get_option( 'arm_payday');

    // Get previous month
    if (get_locale() == 'fa_IR')
    {
        // Get today in jalali format
        $today_jalali = arm_convert_gregorian_to_jalali(arm_get_current_datetime(false));

        $previous_month = date("Y-m", strtotime($today_jalali." previous month"));
        $month_start_date = date("Y-m-d H:i:s", strtotime( $previous_month.'-'.$payday.' '.$start_time ));
        $month_start_date = arm_convert_jalali_to_gregorian($month_start_date);
    }
    else
    {
        $previous_month = date("Y-m", strtotime("previous month"));
        $month_start_date = date("Y-m-d H:i:s", strtotime( $previous_month.'-'.$payday.' '.$start_time ));
    }


    return $month_start_date;
}

// Get current month end datetime (based on payday)
function arm_get_current_month_end_datetime()
{
    // Set end time
    $end_time = '23:59:59';
    
    // Get payday
    $payday = get_option( 'arm_payday');

    // Get the day before payday of the current month
    if (get_locale() == 'fa_IR')
    {
        // Get today in jalali format
        $today_jalali = arm_convert_gregorian_to_jalali(arm_get_current_datetime(false));

        $month_end_date = date("Y-m-d H:i:s", strtotime( date("Y-m", strtotime($today_jalali)).'-'.$payday.' '.$end_time. ' -1 day'));
        $month_end_date = arm_convert_jalali_to_gregorian($month_end_date);
    }
    else
        $month_end_date = date("Y-m-d H:i:s", strtotime( date("Y-m").'-'.$payday.' '.$end_time. ' -1 day'));
    

    return $month_end_date;
}

// Get current datetime
function arm_get_current_datetime( $with_time = true )
{
    // Get current date
    $result = current_time('mysql');

    if ( false === $with_time )
        $result = date('Y-m-d', strtotime($result));

    return $result;
}

// Get month's first day
function arm_get_month_first_day_date( $month_number, $is_jalali )
{
    // Get current date
    $current_date = arm_get_current_datetime();
    
    // Convert gregorian to jalali
    if ($is_jalali)
        $current_date = arm_convert_gregorian_to_jalali(arm_get_current_datetime());

    // Set first day date
    $month_first_day_date = date("Y", strtotime($current_date)).'-'.$month_number.'-01';

    // Convert jalali to gregorian
    if ($is_jalali)
        $month_first_day_date = arm_convert_jalali_to_gregorian($month_first_day_date);

    return $month_first_day_date;
}

// Get month's last day
function arm_get_month_last_day_date( $month_number, $is_jalali )
{
    // Get current date
    $current_date = arm_get_current_datetime();
    
    // Get month last day
    $month_last_day_date = date("Y", strtotime( $current_date )).'-'.$month_number.'-'.date("t", strtotime( $current_date ));

    // Convert gregorian to jalali
    if ($is_jalali)
    {
        // Set the last day
        $month_last_day_date = date("Y", strtotime( arm_convert_gregorian_to_jalali($current_date ))).'-'.$month_number.'-28';

        // Convert jalali to gregorian
        $month_last_day_date = arm_convert_jalali_to_gregorian($month_last_day_date);
    }

    return $month_last_day_date;
}

// Get date month number
function arm_get_date_month_number( $date, $is_jalali )
{
    if ($is_jalali)
        $month_number = date('m', strtotime(arm_convert_gregorian_to_jalali($date)));
    else
        $month_number = date('m', strtotime($date));

    return $month_number;
}

// Render adjustment content insid pill
function arm_render_adjustment( $adjustment )
{
    ?>
        <div class="row arm-pill pill-<?php echo $adjustment->adjustment == 'bonus' ? 'green' : 'red' ?> mb-4">
            <div class="col-4 text-start">
                <div class="pill-title"><?php _e($adjustment->reason, 'rxarm') ?></div>
                <div class="pill-value fw-bold"><?php echo $adjustment->minimum_words ? arm_percent_diff( $adjustment->minimum_words, $adjustment->written_words) : '' ?></div>
            </div>
            <div class="col-4 text-center">
                <div class="pill-title"><?php echo $adjustment->minimum_words ? ($adjustment->written_words > $adjustment->minimum_words ? __('Extra words written', 'rxarm') : __('Less words written', 'rxarm')) : '' ?></div>
                <div class="pill-value"><?php echo $adjustment->minimum_words ? number_format(($adjustment->written_words - $adjustment->minimum_words)) : '' ?></div>
            </div>
            <div class="col-4 text-end">
                <div class="pill-title"><?php echo sprintf(__('%s amount', 'rxarm'), __(ucfirst($adjustment->adjustment),'rxarm')) ?></div>
                <div class="pill-value fw-bold"><?php echo $adjustment->adjustment == 'penalty' ? '-' : '' ?><?php echo arm_format_price( $adjustment->amount , arm_get_author_locale($adjustment->author_id) ) ?></div>
            </div>
        </div>
    <?php
}

// Calculate percent difference between two numbers
function arm_percent_diff( $num1, $num2, $symbol = true )
{
    // Formula
    if( $num1 <= 0 && $num2 > 0)
        $percent = 100;
        
    elseif( $num1 > 0 && $num2 <= 0)
        $percent = -100;
        
    elseif( $num1 == 0 && $num2 == 0)
        $percent = 0;

    else
        $percent = (1 - $num1 / $num2) * 100;
    
    if ($symbol)
        $symbol = '%';
    else    
        $symbol = '';

    // Round up
    $percent = round($percent, 0).$symbol;

    return $percent;
}

// Render pagination
function arm_pagination( $total_records )
{
    $total = ($total_records % arm_get_per_page() > 0 ? $total_records / arm_get_per_page() + 1 : $total_records / arm_get_per_page());

	$args = array(
			'base'         => '%_%',
			'total'        => $total,
			'current'      => arm_get_paged(),
			'format'       => '?paged=%#%',
			'show_all'     => ($total > 5 ? false : true),
			'type'         => 'array',
			'end_size'     => 2,
			'mid_size'     => 1,
			'prev_next'    => true,
			'add_args'     => false,
			'add_fragment' => '',
            'prev_text' => __('Previous', 'rxarm' ),
            'next_text' => __('Next', 'rxarm' ),
		);

	echo '<div class="arm-pagination mt-5">';

	$pages = paginate_links( $args );

    if( is_array( $pages ) ) 
    {
        $paged = arm_get_paged();

        echo '<ul class="pagination">';
        
        foreach ( $pages as $page ) 
        {
                echo '<li class="page-item">'.$page.'</li>';
        }
        
        echo '</ul>';
    }

	echo '</div>';

}

// Get paged
function arm_get_paged()
{
    return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
}

// Calculate pagination offset
function arm_get_pagination_offset()
{
    // Get page number
    $page_number = arm_get_paged();

    // Get offset
    $offset = ($page_number - 1) * arm_get_per_page();

    return $offset;
}

// Get per_page from query var
function arm_get_per_page()
{
    $per_page = (isset($_GET['per_page']) ? absint($_GET['per_page']) : 10);
    
    return $per_page;
}


// Calculate and create adjustments and revenue receipts
function arm_do_accounting( $authors, $range_start_datetime, $range_end_datetime)
{
    // Generate automatic adjustments
    foreach ($authors as $author) 
    {
        // Create adjustment
        arm_create_automatic_adjustment( $author->data->ID, $range_start_datetime, $range_end_datetime );
    }
    
    // Generate revenue receipts
    foreach ($authors as $author) 
    {
        // Create revenue receipt
        arm_create_revenue_receipt( $author->data->ID, $range_start_datetime, $range_end_datetime );
    }
}


// Automatically calculate and create adjustments and revenue receipts 
function arm_do_automatic_monthly_accounting()
{
    // Get dotay's day
    if (get_locale() == 'fa_IR')
    {
        // Get today in jalali format
        $today_jalali = arm_convert_gregorian_to_jalali(arm_get_current_datetime(false));

        $today_day = date("d", strtotime($today_jalali));
    }
    else
        $today_day = date("d");
    
    // Get payday
    $payday = get_option( 'arm_payday' );

    // Get today's date
    $today_date = arm_get_current_datetime(false);

    // Get last payday
    $last_payday = get_option('arm_last_payday');
    
    // If today is payday and the procedure is not already done today
    if( $today_day == $payday && $today_date != $last_payday )
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
}

add_action( 'arm_daily_cron', 'arm_do_automatic_monthly_accounting' );

// Do redirection
function arm_redirect( $url_params )
{
    // Redirect
    wp_redirect( add_query_arg( $url_params, admin_url('admin.php') ) );
    exit;
}

// Show alert
function arm_show_alert()
{
    // Get session alert
    $alert = arm_get_alert();

    if (is_array($alert) && !empty($alert))
    {
        // Get alert
        $alert = $_SESSION['arm-alert'];

        // Render alert
        arm_render_alert( $alert['type'], $alert['message'] );
    }

    // Flush alert
    arm_flush_alert();
}

// Render alert
function arm_render_alert( $type, $message )
{
    // Output
    echo '<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">
        '.$message.'
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// Set session alert
function arm_set_alert( $type, $message )
{
    $_SESSION['arm-alert'] = array( 
        'type' => $type,
        'message' => $message 
    );
}

// Get session alert
function arm_get_alert()
{
    if(isset($_SESSION['arm-alert']))
    return $_SESSION['arm-alert'];
}

// Flush session alert
function arm_flush_alert()
{
    unset($_SESSION['arm-alert']);
}

// Upload file
function arm_upload( $file, $prefix = null )
{
    try 
    {
        // Get uploads directory
        $target_dir =  ARM_UPLOADS;

        // Create subdirectory if not exists
        if (!file_exists($target_dir)) 
            mkdir($target_dir, 0777, true);

        // Change file name 
        $file_name = time();
        $file["name"] = $prefix.($prefix ? '-' : '').$file_name.'.'.pathinfo($file["name"], PATHINFO_EXTENSION);
        
        // Get file
        $target_file = $target_dir . basename($file["name"]);

        // Get file type
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is an actual image or a fake image
        if(!$file['tmp_name'])
            throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('File', 'rxarm')), 1);
            $check = getimagesize($file['tmp_name']);

        if( $check === false ) 
            throw new Exception(__('Please select an image.', 'rxarm'), 1);

        // Check if file already exists
        if (file_exists($target_file)) 
            throw new Exception(__('Sorry, file already exists.', 'rxarm'), 1);

        // Check file size
        if ($file["size"] > 1000000) 
            throw new Exception(__('Sorry, your file is too large.', 'rxarm'), 1);

        // Allow certain file formats
        if( !in_array($imageFileType, array('jpeg', 'jpg', 'png')) )
            throw new Exception(__('Sorry, only JPG, JPEG and PNG files are allowed.', 'rxarm'), 1);

        // Upload file
        if (move_uploaded_file($file["tmp_name"], $target_file)) 
        {
            $result = array(
                'success' => true,
                'attachment_id' => htmlspecialchars( basename( $file["name"])),
            );
        }
        else
        {
            throw new Exception(__('Sorry, there was an error uploading your file.', 'rxarm'), 1);
        }

    } catch (\Throwable $ex) 
    {
        $result = array(
            'success' => false,
            'message' => $ex->getMessage(),
        );
    }

    return $result;
    
}

// Get auto adjustment rules
function arm_get_auto_adjustment_rules( $adjustment )
{
    $rules = explode(';',get_option('arm_auto_'.$adjustment.'_rules'));

    $rules_arr = array();

    foreach ($rules as $rule) 
    {
        $rule = explode(',',$rule);
        $rules_arr[] = (object) array(
            'min' => $rule[0],
            'max' => $rule[1],
            'percent' => $rule[2],
        );
    }

    return $rules_arr;
}

// Get rule percentage by diff
function arm_get_rule_percent( $adjustment, $diff )
{
    // Get adjustment rules
    $rules = arm_get_auto_adjustment_rules( $adjustment );

    if (is_array($rules) && !empty($rules))
    {
        foreach ($rules as $rule) 
        {
            if ( ($rule->min <= $diff) && ($diff <= $rule->max) )
            {
                $percent = $rule->percent;                
            }
        }

    }

    return $percent;
}

// Get leaderboard months
function arm_get_leaderboard_months( $is_jalali = false, $month_name = false )
{
    // Get months
    $dates = arm_get_revenue_receipts( array('fields_arr'=>'created_at'))['records'];

    // Get months
    $months = array();

    if (is_array($dates) && !empty($dates))
    {
        foreach ($dates as $date) 
        {

            // Get month number
            $month = arm_get_date_month_number( $date, $is_jalali );
            $months[] = $month;          
        }    
        
        // Remove duplicates
        $months = array_unique($months);

        // Prepare months array
        foreach ($months as $month) 
        {
            // Get month name and number
            if ($month_name)
                $month = array(
                    'number' => $month,
                    'name' => arm_get_month_name( $month, $is_jalali ),
                );

            $months_list[] = $month;          
        }    
    }

    return $months_list;
}

// Get month name
function arm_get_month_name( $month_number, $is_jalali = false)
{
    if ($is_jalali)
    {
        $months = array(
            '01' => __('Farvardin', 'rxarm'),
            '02' => __('Ordibehesht', 'rxarm'),
            '03' => __('Khordad', 'rxarm'),
            '04' => __('Tir', 'rxarm'),
            '05' => __('Mordad', 'rxarm'),
            '06' => __('Shahrivar', 'rxarm'),
            '07' => __('Mehr', 'rxarm'),
            '08' => __('Aban', 'rxarm'),
            '09' => __('Azar', 'rxarm'),
            '10' => __('Dey', 'rxarm'),
            '11' => __('Bahman', 'rxarm'),
            '12' => __('Esfand', 'rxarm'),
        );

        $month_name = $months[$month_number];
    } 
    else
    {
        $dateObj   = DateTime::createFromFormat('!m', $month_number);
        $month_name = $dateObj->format('F');
    }

    return $month_name;
}

// Get contact options
function arm_get_contact_options()
{
    $options = get_option( 'arm_contact_options' );

    return $options;
}

// Get author currency symbol
function arm_get_author_currency_symbol( $author_id )
{
    // Get author's locale
    $author_locale = arm_get_author_locale( $author_id );

    // Get currency
    $currency = get_option('arm_currency_'.$author_locale);

    // Get currency symbol
    $currency_symbol = arm_get_currency_symbol( $currency );

    return $currency_symbol;
}

// Activate SESSIONS when WP is initialized
function arm_session_start()
{

	if ( ! session_id()) {
		
		session_start();
	}
}

add_action('init', 'arm_session_start', 1);

// Deactivate SESSIONS on User logout
function arm_session_end()
{

	if (session_id()) {

		session_destroy();
	}
}

add_action('wp_logout', 'arm_session_end');

// Get calendar
function arm_get_calendar()
{
    return get_locale() == 'fa_IR' ? 'jalali' : 'gregorian';
}

function arm_submit_bonus( $no_nonce = false )
{
  try 
  {
      // Validate nonce
      if( $no_nonce === false && (!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'arm-nonce-bonus' )))
        throw new Exception(__('Security check failure.', 'rxarm'), 1);
    
      // Check accessibility 
      if(!current_user_can( 'administrator' ))
          throw new Exception(__('You are not allowed to perform this action.', 'rxarm'), 1);
  
      // Author
      if(!isset($_POST['author']) || empty($_POST['author']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Author', 'rxarm')), 1);
      $author = absint( $_POST['author'] );
  
      // Amount
      if(!isset($_POST['amount']) || empty($_POST['amount']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Amount', 'rxarm')), 1);
      $amount = absint( str_replace(',','',$_POST['amount']) );
  
      // Reason
      if(!isset($_POST['reason']) || empty($_POST['reason']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Reason', 'rxarm')), 1);
      $reason = sanitize_text_field( $_POST['reason'] );

      // Insert bonus
      $params = array(
        'adjustment' => 'bonus',
        'author_id' => $author,
        'amount' => $amount,
        'reason' => $reason,
        'is_auto' => '0',
        'is_accounted' => '0',
      );

      $result = arm_insert_adjustment( $params );

      if (!is_wp_error( $result ))
        $result = array('type'=>'success', 'message'=>__('Bonus has been submitted.', 'rxarm'));
      else
        throw new Exception( __('Operation was unsuccesful. Please try again.', 'rxarm'), 1);

  } catch (\Throwable $ex) 
  {
      $result = array('type'=>'warning', 'message'=>$ex->getMessage());
  }

  return $result;
}

// Repeal bonus
function arm_repeal_bonus( $no_nonce = false )
{

    try 
    {
        // Validate nonce
        if( $no_nonce === false && (!isset($_GET['_wpnonce']) || empty($_GET['_wpnonce']) || !wp_verify_nonce( $_GET['_wpnonce'], 'arm-repeal-bonus' )))
            throw new Exception(__('Security check failure.', 'rxarm'), 1);
        
        // Check accessibility 
        if(!current_user_can( 'administrator' ))
            throw new Exception(__('You are not allowed to perform this action.', 'rxarm'), 1);
  
        // Check bonus id 
        if(!isset($_GET['id']) || empty($_GET['id']))
            throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Bonus id', 'rxarm')), 1);
        $bonus_id = absint($_GET['id']);

        // Get bonus
        $bonus = arm_get_adjustments( array('id'=>$bonus_id) );
    
        // Validate bonus
        if(!$bonus)
            throw new Exception( sprintf(__('%s does not exist.', 'rxarm'), __('Bonus', 'rxarm')), 1);
    
        // Check bonus accounting status
        if($bonus->is_accounted == 1)
          throw new Exception( sprintf(__('This %s is accounted and can not get repealed.', 'rxarm'), __('Bonus', 'rxarm')), 1);
  
        // Delete bonus 
        $result = arm_delete_adjustment( $bonus_id );
  
        if (!is_wp_error( $result ))
          $result = array('type'=>'success', 'message'=>__('Bonus has been repealed.', 'rxarm'));
        else
          throw new Exception( __('Operation was unsuccesful. Please try again.', 'rxarm'), 1);
  
    } catch (\Throwable $ex) 
    {
        $result = array('type'=>'warning', 'message'=>$ex->getMessage());
    }
  
    return $result;
}

// Submit penalty
function arm_submit_penalty( $no_nonce = false )
{
  try 
  {
      // Validate nonce
      if( $no_nonce === false && ( !isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'arm-nonce-penalty' )))
          throw new Exception(__('Security check failure.', 'rxarm'), 1);
      
      // Check accessibility 
      if(!current_user_can( 'administrator' ))
          throw new Exception(__('You are not allowed to perform this action.', 'rxarm'), 1);
  
      // Author
      if(!isset($_POST['author']) || empty($_POST['author']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Author', 'rxarm')), 1);
      $author = absint( $_POST['author'] );
  
      // Amount
      if(!isset($_POST['amount']) || empty($_POST['amount']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Amount', 'rxarm')), 1);
      $amount = absint( str_replace(',','',$_POST['amount']) );
  
      // Reason
      if(!isset($_POST['reason']) || empty($_POST['reason']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Reason', 'rxarm')), 1);
      $reason = sanitize_text_field( $_POST['reason'] );

      // Insert penalty
      $params = array(
        'adjustment' => 'penalty',
        'author_id' => $author,
        'amount' => $amount,
        'reason' => $reason,
        'is_auto' => '0',
        'is_accounted' => '0',
      );

      $result = arm_insert_adjustment( $params );

      if (!is_wp_error( $result ))
        $result = array('type'=>'success', 'message'=>__('Penalty has been submitted.', 'rxarm'));
      else
        throw new Exception( __('Operation was unsuccesful. Please try again.', 'rxarm'), 1);

  } catch (\Throwable $ex) 
  {
      $result = array('type'=>'warning', 'message'=>$ex->getMessage());
  }

  return $result;
}

// Repeal penalty
function arm_repeal_penalty( $no_nonce = false )
{

    try 
    {
        // Validate nonce
        if( $no_nonce === false && ( !isset($_GET['_wpnonce']) || empty($_GET['_wpnonce']) || !wp_verify_nonce( $_GET['_wpnonce'], 'arm-repeal-penalty' )))
            throw new Exception(__('Security check failure.', 'rxarm'), 1);
        
        // Check accessibility 
        if(!current_user_can( 'administrator' ))
            throw new Exception(__('You are not allowed to perform this action.', 'rxarm'), 1);
    
        // Get penalty id
        $penalty_id = absint($_GET['id']);

        // Get penalty
        $penalty = arm_get_adjustments( array('id'=>$penalty_id) );
    
        // Validate penalty
        if(!$penalty)
            throw new Exception( sprintf(__('%s does not exist.', 'rxarm'), __('Penalty', 'rxarm')), 1);
    
        // Check penalty accounting status
        if($penalty->is_accounted == 1)
          throw new Exception( sprintf(__('This %s is accounted and can not get repealed.', 'rxarm'), __('Penalty', 'rxarm')), 1);
  
        // Delete penalty 
        $result = arm_delete_adjustment( $penalty_id );
  
        if (!is_wp_error( $result ))
          $result = array('type'=>'success', 'message'=>__('Penalty has been repealed.', 'rxarm'));
        else
          throw new Exception( __('Operation was unsuccesful. Please try again.', 'rxarm'), 1);
  
    } catch (\Throwable $ex) 
    {
        $result = array('type'=>'warning', 'message'=>$ex->getMessage());
    }
  
    return $result;
}

// Submit transaction
function arm_submit_transaction( $author_id, $revenue_number, $amount, $no_nonce = false )
{
  try 
  {
      // Validate nonce
      if( $no_nonce === false && ( !isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'arm-nonce-transaction' )))
          throw new Exception(__('Security check failure.', 'rxarm'), 1);
              
      // Check accessibility 
      if(!current_user_can( 'administrator' ))
          throw new Exception(__('You are not allowed to perform this action.', 'rxarm'), 1);

      // Attachment file 
      if(!isset($_FILES['file']) || empty($_FILES['file']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('File', 'rxarm')), 1);
          
      // Method 
      if(!isset($_POST['method']) || empty($_POST['method']))
          throw new Exception( sprintf(__('%s is required.', 'rxarm'), __('Method', 'rxarm')), 1);
          $method = sanitize_text_field( $_POST['method'] );

      // Upload attachment
      $upload_result = arm_upload( $_FILES['file'], 'rev-'.$revenue_number );

      if ( $upload_result['success'] === false )
          throw new Exception( $upload_result['message'], 1);

      // Attachment id
      $attachment_id = $upload_result['attachment_id'];

      // Ref number
      $ref_number = sanitize_text_field( $_POST['ref_number'] );

      // Insert transaction
      $params = array(
        'author_id' => $author_id,
        'revenue_number' => $revenue_number,
        'amount' => $amount,
        'method' => $method,
        'ref_number' => $ref_number,
        'attachment_id' => $attachment_id
      );

      $result = arm_insert_transaction( $params );

      if (!$result)
        throw new Exception( __('Operation was unsuccesful. Please try again.', 'rxarm'), 1);
        
        
      // Update rceipt status
      $update_result = arm_update_revenue_receipt( array('id'=>$revenue_number, 'status'=>'paid') );
        
      $result = array('type'=>'success', 'message'=>__('Transaction has been submitted.', 'rxarm'));


  } catch (\Throwable $ex) 
  {
      $result = array('type'=>'warning', 'message'=>$ex->getMessage());
  }

  return $result;
}