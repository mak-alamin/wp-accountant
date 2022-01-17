<?php

namespace ExpenseManager\Admin;

use ExpenseManager\Installer;

// Export Import handler class.
class ExportImport extends Common{

    private $errors;
    private $messages;

    public function __construct() {

        add_action( 'wp_ajax_exp_man_export', array( $this, 'export' ) );
        add_action( 'wp_ajax_exp_man_import', array( $this, 'import' ) );
    }

    public function render_page() {
        if ( 'wealcoder-expense-manager-export-import' != $_GET['page'] ) {
            return;
        }

        $template = __DIR__ . '/views/export-import/export-import.php';

        if ( file_exists( $template ) ) {
            include_once $template;
        }
    }

    public function export() {
        if ( isset( $_REQUEST['export_submit'] ) ) {
            $data = [];

            if ( $_REQUEST['export_submit'] == "export_incomes" ) {
                $data['incomes'] = get_all( 'exp_man_income' );
                $data['income_sources'] = get_all( 'exp_man_income_sources' );
                $data['payment_type'] = get_all( 'exp_man_payment_type' );
                $file_name = 'incomes.json';

            } else if ( $_REQUEST['export_submit'] == "export_expenses" ) {
                $data['expenses'] = get_all( 'exp_man_expenses' );
                $data['expenses_category'] = get_all( 'exp_man_expenses_category' );
                $file_name = 'expenses.json';

            } else { // Export all
                $data['incomes'] = get_all( 'exp_man_income' );
                $data['income_sources'] = get_all( 'exp_man_income_sources' );
                $data['expenses'] = get_all( 'exp_man_expenses' );
                $data['expenses_category'] = get_all( 'exp_man_expenses_category' );
                $data['payment_type'] = get_all( 'exp_man_payment_type' );
                $file_name = 'all_data.json';
            }

            $data['filename'] = $file_name;

            echo json_encode( $data );
            die();
        }
    }

    public function import() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Access Denied!' );
        }

        if ( !wp_verify_nonce( $_POST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_send_json_error( "Nonce verification failed!" );
        }

        $filename = $this->upload_file( $_FILES['file_to_import'], array('json'), 5242880, false );

        $file = fopen( EXP_MAN_PATH . "/assets/uploads/$filename", 'r' );
        
        $json_data = fread( $file, filesize( EXP_MAN_PATH . "/assets/uploads/$filename" ) );

        $data = json_decode( $json_data );

        // Import expenses
        if ( isset( $data->expenses ) ) {
            $this->import_data( $data->expenses, 'expense' );
        }

        // Import incomes
        if ( isset( $data->incomes ) ) {
            $this->import_data( $data->incomes, 'income' );
        }

        // Import Expenses Category
        if ( isset( $data->expenses_category ) ) {
            $this->import_data( $data->expenses_category, 'expenses_category' );
        }

        // Import Income Sources
        if ( isset( $data->income_sources ) ) {
            $this->import_data( $data->income_sources, 'income_sources' );
        }

        // Import Payment Types
        if ( isset( $data->payment_type ) ) {
            $this->import_data( $data->payment_type, 'payment_type' );
        }

        if ( !empty( $this->errors ) ) {
            wp_send_json_error( $this->errors );

        } else {
            fclose( $file );
            unlink( EXP_MAN_PATH . "/assets/uploads/$filename" );

            wp_send_json_success( [
                'congrats'  => 'Congrats! All data imported Successfully.',
                'messages' => $this->messages,
            ] );
        }
    }

    /**
     * Import Data function
     */
    private function import_data( $all_data, $type ) {
        if ( empty( $all_data ) && !is_array( $all_data ) ) {return;}

        $tablename = 'exp_man_' . $type;
        $data = [];

        global $wpdb;

        $table_exists = "SELECT 1 FROM {$wpdb->prefix}$tablename";
        
        if ( ! $table_exists ){
            $installer = new Installer();
            $installer->create_tables();
        }

        foreach ( $all_data as $key => $single_data ) {
            if ( $type == 'expense' ) {
                $tablename = 'exp_man_' . $type . 's';

                $data['created_date'] = $single_data->created_date;
                $data['expense_amount'] = $single_data->expense_amount;
                $data['expense_cat_id'] = $single_data->expense_cat_id;
                $data['expense_short_desc'] = $single_data->expense_short_desc;
                $data['expense_paid_to'] = $single_data->expense_paid_to;
                $data['expense_add_by'] = $single_data->expense_add_by;

            } else if ( $type == 'income' ) {
                $data['created_date'] = $single_data->created_date;
                $data['income_amount'] = $single_data->income_amount;
                $data['income_source_id'] = $single_data->income_source_id;
                $data['income_short_desc'] = $single_data->income_short_desc;
                $data['income_pay_type_id'] = $single_data->income_pay_type_id;

            } else if ( $type == 'expenses_category' ) {

                if ( exp_man_is_data_exists( $tablename, 'category_slug', $single_data->category_slug ) ) {
                    $this->messages["category_exists_" . $single_data->id] = "Category " . $single_data->category_name . " already exists.";
                    continue;
                }

                $data['category_name'] = $single_data->category_name;
                $data['category_slug'] = $single_data->category_slug;

                $data['date_created'] = isset( $single_data->date_created ) ? $single_data->date_created : '';

            } else if ( $type == 'income_sources' ) {
                if ( exp_man_is_data_exists( $tablename, 'source_slug', $single_data->source_slug ) ) {
                    $this->messages["source_exists_" . $single_data->id] = "Source " . $single_data->source_name . " already exists.";
                    continue;
                }

                $data['source_name'] = $single_data->source_name;
                $data['source_slug'] = $single_data->source_slug;

                $data['date_created'] = isset( $single_data->date_created ) ? $single_data->date_created : '';

            } else if ( $type == 'payment_type' ) {
                if ( exp_man_is_data_exists( $tablename, 'payment_type_slug', $single_data->payment_type_slug ) ) {
                    $this->messages["payment_type_exists_" . $single_data->id] = "Payment Type " . $single_data->payment_type . " already exists.";
                    continue;
                }

                $data['payment_type'] = $single_data->payment_type;
                $data['payment_type_slug'] = $single_data->payment_type_slug;
                $data['date_created'] = isset( $single_data->date_created ) ? $single_data->date_created : '';
            }

            $imported = insert_data( $tablename, $data );

            if ( !$imported ) {
                $this->errors["err_" . $type . $single_data->id] = "Error importing $type id-" . $single_data->id;
            }
        }
    }
}