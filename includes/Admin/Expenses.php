<?php

namespace ExpenseManager\Admin;

use ExpenseManager\Installer;

/**
 * Class Expenses
 */

class Expenses extends Common{
    public $errors = [];
    public $expenses;
    public $categories;
    public $category_obj;
    public $exp_settings;
    public $common;

    private $tablename;
    private $cat_tablename;
    private $total_expense;

    public function __construct() {
        $this->tablename = "exp_man_expenses";
        $this->cat_tablename = "exp_man_expenses_category";

        $this->exp_settings = new ExpSettings();

        $this->expenses = get_all( $this->tablename, 'id', 'DESC', $this->exp_settings->expense_limit );

        $this->categories = get_all( $this->cat_tablename );

        add_action( 'wp_ajax_add_new_expense', [$this, 'save_expense'] );
       
        add_action( 'wp_ajax_get_single_expense', [$this, 'get_single_expense'] );
        add_action( 'wp_ajax_delete_expense', [$this, 'delete_expense'] );

        add_action( 'wp_ajax_filter_expenses', [$this, 'filter_expenses'] );
    }

    public function render_page() {
        if ( 'wealcoder-expense-manager-expenses' != $_GET['page'] ) {
            return;
        }

        $this->category_obj = new ExpenseCategory();
        
        $this->expenses = $this->get_this_month_expenses($this->exp_settings->expense_limit);
        
        $template = __DIR__ . '/views/expenses/expense_list.php';

        if ( file_exists( $template ) ) {
            include_once $template;
        }
    }

    // Get Single Expense
    public function get_single_expense() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You are not allowed to access this page!' );
        }

        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_send_json_error( "Nonce verification failed!" );
        }

        if ( isset( $_REQUEST['id'] ) ) {
            $single_expense = get_data_by_id( $this->tablename, $_REQUEST['id'] )[0];

            $cat_arr = get_data_by_id( $this->cat_tablename, $single_expense->expense_cat_id );

            $single_expense->expense_category = ( !empty( $cat_arr ) ) ? $cat_arr[0]->category_name : '';

            wp_send_json_success( $single_expense );

        } else {
            wp_send_json_error( "Something went wrong!" );
        }
    }

    public function save_expense() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You are not allowed to access this page!' );
        }
        if ( !wp_verify_nonce( $_POST['_wpnonce'], 'add-new-expense-nonce' ) ) {
            wp_send_json_error( "Nonce verification failed!" );
        }

        if ( !isset( $_POST['save_and_close'] ) && !isset( $_POST['expense_form'] ) ) {
            wp_send_json_error( 'Access Denied!' );
        }

        $paid_to = isset( $_POST['paid_to'] ) ? $_POST['paid_to'] : '';
        $amount = ( isset( $_POST['amount'] ) ) ? trim( $_POST['amount'] ) : '';

        if ( empty( $_POST['paid_to'] ) ) {
            $this->errors['paid_for_err'] = 'Paid For is required.';
        }

        if ( empty( $_POST['amount'] ) ) {
            $this->errors['amount_err'] = 'Amount is required.';
        }

        if ( !empty( $this->errors ) ) {
            wp_send_json_error( $this->errors );
        } else {
            global $current_user;
            wp_get_current_user();
  
            if ( ! $this->is_table_exists($this->tablename) ){
                $installer = new Installer();
                $installer->create_tables();
            }
    
            $uncat_id = 0;
            
            $uncat_arr = get_data( $this->cat_tablename,  "category_slug='uncategorized'");
            
            if ( ! empty($uncat_arr) ){
                $uncat_id = $uncat_arr[0]->id;
            }

            $cat_id = ( isset( $_POST['purpose'] ) && 0 != $_POST['purpose'] ) ? $_POST['purpose'] : $uncat_id;

            $short_desc = isset( $_POST['short_desc'] ) ? $_POST['short_desc'] : '';
            $date = isset( $_POST['expense_date'] ) ? $_POST['expense_date'] : '';

            $expense_doc_name = isset( $_POST['uploaded_file_url'] ) ? esc_url( $_POST['uploaded_file_url'] ) : '';

            $data = [
                'created_date'       => $date,
                'expense_amount'     => $amount,
                'expense_short_desc' => $short_desc,
                'expense_cat_id'     => $cat_id,
                'expense_paid_to'    => $paid_to,
                'expense_doc_name'   => $expense_doc_name,
                'expense_add_by'     => $current_user->display_name,
            ];

            if ( isset( $_POST['expense_id'] ) && $_POST['expense_id'] != 0 ) {

                update_data( $this->tablename, $data, array( 'id' => $_POST['expense_id'] ) );

            } else {
                insert_data( $this->tablename, $data );
            }

            $updated_data = get_all( $this->tablename, 'id', 'DESC', $this->exp_settings->expense_limit );

            // Push category names to live update data
            foreach ( $updated_data as $key => $data ) {
                $cat_arr = get_data_by_id( $this->cat_tablename, $data->expense_cat_id );

                $data->expense_category = ( !empty( $cat_arr ) ) ? $cat_arr[0]->category_name : '';
            }

            wp_send_json_success( [
                'total_rows' => count( get_all( $this->tablename ) ),
                'data'       => $updated_data,
            ] );
        }
    }

    // Get Total Expense
    public function get_total_expense() {
        $this->expenses = get_all( $this->tablename, 'id', 'DESC', $this->exp_settings->expense_limit );

        if ( empty( $this->expenses ) ) {
            $this->total_expense = 0;
        } else {
            foreach ( $this->expenses as $expense ) {
                $this->total_expense += $expense->expense_amount;
            }
        }
        return $this->total_expense;
    }

    //Delete Expense with Ajax
    public function delete_expense() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_send_json_error( 'Nonce Verification failed!' );

        } else {
            delete_data( $this->tablename, $_REQUEST['id'] );

            $updated_data = get_all( $this->tablename, 'id', 'DESC',
                $this->exp_settings->expense_limit );

            wp_send_json_success( [
                'total_rows' => count( get_all( $this->tablename ) ),
                'data'       => $updated_data,
            ] );
        }
    }

    // This Month Expenses Function
    public function get_this_month_expenses( $limit = 0 ) {
        $current_month = date( 'Y' ) . '-' . date( 'm' );

        $this_month_expenses = get_data( 'exp_man_expenses', "cast(created_date as date) LIKE '%$current_month%'", 'id', 'DESC', $limit );

        return $this_month_expenses;
    }

    // This Month Total
    public function get_this_month_total()
    {
        $expenses = $this->get_this_month_expenses();
        $this_month_total = 0;

        if ( empty( $expenses ) ) {
            $this_month_total = 0;
        } else {
            foreach ( $expenses as $expense ) {
                $this_month_total += $expense->expense_amount;
            }
        }
        return $this_month_total;
    }

    // Last Month Expenses Function
    public function get_last_month_expenses() {
        $last_month = date( 'Y-m', strtotime( 'last month' ) );

        $last_month_expenses = get_data( 'exp_man_expenses', "cast(created_date as date) LIKE '%$last_month%'" );

        return $last_month_expenses;
    }

    // Last 3 months expenses
    public function get_last_3_month_expenses() {
        $first_month = date( 'Y' ) . '-' . date( 'm' );
        $second_month = date( 'Y-m', strtotime( '-1 month' ) );
        $third_month = date( 'Y-m', strtotime( '-2 month' ) );

        $last_3_month_expenses = get_data( 'exp_man_expenses', "cast(created_date as date) LIKE '%$first_month%' OR created_date LIKE '%$second_month%' OR created_date LIKE '%$third_month%'" );

        return $last_3_month_expenses;
    }

    // Filter Expenses
    public function filter_expenses() {
        // Get expenses on Filter Types
        if ( $_GET['filter_type'] == 'this_month' ) {
            $all_expenses = $this->get_this_month_expenses();

        } else if ( $_GET['filter_type'] == 'last_month' ) {
            $all_expenses = $this->get_last_month_expenses();

        } else if ( $_GET['filter_type'] == 'last_3_months' ) {
            $all_expenses = $this->get_last_3_month_expenses();

        } else if ( $_GET['filter_type'] == 'custom_date' ) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $all_expenses = get_data_between( $this->tablename, $start_date, $end_date );

        } else if ( $_GET['filter_type'] == 'yearly' ) {
            $all_expenses = get_all( $this->tablename );

            // Filter By Year
            if ( isset( $_GET['year'] ) && $_GET['year'] != 0 ) {
                $year = $_GET['year'];

                foreach ( $all_expenses as $key => $expense ) {
                    if (  ( substr( $expense->created_date, 0, 4 ) ) != $year ) {
                        unset( $all_expenses[$key] );
                    }
                }
            }

            // Filter By Month
            if ( isset( $_GET['month'] ) && $_GET['month'] != 0 ) {
                $month = $_GET['month'];

                foreach ( $all_expenses as $key => $expense ) {
                    if (  ( substr( $expense->created_date, 5, 2 ) ) != $month ) {
                        unset( $all_expenses[$key] );
                    }
                }
            }
        } else {

            $all_expenses = get_all( $this->tablename );
        }

        //Filter By Category
        if ( isset( $_GET['categories'] ) && !empty( $_GET['categories'] ) ) {

            $cat_ids = $_GET['categories'];

            foreach ( $all_expenses as $key => $expense ) {
                if ( !in_array( $expense->expense_cat_id, $cat_ids ) ) {
                    unset( $all_expenses[$key] );
                }
            }
        }

        // Push category names
        foreach ( $all_expenses as $key => $expense ) {
            $cat_arr = get_data_by_id( $this->cat_tablename, $expense->expense_cat_id );

            $expense->expense_category = ( !empty( $cat_arr ) ) ? $cat_arr[0]->category_name : '';
        }

        // Search Expense
        if ( isset( $_GET['search'] ) && !empty( $_GET['search'] ) ) {
            $search_key = strtolower( trim( $_GET['search'] ) );

            foreach ( $all_expenses as $key => $expense ) {
                $date = date_format( date_create( $expense->created_date ), 'j F, Y' );

                // Search By Category, Date, Amount, Paid To, Added By
                if ( strpos( strtolower( $expense->expense_category ), $search_key ) === false && strpos( strtolower( $expense->expense_short_desc ), $search_key ) === false && strpos( strtolower( $date ), $search_key ) === false && strpos( strtolower( $expense->expense_paid_to ), $search_key ) === false && strpos( strtolower( $expense->expense_amount ), $search_key ) === false && strpos( strtolower( $expense->expense_add_by ), $search_key ) === false ) {
                    unset( $all_expenses[$key] );
                }
            }
        }

        // Check if end of data on click "Load More"
        $end_of_data = false;
        $end_of_data = $this->is_end_of_data( $all_expenses )['end_of_data'];

        $filtered_expenses = $this->is_end_of_data( $all_expenses )['filtered_data'];

        wp_send_json_success( [
            'end_of_data' => $end_of_data,
            'total_rows'  => count( $all_expenses ),
            'data'        => $filtered_expenses,
        ] );
    }
}
