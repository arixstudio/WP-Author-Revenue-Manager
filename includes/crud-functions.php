<?php defined( 'ABSPATH' ) or exit;

/*
* Functions
*
*/

// Insert data function  
function arm_insert( $params , $mysql_error = false ) 
{
    // Hint >> params : table_name , col_arr , col_format_arr ;
    
    global $wpdb;

    // Get db name
    $table = $wpdb ->prefix . $params['table_name'];

    // Get query result
    $result =  $wpdb->insert(

        $table,

        // Notice that $col_arr and $col_format_arr must be in same size

        $params['col_arr'], // Associative array that it's users depending on table is same as : 'title' => 'rxarm'

        $params['col_format_arr'] // Indexed array that it's users is same as : '%s' 

    );

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error) )
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }

    // Empty result error
    if( empty($result) )  
        return new WP_Error( "No_data_saved", __( "No data saved", "rxarm" ) );

    return $wpdb->insert_id;

}

// Update data function  
function arm_update( $params, $mysql_error = false ) 
{
    
    global $wpdb;

    // Get db name
    $table = $wpdb -> prefix . $params['table_name'];

    if ( empty($params['where_col_arr']) || !is_array( $params['where_col_arr'] ))
        return new WP_Error( "cant__update", __( "Update failed.", "rxarm" ) );

    // Get query result
    $result =  $wpdb->update(

        $table,

        // Notice that $col_arr / $where_col_arr and $col_format_arr / $where_col_format_arr must be in same size
        
        $params['col_arr'], // Associative array that it's users is same as : 'title' => 'rxarm'

        $params['where_col_arr'], // Associative array that it's users depending on table is same as : 'title' => 'rxarm'

        $params['col_format_arr'], // Indexed array that it's users is same as : '%s' 

        $params['where_col_format_arr'] // Indexed array that it's users is same as : '%s' 

    );

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error))
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }
    
    // Empty result error
    if( empty($result) )  
        return new WP_Error( "No_data_updated", __( "No data updated.", "rxarm" ) );

    return $result;

}

// Delete data function  
function arm_delete( $params, $mysql_error = false ) 
{
    // Hint >> params : table_name , where_col_arr , col_format_arr ;
    
    global $wpdb;

    // Get db name
    $table = $wpdb -> prefix . $params['table_name'];

    if ( empty($params['where_col_arr']) || !is_array( $params['where_col_arr'] ) )
        return new WP_Error( "cant__delete", __( "Delete failed.", "rxarm" ) );

    // Get query result
    $result = $wpdb->delete(

        $table,
    
        // Notice that $col_arr and $col_format_arr must be in same size

        $params['where_col_arr'], // Associative array that it's users depending on table is same as : 'title' => 'rxarm' 

        $params['col_format_arr'] // Indexed array that it's users is same as : '%s'

    );

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error) )
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }

    // Empty result error
    if( empty($result) )  
        return new WP_Error( "No_data_deleted", __( "No data deleted.", "rxarm" ) );

    return $result;

}

// Delete data function for array meta values
function arm_delete_for_array_meta_values( $params, $mysql_error = false )
{

    // Hint >> params : table_name , from_col , where_col_arr , variables_arr, limit = null , offset = null, orderby = null, order = null, format = false);
    global $wpdb;

    // Get db name
    $table = $wpdb ->prefix . $params['table_name'];

    // Query 
    $query = "DELETE FROM {$table}" ; 

    if ( empty($params['where_col_arr']) || !is_array( $params['where_col_arr'] ))
        return new WP_Error( "cant__delete", __( "Delete failed.", "rxarm" ) );

    $query .= " WHERE " ;

    $values_arr = array();
    
    foreach( $params['where_col_arr'] as $key => $item )
    {

        if( is_array( $item['val'] ) && !empty( $item['val'] ) )
        {
            // Set format %d, %f, %s
            if( $item['format'] )
            {

                $format =  '(' .  implode( "," , array_fill( 0, count($item['val']), $item['format']) ). ')';

            } else {

                $format = '(' ;

                foreach( $item['val'] as $k => $v )
                {
                    if( gettype( $item['val'] ) == 'string' )
                        $format .= '%s';
                    else if( gettype( $item['val'] ) == 'integer' )  
                        $format .= '%d';
                    else 
                        $format .= '%f';

                    if( count($item['val']) > $k + 1 )
                        $format .= ',';

                }

                $format .= ')' ;
            }
            
            // Set operation 
            $operation = $item['operation'] ? $item['operation'] : 'IN' ;

            // Set relation
            $relation = ( count($params['where_col_arr']) > 1 && count($params['where_col_arr']) > $key + 1 ) ? ( $item['relation'] ? $item['relation'] : ' AND ') : '';

            $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

            // Add values
            $values_arr = array_merge( $values_arr , $item['val'] );

        } else
        {
            // Set format %d, %f, %s
            $format = $item['format'] ? $item['format'] : ( gettype( $item['val'] ) == 'string' ? '%s' : ( gettype( $item['val'] ) == 'integer' ? '%d' : '%f' ) ) ;
            
            // Set operation 
            $operation = $item['operation'] ? $item['operation'] : '=' ;

            // Set relation
            $relation = ( count($params['where_col_arr']) > 1 && count($params['where_col_arr']) > $key + 1 ) ? ( $item['relation'] ? $item['relation'] : ' AND ') : '';

            $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

            // Add values
            $values_arr[] = $item['val'];
        }
    }

    // Set safe query. Example : $wpdb -> prepare(" SELECT * FROM title = '%s' ", $variables_arr );
    $safequery = $wpdb->prepare(" $query ", !empty($values_arr) ? $values_arr : array() ); // $variables_arr is array of variables

    $result = $wpdb->get_results($safequery);

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error) )
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }

    // Empty result error
    if( empty($result) )  
        return new WP_Error( "No_data_deleted", __( "No data deleted.", "rxarm" ) );

    return $result;
   
}

// Read data function
function arm_read( $params, $mysql_error = false )
{
    $d_counter = 1;
    $s_counter = 1;
    $f_counter = 1;
    $d_count = 0;
    $s_count = 0;
    $f_count = 0;

    if ( isset($params['limit']) && !empty($params['limit']) )
        $d_count +=1;

    if(isset($params['offset']))
        $d_count +=1;



    // Hint >> params : table_name , fields_arr , where_col , variables_arr, limit = null , offset = null, orderby = null, order = null, format = false);
    global $wpdb;

    // Get db name
    $table = $wpdb ->prefix . $params['table_name'];

    $fields_arr = isset($params['fields_arr']) ? $params['fields_arr'] : '';
    
    // Set default from_col_arr as *
    if ( empty( $params['fields_arr'] ) )
    {
        
        $fields = ' * ';
    }
    else
    {
        // Is fields array
        if( !is_array($fields_arr) )
        {
            $fields = sanitize_text_field( $fields_arr );

        } else
        {

            // Sanitize Array Values
            $sanitized_fields_arr = array();
    
            foreach( $fields_arr as $item )
            {
                $sanitized_fields_arr[] = sanitize_text_field( $item );
            }
    
            $fields = ' ' . implode( ", " , $sanitized_fields_arr ) . ' ';
        } 


    }

    // Query 
    $query = "SELECT {$fields} FROM {$table}" ; // $from_col is string as : '*' or 'id, title'

    // Get where col array
    $where_col_arr = $params['where_col_arr'];

    if ( is_array( $where_col_arr ) && !empty( $where_col_arr ) ) // $where_col is string as : ' title like "%s" OR id = "%d" '
    {
        $query .= " WHERE " ;
        
        $values_arr = array();

        foreach( $where_col_arr as $key => $item )
        {
            $item_format = sanitize_text_field( $item['format'] );

            if( empty($item_format))
                return new WP_Error( "format_error", __( "The data format is not specified correctly.", "rxarm" ) );

            if( is_array( $item['val'] ) && !empty( $item['val'] ) )
            {

                if( count($item['val']) > 1 )
                {
                    if( $item_format == '%d' )
                    {
                        $d_count += count($item['val']);
                    } else if( $item_format == '%s' )
                    {
                        $s_count += count($item['val']);
                    }else if( $item_format == '%f' )
                    {
                        $f_count += count($item['val']);
                    }

                } else 
                {
                    if( $item_format == '%d' )
                    {
                        $d_count += 1;
                    } else if( $item_format == '%s' )
                    {
                        $s_count += 1;
                    }else if( $item_format == '%f' )
                    {
                        $f_count += 1;
                    }
                }

            } else
            {
                
                if( $item_format == '%d' )
                {
                    $d_count += 1;

                } else if( $item_format == '%s' )
                {
                    $s_count += 1;
                }else if( $item_format == '%f' )
                {
                    $f_count += 1;
                }

            }
        }

        foreach( $where_col_arr as $key => $item )
        {

            $item_format = sanitize_text_field( $item['format'] );

            if( is_array( $item['val'] ) && !empty( $item['val'] ) )
            {

                // Set 
                $format =  '(';

                for( $my_index = 0; $my_index < count($item['val']); $my_index++ )
                {

                    if( $item_format == '%d' )
                    {
                    
                        $format .= $d_count > 1 ? $d_counter . $item_format : $item_format ;
                        
                        $d_counter += 1;

                    } else if($item_format == '%s'){

                        $format .= $s_count > 1 ? $s_counter . $item_format : $item_format ;
                        
                        $s_counter += 1;

                    } else if( $item_format == '%f' ) {

                        $format = $f_count > 1 ? $f_counter . $item_format : $item_format ;

                        $f_counter += 1;
                    }

                    if( $my_index < count($item['val']) - 1 )
                        $format .= ',';

                } 

                $format .=  ')';

                // Set operation 
                $operation = $item['operation'] ? $item['operation'] : 'IN' ;

                // Set relation
                $relation = ( count( $where_col_arr ) > 1 && count( $where_col_arr ) > $key + 1 ) ? ( $item['relation'] ? $item['relation'] : ' AND ' ) : '';

                $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

                // Add values
                $values_arr = array_merge( $values_arr , $item['val'] );

            } else
            {
                $format = '';

                // Set format %d, %f, %s

                if( $item_format == '%d' )
                {
                    $format .= $d_count > 1 ? $d_counter . $item_format : $item_format ;

                    $d_counter += 1;

                } else if($item_format == '%s'){

                    $format .= $s_count > 1 ? $s_counter . $item_format : $item_format ;

                    $s_counter += 1;

                } else if( $item_format == '%f' ) {

                    $format = $f_count > 1 ? $f_counter . $item_format : $item_format ;

                    $f_counter += 1;
                }
                
                // Set operation 
                $operation = isset($item['operation']) ? $item['operation'] : '=' ;

                // Set relation
                $relation = ( count( $where_col_arr ) > 1 && count( $where_col_arr ) > $key + 1 ) ? ( isset($item['relation']) ? $item['relation'] : ' AND ') : '';

                $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

                // Add values
                $values_arr[] = $item['val'];
            }

        }
    }
    
    if ( isset($params['orderby']) && !empty($params['orderby']) && isset($params['order']) && !empty($params['order'])  )
    {
        $query .= ' ORDER BY ' . sanitize_text_field( $params['orderby'] );
        
        $query .= ' ' . sanitize_text_field( $params['order'] );
    } else {        
        $query .= ' ORDER BY id DESC';
    }

    if ( isset($params['limit']) && !empty($params['limit']) )
    {
        $query .= $d_count > 1 ? ' LIMIT '. $d_counter.'%d' : ' LIMIT %d';
        $d_counter += 1;

        $values_arr[] =  absint($params['limit']);
                
    }
    
    if ( isset($params['offset']) )
    {
        $query .= $d_count > 1 ? ' OFFSET '.$d_counter.'%d' : ' OFFSET %d';
        $d_counter += 1;

        $values_arr[] =  absint($params['offset']);
    }       
    
    // Set safe query. Example : $wpdb -> prepare(" SELECT * FROM title = '%s' ", $variables_arr );
    $safequery = $wpdb->prepare(" $query ", !empty($values_arr) ? $values_arr : array() ); // $variables_arr is array of variables

    if (isset($params['format']))
    {
        $result = $wpdb->get_results($safequery , $params['format']);
    } else 
    {
        $result = $wpdb->get_results($safequery);
    }

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error) )
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }

    if( empty($result) || !is_array($result) )
        return new WP_Error( "no_data_found", __( "No data found.", "rxarm" ) );

    return $result;
}

// Read data function
function arm_read_col( $params, $mysql_error = false )
{
    // Hint >> params : table_name , fields_arr , where_col , variables_arr, limit = null , offset = null, orderby = null, order = null, format = false);
    global $wpdb;

    // Get db name
    $table = $wpdb ->prefix . $params['table_name'];

    $fields_arr = $params['fields_arr'];
    
    // Set default from_col_arr as *
    if ( empty( $params['fields_arr'] ) )
    {
        
        $fields = ' * ';
    }
    else
    {
        // Is fields array
        if( !is_array($fields_arr) )
        {
            $fields = sanitize_text_field( $fields_arr );

        } else
        {

            // Sanitize Array Values
            $sanitized_fields_arr = array();
    
            foreach( $fields_arr as $item )
            {
                $sanitized_fields_arr[] = sanitize_text_field( $item );
            }
    
            $fields = ' ' . implode( ", " , $sanitized_fields_arr ) . ' ';
        } 


    }

    // Query 
    $query = "SELECT {$fields} FROM {$table}" ; // $from_col is string as : '*' or 'id, title'

    // Get where col array
    $where_col_arr = $params['where_col_arr'];

    if ( is_array( $where_col_arr ) && !empty( $where_col_arr ) ) // $where_col is string as : ' title like "%s" OR id = "%d" '
    {
        $query .= " WHERE " ;
        
        $values_arr = array();
    
        foreach( $where_col_arr as $key => $item )
        {

            if( empty($item['format']))
                return new WP_Error( "format_error", __( "The data format is not specified correctly.", "rxarm" ) );

            if( is_array( $item['val'] ) && !empty( $item['val'] ) )
            {
                
                // Set 
                $format =  '(' .  implode( "," , array_fill( 0, count($item['val']), sanitize_text_field( $item['format']) ) ). ')';

                // Set operation 
                $operation = $item['operation'] ? $item['operation'] : 'IN' ;

                // Set relation
                $relation = ( count( $where_col_arr ) > 1 && count( $where_col_arr ) > $key + 1 ) ? ( $item['relation'] ? $item['relation'] : ' AND ' ) : '';

                $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

                // Add values
                $values_arr = array_merge( $values_arr , $item['val'] );

            } else
            {
                // Set format %d, %f, %s
                $format = sanitize_text_field( $item['format'] ) ;
                
                // Set operation 
                $operation = $item['operation'] ? $item['operation'] : '=' ;

                // Set relation
                $relation = ( count( $where_col_arr ) > 1 && count( $where_col_arr ) > $key + 1 ) ? ( $item['relation'] ? $item['relation'] : ' AND ') : '';

                $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

                // Add values
                $values_arr[] = $item['val'];
            }
        }
    }
    
    if ( isset($params['orderby']) && !empty($params['orderby']) && isset($params['order']) && !empty($params['order'])  )
    {
        $query .= ' ORDER BY ' . sanitize_text_field( $params['orderby'] );

        $query .= ' ' . sanitize_text_field( $params['order'] );
    }    

    if ( isset($params['limit']) && !empty($params['limit']) )
    {
        $query .= ' LIMIT %d';

        $values_arr[] =  absint($params['limit']);
                
    }
    
    if ( isset($params['offset']) && !empty($params['offset']) )
    {
        $query .= ' OFFSET %d';

        $values_arr[] =  absint($params['offset']);
    }       
  
    $safequery = $wpdb->prepare( " $query ", !empty($values_arr) ? $values_arr : array() );

    $result = $wpdb->get_col( $safequery );

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error) )
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }

    if( empty($result) || !is_array($result) )
        return new WP_Error( "no_data_found", __( "No data found.", "rxarm" ) );

    return $result;
}

// Count data function
function arm_count( $params, $mysql_error = false )
{
    // Hint >> params : table_name , field , where_col , variables_arr, limit = null , offset = null, orderby = null, order = null, format = false);
    global $wpdb;

    // Get db name
    $table = $wpdb ->prefix . $params['table_name'];

    // Set default from_col_arr as *
    if ( empty( $params['field'] ) )
    {
        
        $field = ' * ';
    }
    else
    {
        
        $field = sanitize_text_field( $params['field'] );

    }

    // Query 
    $query = "SELECT {$field} FROM {$table}" ; // $from_col is string as : '*' or 'id, title'

    // Get where col array
    $where_col_arr = $params['where_col_arr'];

    if ( is_array( $where_col_arr ) && !empty( $where_col_arr ) ) // $where_col is string as : ' title like "%s" OR id = "%d" '
    {
        $query .= " WHERE " ;
        
        $values_arr = array();
    
        foreach( $where_col_arr as $key => $item )
        {

            if( empty($item['format']))
                return new WP_Error( "format_error", __( "The data format is not specified correctly.", "rxarm" ) );

            if( is_array( $item['val'] ) && !empty( $item['val'] ) )
            {
                
                // Set 
                $format =  '(' .  implode( "," , array_fill( 0, count($item['val']), sanitize_text_field( $item['format']) ) ). ')';

                // Set operation 
                $operation = $item['operation'] ? $item['operation'] : 'IN' ;

                // Set relation
                $relation = ( count( $where_col_arr ) > 1 && count( $where_col_arr ) > $key + 1 ) ? ( $item['relation'] ? $item['relation'] : ' AND ' ) : '';

                $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

                // Add values
                $values_arr = array_merge( $values_arr , $item['val'] );

            } else
            {
                // Set format %d, %f, %s
                $format = sanitize_text_field( $item['format'] ) ;
                
                // Set operation 
                $operation = isset($item['operation']) ? $item['operation'] : '=' ;

                // Set relation
                $relation = ( count( $where_col_arr ) > 1 && count( $where_col_arr ) > $key + 1 ) ? ( isset($item['relation']) ? $item['relation'] : ' AND ') : '';

                $query .= ' ' . $item['key'] . ' ' . $operation . ' ' . $format . ' ' . $relation ;

                // Add values
                $values_arr[] = $item['val'];
            }
        }
    }
    
    $safequery = $wpdb->prepare( " $query ", !empty($values_arr) ? $values_arr : array() );

    $result = $wpdb->get_col( $safequery );

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error) )
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }

    if( empty($result) || !is_array($result) )
        return new WP_Error( "no_data_found", __( "No data found.", "rxarm" ) );

    return count($result);

}

 // Custom query function
function arm_custom_query($query, $need_results = false, $mysql_error = false)
{
   global $wpdb;

    if ($need_results)
    {
        // run query
        $result = $wpdb->get_results( $query ); // $query is string

    } else
    {
        // run query
        $result = $wpdb->query( $query ); // $query is string
    }

    // For check mysql error
    if( $mysql_error && !empty($wpdb->last_error) )
    {
        return new WP_Error( "mysql_error", $wpdb->last_error );
    }

    if( empty($result) )
        return new WP_Error( "no_data_found", __( "No data found.", "rxarm" ) );

   return $result;
}

