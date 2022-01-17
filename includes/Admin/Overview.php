<?php

namespace ExpenseManager\Admin;

/**
 ** Class Overview
 */

class Overview extends Common{
    private $all_incomes;
    private $all_expenses;

    private $total_income = 0;
    private $total_expense = 0;
    private $profit = 0;

    public $income_sources;
    public $expense_categories;

    // Overview Constructor
    public function __construct() {
        $this->income_sources = new IncomeSource();
        $this->expense_categories = new ExpenseCategory();

        add_action( 'wp_ajax_filter_overview', array( $this, 'filter_overview' ) );
       
        add_action( 'wp_ajax_filter_overview_yearly', array( $this, 'filter_overview_yearly' ) );

        add_action( 'wp_ajax_filter_overview_custom_date', array( $this, 'filter_overview_custom_date' ) );
    }

    // Render Overview Page
    public function render_page() {
        if ( 'wealcoder-expense-manager' != $_GET['page'] ) {
            return;
        }

        $template = __DIR__ . '/views/overview/overview.php';

        if ( file_exists( $template ) ) {
            include_once $template;
        }
    }

    // Render Recent Incomes table
    public function render_recent_incomes_table() {
        $incomes = get_all( 'exp_man_income', 'id', 'DESC', 5 );

        $currency_symbol = ( !empty( get_option( 'select_currency' ) ) ) ? get_option( 'select_currency' ) : '$';

        $html = '';
        if ( count( $incomes ) > 0 ) {
            foreach ( $incomes as $income ) {
                $source_arr = get_data_by_id( 'exp_man_income_sources', $income->income_source_id );

                $source = ( ! empty($source_arr) ) ? $source_arr[0]->source_name : '';
    
                $pay_type_arr = get_data_by_id( 'exp_man_payment_type', $income->income_pay_type_id );
                
                $pay_type = ( ! empty($pay_type_arr) ) ? $pay_type_arr[0]->payment_type : '';

                $html .= '<tr class="data">';

                $html .= "<td class='source'> $source </td>";
                
                $html .= "<td>" . $currency_symbol . " <span class='amount'>" . number_format( $income->income_amount ) . "</span></td>";

                $html .= "<td class='short_desc'> $income->income_short_desc </td>";
                
                $html .= "<td class='pay_type'> $pay_type </td>";

                if( empty($income->created_date) ){
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


    // This Month Income
    public function this_month_income()
    {
        $this_month_income = 0;
        $current_month = date('Y') . '-' . date('m');

        $all_incomes = get_data('exp_man_income', "created_date LIKE '%$current_month%'");

        foreach ( $all_incomes as $key => $income ) {
            $this_month_income += $income->income_amount;
        }

        return $this_month_income;
    }

    // This Month Expense
    public function this_month_expense(){
        $this_month_expense = 0;
        $current_month = date('Y') . '-' . date('m');
      
        $all_expenses = get_data('exp_man_expenses', "created_date LIKE '%$current_month%'");

      
        foreach ( $all_expenses as $key => $expense ) {
            $this_month_expense += $expense->expense_amount;
        }

        $this_month_expense;

        return $this_month_expense;
    }

    // Last month Income
    public function last_month_income()
    {
        $last_month_income = 0;
        $last_month = date('Y-m', strtotime('last month'));
        
        $all_incomes = get_data('exp_man_income', "created_date LIKE '%$last_month%'");

        foreach ( $all_incomes as $key => $income ) {
            $last_month_income += $income->income_amount;
        }

        return $last_month_income;
    }

    // Last month expense
    public function last_month_expense()
    {
        $last_month_expense = 0;
        $last_month = date('Y-m', strtotime('last month'));
        
        $all_expenses = get_data('exp_man_expenses', "created_date LIKE '%$last_month%'");

        foreach ( $all_expenses as $key => $expense ) {
            $last_month_expense += $expense->expense_amount;
        }

        return $last_month_expense;
    }

    // Last 3 months income
    public function last_3_month_income()
    {
        $last_3months_income = 0;

        $first_month = date('Y') . '-' . date('m');
        $second_month = date('Y-m', strtotime('-1 month'));
        $third_month = date('Y-m', strtotime('-2 month'));

        $all_incomes = get_data('exp_man_income', "created_date LIKE '%$first_month%' OR created_date LIKE '%$second_month%' OR created_date LIKE '%$third_month%'");

        foreach ( $all_incomes as $key => $income ) {
            $last_3months_income += $income->income_amount;
        }

        return $last_3months_income;
    }
   
    // Last 3 months expense
    public function last_3_month_expense()
    {
        $last_3months_expense = 0;
        
        $first_month = date('Y') . '-' . date('m');
        $second_month = date('Y-m', strtotime('-1 month'));
        $third_month = date('Y-m', strtotime('-2 month'));

        $all_expenses = get_data('exp_man_expenses', "created_date LIKE '%$first_month%' OR created_date LIKE '%$second_month%' OR created_date LIKE '%$third_month%'");

        foreach ( $all_expenses as $key => $expense ) {
            $last_3months_expense += $expense->expense_amount;
        }

        return $last_3months_expense;
    }


    // Filter Overview
    public function filter_overview()
    {
        if (!isset($_GET['filter_type'])){
            return;
        }
        
        $current_txt = '';

        if( $_GET['filter_type'] == 'this_month' ){
            $this->total_income = $this->this_month_income();
            $this->total_expense = $this->this_month_expense();
            $current_txt = 'Current Month';
        } elseif ( $_GET['filter_type'] == 'last_month' ){
            $this->total_income = $this->last_month_income();
            $this->total_expense = $this->last_month_expense();  
            $current_txt = 'Last Month';
        } elseif ( $_GET['filter_type'] == 'last_3_months' ){
            $this->total_income = $this->last_3_month_income();
            $this->total_expense = $this->last_3_month_expense();
            $current_txt = 'Last 3 Months';
        } else {
            $this->total_income = $this->get_lifetime_income();
            $this->total_expense = $this->get_lifetime_expense();
            $current_txt = 'Life Time';
        }

        $this->profit = ($this->total_income - $this->total_expense);

        wp_send_json_success( array(
            'income'  => $this->total_income,
            'expense' => $this->total_expense,
            'profit'  => $this->profit,
            'current_text' => $current_txt
        ) );
    }

    // Filter Overview Yearly
    public function filter_overview_yearly() {
        $this->all_incomes = get_all( 'exp_man_income' );
        $this->all_expenses = get_all( 'exp_man_expenses' );

        $current_txt = '';
        $year = 'All Years';
        $months = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ];

        $current_txt = $year;
        
        if ( isset($_GET['filter_type']) && $_GET['filter_type'] = 'yearly'){
            // Filter By Year
            if ( isset( $_GET['year'] ) && $_GET['year'] != 0 ) {
                $year = $_GET['year'];

                foreach ( $this->all_incomes as $key => $income ) {
                    if (  ( substr( $income->created_date, 0, 4 ) ) != $year ) {
                        unset( $this->all_incomes[$key] );
                    }
                }
                foreach ( $this->all_expenses as $key => $expense ) {
                    if (  ( substr( $expense->created_date, 0, 4 ) ) != $year ) {
                        unset( $this->all_expenses[$key] );
                    }
                }

                $current_txt = $year;
            }

            // Filter By Month
            if ( isset( $_GET['month'] ) && $_GET['month'] != 0 ) {
                $month = $_GET['month'];

                foreach ( $this->all_incomes as $key => $income ) {
                    if (  ( substr( $income->created_date, 5, 2 ) ) != $month ) {
                        unset( $this->all_incomes[$key] );
                    }
                }
                foreach ( $this->all_expenses as $key => $expense ) {
                    if (  ( substr( $expense->created_date, 5, 2 ) ) != $month ) {
                        unset( $this->all_expenses[$key] );
                    }
                }

                $current_txt = $months[$month-1] . ', ' . $year;
            }
        }
        

        foreach ( $this->all_incomes as $key => $income ) {
            $this->total_income += $income->income_amount;
        }

        foreach ( $this->all_expenses as $key => $expense ) {
            $this->total_expense += $expense->expense_amount;
        }

        $this->profit = ($this->total_income - $this->total_expense);

        wp_send_json_success( array(
            'income'  => $this->total_income,
            'expense' => $this->total_expense,
            'profit'  => $this->profit,
            'current_text' => $current_txt
        ) );
    }


    // Filter Overview Custom Date
    public function filter_overview_custom_date()
    {
        $current_txt = '';

        if( isset($_GET['start_date']) && isset($_GET['end_date']) ){
           
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $current_txt = date_format( date_create( $start_date  ), 'j F, Y' ) . ' - ' . date_format( date_create( $end_date  ), 'j F, Y' );

            $this->all_incomes = get_data_between('exp_man_income', $start_date, $end_date);
            $this->all_expenses = get_data_between('exp_man_expenses', $start_date, $end_date);
        }

        foreach ( $this->all_incomes as $key => $income ) {
            $this->total_income += $income->income_amount;
        }

        foreach ( $this->all_expenses as $key => $expense ) {
            $this->total_expense += $expense->expense_amount;
        }

        $this->profit = ($this->total_income - $this->total_expense);

        wp_send_json_success( array(
            'income'  => $this->total_income,
            'expense' => $this->total_expense,
            'profit'  => $this->profit,
            'current_text' => $current_txt
        ) );
    }
}