<?php

namespace ExpenseManager\Admin;

use ExpenseManager\Installer;

/**
 * Class Income
 */

class Income extends Common{

    public $errors = [];
    public $exp_settings;
    public $source_obj;
    public $pay_type_obj;

    private $tablename;
    private $source_table;
    private $pay_type_table;

    private $incomes;
    private $sources;
    private $pay_types;
    private $total_income = 0;

    public function __construct() {
        $this->tablename = "exp_man_income";
        $this->source_table = "exp_man_income_sources";
        $this->pay_type_table = "exp_man_payment_type";

        $this->source_obj = new IncomeSource();
        $this->pay_type_obj = new PaymentType();
        $this->exp_settings = new ExpSettings();

        add_action( 'wp_ajax_save_income', [$this, 'save_income'] );
        add_action( 'wp_ajax_get_single_income', [$this, 'get_single_income'] );
        add_action( 'wp_ajax_delete_income', [$this, 'delete_income'] );

        add_action( 'wp_ajax_filter_incomes', [$this, 'filter_incomes'] );
        add_action( 'wp_ajax_search_incomes', [$this, 'search_incomes'] );
    }

    public function render_page() {
        if ( 'wealcoder-expense-manager-income' != $_GET['page'] ) {
            return;
        }

        $this->sources = get_all( $this->source_table );
        $this->pay_types = get_all( "exp_man_payment_type" );

        $template = __DIR__ . '/views/income/income_list.php';

        if ( file_exists( $template ) ) {
            include_once $template;
        }
    }

    // Get Total Income
    public function get_total_income() {
        $this->incomes = get_all( $this->tablename, 'id', 'DESC', $this->exp_settings->income_limit );

        if ( !empty( $this->incomes ) ) {
            foreach ( $this->incomes as $income ) {
                $this->total_income += $income->income_amount;
            }
        }
        return $this->total_income;
    }

    // Get Single Income
    public function get_single_income() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You are not allowed to access this page!' );
        }

        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_send_json_error( "Nonce verification failed!" );
        }

        if ( isset( $_REQUEST['id'] ) ) {
            $single_income = get_data_by_id( $this->tablename, $_REQUEST['id'] )[0];

            $source_arr = get_data_by_id( $this->source_table, $single_income->income_source_id );

            if ( !empty( $source_arr ) ) {
                $single_income->income_source = $source_arr[0]->source_name;
            } else {
                $single_income->income_source = '';
            }

            $pay_type_arr = get_data_by_id( $this->pay_type_table, $single_income->income_pay_type_id );

            if ( !empty( $pay_type_arr ) ) {
                $single_income->payment_type = $pay_type_arr[0]->payment_type;
            }

            wp_send_json_success( $single_income );

        } else {
            wp_send_json_error( "Something went wrong!" );
        }
    }

    // Save Income
    public function save_income() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You are not allowed to access this page!' );
        }
        if ( !wp_verify_nonce( $_POST['_wpnonce'], 'save-income-nonce' ) ) {
            wp_send_json_error( "Nonce verification failed!" );
        }

        if ( !isset( $_POST['income_form'] ) ) {
            wp_send_json_error( 'Access Denied!' );
        }

        if ( ! $this->is_table_exists($this->tablename) ){
            $installer = new Installer();
            $installer->create_tables();
        }

        $date = isset( $_POST['income_date'] ) ? $_POST['income_date'] : '';
        $amount = ( isset( $_POST['amount'] ) ) ? trim( $_POST['amount'] ) : '';
        $short_desc = ( isset( $_POST['short_desc'] ) ) ? trim( $_POST['short_desc'] ) : '';
        $source_id = isset( $_POST['income_source'] ) ? $_POST['income_source'] : 0;
        $pay_type_id = isset( $_POST['payment_type'] ) ? $_POST['payment_type'] : 0;

        if ( empty( $_POST['amount'] ) ) {
            $this->errors['amount_err'] = 'Amount is required';
        }

        if ( !empty( $this->errors ) ) {
            wp_send_json_error( $this->errors );
        } else {
            $data = [
                'created_date'       => $date,
                'income_amount'      => $amount,
                'income_short_desc'  => $short_desc,
                'income_source_id'   => $source_id,
                'income_pay_type_id' => $pay_type_id,
            ];

            if ( isset( $_POST['income_id'] ) && $_POST['income_id'] != 0 ) {
                update_data( $this->tablename, $data, array( 'id' => $_POST['income_id'] ) );
            } else {
                insert_data( $this->tablename, $data );
            }

            $updated_data = get_all( $this->tablename, 'id', 'DESC',
                $this->exp_settings->income_limit );

            // Push source names and payment type names
            foreach ( $updated_data as $key => $data ) {
                $source_arr = get_data_by_id( $this->source_table, $data->income_source_id );

                $pay_type_arr = get_data_by_id( "exp_man_payment_type", $data->income_pay_type_id );

                if ( !empty( $source_arr ) ) {
                    $source = $source_arr[0]->source_name;
                    $data->income_source = $source;
                } else {
                    $data->income_source = '';
                }

                if ( !empty( $pay_type_arr ) ) {
                    $pay_type = $pay_type_arr[0]->payment_type;
                    $data->payment_type = $pay_type;
                }
            }

            wp_send_json_success( [
                'total_rows' => count( get_all( $this->tablename ) ),
                'data'       => $updated_data,
            ] );
        }
    }

    // Render Data table
    public function render_data_table() {
        $this->incomes = $this->get_this_month_incomes($this->exp_settings->income_limit);

        $currency_symbol = ( !empty( get_option( 'select_currency' ) ) ) ? get_option( 'select_currency' ) : '$';

        $html = '';
        if ( count( $this->incomes ) > 0 ) {
            foreach ( $this->incomes as $income ) {
                $source_arr = get_data_by_id( $this->source_table, $income->income_source_id );

                $source = ( !empty( $source_arr ) ) ? $source_arr[0]->source_name : '';

                $pay_type_arr = get_data_by_id( 'exp_man_payment_type', $income->income_pay_type_id );

                $pay_type = ( !empty( $pay_type_arr ) ) ? $pay_type_arr[0]->payment_type : '';

                $html .= '<tr class="data">';

                $html .= "<td> <input type='checkbox' name='select_$income->id' id='select_$income->id' value='1'> <span class='source'> $source</span>";

                $html .= '<div class="row-actions">';
                $html .= '<span class="view"><a href="" data-id="' . $income->id . '">View</a> | </span>';

                $html .= "<span class='edit'><a
                                    href='' data-id='$income->id'>Edit</a> | </span>";

                $html .= "<span class='delete'><a href='#' class='delete_btn' id='$income->id'>Delete</a> </span>";
                $html .= '</div></td>';

                $html .= "<td>" . $currency_symbol . " <span class='amount'>" . number_format( $income->income_amount ) . "</span></td>";

                $html .= "<td class='short_desc'> $income->income_short_desc </td>";

                $html .= "<td class='pay_type'> $pay_type </td>";

                if ( empty( $income->created_date ) ) {
                    $income_date = '';
                } else {
                    $income_date = date_format( date_create( $income->created_date ), 'j F, Y' );
                }

                $html .= "<td class='date'> $income_date </td>";

                $html .= '</tr>';
            }
        } else {
            $html .= '<tr class="data">';
            $html .= "<td>" . __( 'No Income Founds.', EXP_MAN_TXT_DOMAIN ) . "</td>";
            $html .= "</tr>";
        }

        return $html;
    }


    // This Month incomes Function
    public function get_this_month_incomes( $limit = 0 ) {
        $current_month = date( 'Y' ) . '-' . date( 'm' );

        $this_month_incomes = get_data( 'exp_man_income', "cast(created_date as date) LIKE '%$current_month%'", 'id', 'DESC',
        $limit );

        return $this_month_incomes;
    }

     // Get this month total
     public function get_this_month_total() {
        $incomes = $this->get_this_month_incomes();
        $this_month_total = 0;

        if ( !empty( $incomes ) ) {
            foreach ( $incomes as $income ) {
                $this_month_total += $income->income_amount;
            }
        }
        return $this_month_total;
    }

    // Last Month incomes Function
    public function get_last_month_incomes() {
        $last_month = date( 'Y-m', strtotime( 'last month' ) );

        $last_month_incomes = get_data( 'exp_man_income', "cast(created_date as date) LIKE '%$last_month%'" );

        return $last_month_incomes;
    }

    // Last 3 months incomes
    public function get_last_3_month_incomes() {
        $first_month = date( 'Y' ) . '-' . date( 'm' );
        $second_month = date( 'Y-m', strtotime( '-1 month' ) );
        $third_month = date( 'Y-m', strtotime( '-2 month' ) );

        $last_3_month_incomes = get_data( 'exp_man_income', "cast(created_date as date) LIKE '%$first_month%' OR created_date LIKE '%$second_month%' OR created_date LIKE '%$third_month%'" );

        return $last_3_month_incomes;
    }

    // Filter Incomes
    public function filter_incomes() {
        // Get incomes on Filter Types
        if ( $_GET['filter_type'] == 'this_month' ) {
            $all_incomes = $this->get_this_month_incomes();

        } else if ( $_GET['filter_type'] == 'last_month' ) {
            $all_incomes = $this->get_last_month_incomes();

        } else if ( $_GET['filter_type'] == 'last_3_months' ) {
            $all_incomes = $this->get_last_3_month_incomes();

        } else if ( $_GET['filter_type'] == 'custom_date' ) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $all_incomes = get_data_between( $this->tablename, $start_date, $end_date );

        } else if ( $_GET['filter_type'] == 'yearly' ) {

            $all_incomes = get_all( $this->tablename );

            // Filter By Year
            if ( isset( $_GET['year'] ) && $_GET['year'] != 0 ) {
                $year = $_GET['year'];

                foreach ( $all_incomes as $key => $income ) {
                    if (  ( substr( $income->created_date, 0, 4 ) ) != $year ) {
                        unset( $all_incomes[$key] );
                    }
                }
            }

            // Filter By Month
            if ( isset( $_GET['month'] ) && $_GET['month'] != 0 ) {
                $month = $_GET['month'];

                foreach ( $all_incomes as $key => $income ) {
                    if (  ( substr( $income->created_date, 5, 2 ) ) != $month ) {
                        unset( $all_incomes[$key] );
                    }
                }
            }
        } else {

            $all_incomes = get_all( $this->tablename );
        }

        //Filter By Source
        if ( isset( $_GET['sources'] ) && !empty( $_GET['sources'] ) ) {
            $source_ids = $_GET['sources'];

            foreach ( $all_incomes as $key => $income ) {
                if ( !in_array( $income->income_source_id, $source_ids ) ) {
                    unset( $all_incomes[$key] );
                    continue;
                }

                $source_arr = get_data_by_id( $this->source_table, $income->income_source_id );

                if ( !empty( $source_arr ) ) {
                    $all_incomes[$key]->income_source = $source_arr[0]->source_name;
                } else {
                    $all_incomes[$key]->income_source = '';
                }
            }
        }

        // Push source names and payment type names
        if ( !empty( $all_incomes ) ) {
            foreach ( $all_incomes as $key => $income ) {
                $source_arr = get_data_by_id( $this->source_table, $income->income_source_id );

                $pay_type_arr = get_data_by_id( $this->pay_type_table, $income->income_pay_type_id );

                $income->income_source = ( !empty( $source_arr ) ) ? $source_arr[0]->source_name : '';

                $income->payment_type = ( !empty( $pay_type_arr ) ) ? $pay_type_arr[0]->payment_type : '';
            }
        }

        // Search Incomes
        if ( isset( $_GET['search'] ) && !empty( $_GET['search'] ) ) {
            $search_key = strtolower( trim( $_GET['search'] ) );

            foreach ( $all_incomes as $key => $income ) {

                $date = date_format( date_create( $income->created_date ), 'j F, Y' );

                // Search By Source, Date, Amount, Payment Type
                if ( strpos( strtolower( $income->income_source ), $search_key ) === false && strpos( strtolower( $date ), $search_key ) === false && strpos( strtolower( $income->payment_type ), $search_key ) === false && strpos( strtolower( $income->income_amount ), $search_key ) === false && strpos( strtolower( $income->income_short_desc ), $search_key ) === false ) {

                    unset( $all_incomes[$key] );
                }
            }
        }

        // Check if end of data on click "Load More"
        $end_of_data = false;
        $end_of_data = $this->is_end_of_data( $all_incomes )['end_of_data'];

        $filtered_incomes = $this->is_end_of_data( $all_incomes )['filtered_data'];

        wp_send_json_success( [
            'end_of_data' => $end_of_data,
            'total_rows'  => count( $all_incomes ),
            'data'        => $filtered_incomes,
        ] );
    }

    //Delete Income with Ajax
    public function delete_income() {
        if ( !isset( $_REQUEST['id'] ) ) {
            wp_send_json_error( 'Access Denied!' );
        }

        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_send_json_error( 'Nonce Verification failed!' );
        }

        delete_data( $this->tablename, $_REQUEST['id'] );

        $updated_data = get_all( $this->tablename, 'id', 'DESC',
            $this->exp_settings->income_limit );

        wp_send_json_success( [
            'total_rows' => count( get_all( $this->tablename ) ),
            'data'       => $updated_data,
        ] );
    }
}
