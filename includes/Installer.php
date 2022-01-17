<?php

namespace ExpenseManager;

class Installer {

    public function install() {
        $this->set_default_options();

        $this->create_tables();

        $this->create_folders_and_files();
    }

    /**
     * ==================================
     * Set Default Options
     * ==================================
     */
    public function set_default_options() {
        add_option( 'exp_man_expense_limit', 100 );
        add_option( 'exp_man_income_limit', 100 );
        add_option( 'exp_man_load_more', 20 );
    }

    /**
     * ===================================
     * Create necessary Folders and Files
     * ===================================
     */
    public function create_folders_and_files()
    {
        $uploads_dir =  WP_CONTENT_DIR . '/uploads/wp_expense_manager_uploads';

        if ( ! is_dir( $uploads_dir ) ) {
            mkdir( $uploads_dir );
        }
    }


    /**
     * ==================================
     * Create Necessary Tables
     * ==================================
     */
    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Expense Category Table
        $em_expense_category_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}exp_man_expenses_category (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            category_name varchar(200) NOT NULL,
            category_slug varchar(200) NOT NULL,
            date_created timestamp NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Expense Table
        $em_expense_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}exp_man_expenses (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_date datetime NOT NULL,
            expense_amount double(53, 2) NOT NULL,
            expense_short_desc TEXT,
            expense_cat_id mediumint(9) NOT NULL,
            expense_paid_to varchar(200),
            expense_doc_name TEXT,
            expense_add_by varchar(200) NOT NULL,
            expense_status tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Income Sources Table
        $em_income_sources_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}exp_man_income_sources (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            source_name varchar(200) NOT NULL,
            source_slug varchar(200) NOT NULL,
            date_created timestamp NOT NULL,
            source_status tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Income Table
        $em_income_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}exp_man_income (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_date datetime NOT NULL,
            income_amount double(53, 2) NOT NULL,
            income_short_desc TEXT,
            income_source_id mediumint(9) NOT NULL,
            income_pay_type_id mediumint(9) NOT NULL,
            income_status tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Income Sources Table
        $em_pay_type_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}exp_man_payment_type (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            payment_type varchar(200) NOT NULL,
            payment_type_slug varchar(200) NOT NULL,
            date_created timestamp NOT NULL,
            type_status tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        if ( !function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta( $em_expense_category_sql );
        dbDelta( $em_expense_sql );
        dbDelta( $em_income_sources_sql );
        dbDelta( $em_income_sql );
        dbDelta( $em_pay_type_sql );
    }
}
