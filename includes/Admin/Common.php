<?php

namespace ExpenseManager\Admin;

/**
 * Class Common
 */

class Common{
    public $exp_settings;

    public function __construct()
    {
        $this->exp_settings = new ExpSettings();
    }

    // Is table exists
    public function is_table_exists($tablename)
    {
        global $wpdb;

        $table_exists = $wpdb->get_results("DESCRIBE {$wpdb->prefix}$tablename");

        return $table_exists;
    }

    // Get lifetime income
    public function get_lifetime_income()
    {
        $lifetime_income = 0;
        $all_incomes = get_all( 'exp_man_income' );
            
        if ( !empty( $all_incomes )){
            foreach ( $all_incomes as $key => $income ) {
                $lifetime_income += $income->income_amount;
            }
        }
        return $lifetime_income;
    }

    // Get Lifetime expense
    public function get_lifetime_expense()
    {
        $lifetime_expense = 0;
       
        $all_expenses = get_all( 'exp_man_expenses' ); 

        if ( !empty($all_expenses) ){
            foreach ( $all_expenses as $key => $expense ) {
                $lifetime_expense += $expense->expense_amount;
            }
        }
        
        return $lifetime_expense;
    }

    // Check end of data on load more
    public function is_end_of_data($data)
    {
        $all_data = $data;
        $filtered_data = $data;

        if ( $_GET['load_click'] ) {
            // Load More Expense
            $filtered_data = array_slice( $filtered_data, 0, $this->exp_settings->expense_limit + ( $this->exp_settings->load_more * $_GET['load_click'] ) );
        
        } else {
            // Show Limited Expense items
            $filtered_data = array_slice($filtered_data, 0, $this->exp_settings->expense_limit);
        }

        // Check if data ends
        $end_of_data = false;
        
        $total_data = array_slice( $all_data, 0, $this->exp_settings->expense_limit + ( $this->exp_settings->load_more * ( $_GET['load_click']+1 ) ) );

        if( count($filtered_data) == count( $total_data ) ){
            $end_of_data = true;
        }

        return [ 
            'filtered_data' => $filtered_data, 
            'end_of_data' => $end_of_data 
        ];
    }


    /**
     * ==================================
     * Create Default Fields
     * ==================================
     */
    public function create_default_fields() {
    
        $expense_cat_table = "exp_man_expenses_category";

        $has_uncategoriezed = get_data($expense_cat_table, "category_slug='uncategorized'") ;

            if ( ! empty( $has_uncategoriezed ) ) {
                return;
            }
            
        insert_data($expense_cat_table, array(
                'category_name' => 'Uncategorized',
                'category_slug' => 'uncategorized',
        ));
    }


    /**
     * Upload File function
     */
    public function upload_file( $file, $types = array(), $maxSize = 1048576, $unique = false ) {
        $target_dir = EXP_MAN_PATH . "/assets/uploads/";
        
        if ( $unique ){
            $filename = time() . '_' . basename( $file["name"] );
        } else {
            $filename = basename( $file["name"] );
        }

        $target_file = $target_dir . $filename;

        $fileType = strtolower( pathinfo( $target_file, PATHINFO_EXTENSION ) );

        // Check file size
        if ( $file["size"] > $maxSize ) {
            $maxSize = $maxSize / 1048576;

            wp_send_json_error( array(
                "message" => "File is too large! Maximum size is $maxSize MB",
            ) );
        }

        // Restrict file format
        if ( ! in_array( $fileType, $types ) ) {
            $type_str = implode( ", ", $types );

            wp_send_json_error( array(
                "message" => "Sorry, only $type_str files are allowed.",
            ) );
        }
        
        $uploaded = move_uploaded_file( $file["tmp_name"], $target_file );
        
        if ( ! $uploaded ) {
            wp_send_json_error( array(
                "message" => "Something went wrong when uploading your file!",
            ) );
        }

        return $filename;
    }
}