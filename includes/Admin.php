<?php

namespace ExpenseManager;

use ExpenseManager\Admin\Menu;
use ExpenseManager\Admin\Common;
use ExpenseManager\Admin\CustomUpload;

// Admin handler class
class Admin {
    public $common;

    public function __construct() {
        $this->common = new Common();

        new Menu();

        new CustomUpload();

        add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );

        add_action('admin_init', array( $this->common, 'create_default_fields') );
    }

    public function admin_enqueue_scripts( $hook ) {

        // echo $hook;

        $page_prefix = 'wp-expense-manager_page_wealcoder-expense-manager-';
        $overview_page = 'toplevel_page_wealcoder-expense-manager';

        $expense_page = $page_prefix . 'expenses';
        $expense_cat_page = $page_prefix . 'expense-categories';

        $income_page = $page_prefix . 'income';
        $income_sources_page = $page_prefix . 'income-sources';

        $payment_type_page = $page_prefix . 'payment-types';
        $settings_page = $page_prefix . 'settings';
        $export_import_page = $page_prefix . 'export-import';

        if ( $overview_page == $hook || $expense_page == $hook || $expense_cat_page == $hook || $income_page == $hook || $income_sources_page == $hook || $payment_type_page == $hook || $settings_page == $hook || $export_import_page == $hook ) {

            wp_enqueue_style( 'exp-man-admin-style', EXP_MAN_ASSETS . '/css/admin-style.css', array(), time(), 'all' );

            wp_enqueue_script( 'admin-js', EXP_MAN_ASSETS . '/js/admin.js', array( 'jquery' ), time(), true );

            $currency_symbol = ( !empty( get_option( 'select_currency' ) ) ) ? get_option( 'select_currency' ) : '$';
            
            $expense_limit = get_option( 'exp_man_expense_limit' );
            $income_limit = get_option( 'exp_man_income_limit' );

            wp_localize_script( 'admin-js', 'exp_manager_ajax_obj', array(
                'ajax_url'              => admin_url( 'admin-ajax.php' ),    
                'nonce'                 => wp_create_nonce( 'exp-manager-admin-nonce' ),
                'EXP_MAN_UPLOADS' => EXP_MAN_ASSETS . "/uploads/",
                'no_expense_text'       => __( 'No Expense Founds.', EXP_MAN_TXT_DOMAIN ),
                'no_exp_cat_text'       => __( 'No Expense Category Founds.', EXP_MAN_TXT_DOMAIN ),
                'no_income_text'        => __( 'No Income Founds.', EXP_MAN_TXT_DOMAIN ),
                'no_income_source_text' => __( 'No Income Source Founds.', EXP_MAN_TXT_DOMAIN ),
                'no_pay_type_text'      => __( 'No Payment Type Founds.', EXP_MAN_TXT_DOMAIN ),
                'expense_limit' => $expense_limit ,
                'income_limit' => $income_limit ,
                'currency_symbol'       => $currency_symbol . ' ',
            ) );
        }

        if ( $income_page == $hook || $income_sources_page == $hook ) {
            wp_enqueue_script( 'admin-income-js', EXP_MAN_ASSETS . '/js/admin-income.js', array( 'jquery', 'admin-js' ), time(), true );
        }

        if ( $expense_page == $hook || $expense_cat_page == $hook ) {
            wp_enqueue_script( 'admin-expense-category-js', EXP_MAN_ASSETS . '/js/admin-expense-category.js', array( 'jquery', 'admin-js' ), time(), true );
        }

        if ( $expense_page == $hook ) {
            wp_enqueue_script( 'admin-expense-js', EXP_MAN_ASSETS . '/js/admin-expense.js', array( 'jquery', 'admin-js','admin-expense-category-js', 'admin-jspdf', 'admin-jspdf-autotable' ), time(), true );
        }
        
        if ( $expense_page == $hook || $income_page == $hook ){
                // Media uploader
                wp_enqueue_script('media-upload');
                wp_enqueue_media();
            
                wp_enqueue_script( 'media-uploader-js', EXP_MAN_ASSETS . '/js/media_uploader.js', array('jquery', 'media-upload'), null, true );

                // PDF download scripts
                wp_enqueue_script('admin-jspdf', '//cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js', array('jquery'), false, true );

                wp_enqueue_script('admin-jspdf-autotable', '//cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.2.4/jspdf.plugin.autotable.min.js', array('jquery','admin-jspdf'), false, true );
        }
        
        if ( $income_page == $hook || $payment_type_page == $hook ) {
            wp_enqueue_script( 'admin-payment-type-js', EXP_MAN_ASSETS . '/js/admin-payment-type.js', array( 'jquery', 'admin-js' ), time(), true );
        }
        
        if ( $overview_page == $hook ) {
            wp_enqueue_script( 'admin-overview-js', EXP_MAN_ASSETS . '/js/admin-overview.js', array( 'jquery', 'admin-js' ), time(), true );
        }
        
        if ( $export_import_page == $hook ){
            wp_enqueue_script( 'admin-export-import-js', EXP_MAN_ASSETS . '/js/admin-export-import.js', array( 'jquery', 'admin-js' ), time(), true );
        }
    }
}