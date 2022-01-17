<?php

//Display all type of errors
error_reporting( E_ALL );
ini_set( "display_errors", 1 );

if ( !function_exists( 'dpr' ) ) {
    function dpr( $data ) {
        echo '<pre>';
        print_r( $data );
        echo '</pre>';
        die();
    }
}
if ( !function_exists( 'pr' ) ) {
    function pr( $data ) {
        echo '<pre>';
        print_r( $data );
        echo '</pre>';
    }
}

// Insert Data
if ( !function_exists( 'insert_data' ) ) {
    function insert_data( $table, $data ) {
        global $wpdb;
        $wpdb->insert( $wpdb->prefix . $table, $data );
        return $wpdb->insert_id;
    }
}

// Update Data
if ( !function_exists( 'update_data' ) ) {
    function update_data( $table, $data, $where ) {
        global $wpdb;
        $updated = $wpdb->update($wpdb->prefix . $table, $data, $where );
        return $updated;
    }
}

// Get All Data
if ( !function_exists( 'get_all' ) ) {
    function get_all( $table, $orderby='id', $order='DESC', $limit = 0 ) {
        global $wpdb;

        if ($limit > 0) {
            $sql = "SELECT * FROM {$wpdb->prefix}$table ORDER BY {$orderby} {$order} LIMIT $limit";
       
        } else {
           $sql = "SELECT * FROM {$wpdb->prefix}$table ORDER BY {$orderby} {$order}";
        }
        
        $result = $wpdb->get_results( $sql );

        return $result;
    }
}

// Get Data By Condition
if ( !function_exists( 'get_data' ) ) {
    function get_data( $table, $condition, $orderby='id', $order='DESC', $limit = 0 ) {
        global $wpdb;

        if ($limit > 0) {
          $sql = "SELECT * FROM {$wpdb->prefix}$table WHERE $condition ORDER BY {$orderby} {$order} LIMIT $limit";
       
        } else {
          $sql =  "SELECT * FROM {$wpdb->prefix}$table WHERE $condition ORDER BY {$orderby} {$order}";
        }

        $result = $wpdb->get_results( $sql );

        return $result;
    }
}

// Get Data By Multi ID
if ( !function_exists( 'get_data_by_multi_id' ) ) {
    function get_data_by_multi_id( $table, $multi_id ) {
        global $wpdb;
        $result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}$table WHERE id IN ( $multi_id )" );

        return $result;
    }
}

// Get Data By ID
if ( !function_exists( 'get_data_by_id' ) ) {
    function get_data_by_id($table, $id) {
        global $wpdb;

        $result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}$table WHERE id=%d", $id) );

        return $result;
    }
}

// Get Data Between 2 Dates
if ( !function_exists( 'get_data_between' ) ) {
    function get_data_between($table, $start, $end) {
        global $wpdb;

        $result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}$table WHERE cast(created_date as date) BETWEEN '%s' AND  '%s'", $start, $end) );

        return $result;
    }
}

 // Check if data already exists
if ( !function_exists( 'exp_man_is_data_exists' ) ) {
    function exp_man_is_data_exists( $tablename, $column_key, $column_value )
    {
        $result = get_data($tablename, "$column_key='$column_value'");

        return $result;
    }
}

// Delete Data
if ( !function_exists( 'delete_data' ) ) {
    function delete_data( $table, $id ) {
        global $wpdb;

        $id = intval( $id );

        $deleted = $wpdb->delete(
            $wpdb->prefix . $table,
            [
                'id' => $id,
            ]
        );

        return $deleted;
    }
}


// Find Lowest Year
function find_lowest_year(...$data){
    $all_data = array_merge(...$data);

    $years = [];

    if ( !empty($all_data)){
        foreach ($all_data as $key => $data) {
            $years[] = intval(date('Y', strtotime($data->created_date)));
        }
    }
    
    $lowest_year = min($years);

    return $lowest_year;
}


// Render Years For Filtering
function render_years(){
    $html = '';
    $all_incomes = get_all( 'exp_man_income' );
    $all_expenses = get_all( 'exp_man_expenses' );
    
    if( empty($all_incomes) && empty($all_expenses) ){
        return $html;
    }

    $lowest_year = find_lowest_year($all_incomes, $all_expenses);
    
    for ( $i = date( 'Y' ); $i >= $lowest_year; $i-- ) {
        $html .= '<option value="' . $i . '">' . $i . '</option>';
    }
    
    echo $html;
}

// Render Months For Filtering
function render_months()
{
    for ( $i = 1; $i <= 12; $i++ ) {
        echo '<option value="' . $i . '">' . date( 'F', mktime( 0, 0, 0, $i, 1 ) ) . '</option>';
    }
}