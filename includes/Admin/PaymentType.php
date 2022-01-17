<?php

namespace ExpenseManager\Admin;

use ExpenseManager\Installer;

/**
 * Class PaymentType
 */

class PaymentType extends Common{
    public $tablename;
    public $payment_types;
    public $income_table;

    public function __construct() {
        $this->tablename = "exp_man_payment_type";
        $this->income_table = 'exp_man_income';
        
        add_action( 'wp_ajax_add_new_payment_type', [$this, 'add_new_payment_type'] );
        add_action( 'wp_ajax_delete_payment_type', [$this, 'delete_payment_type'] );
    }

    public function render_page() {
        if ( 'wealcoder-expense-manager-payment-types' != $_GET['page'] ) {
            return;
        }

        $template = __DIR__ . '/views/payment_types/payment_types.php';

        if ( file_exists( $template ) ) {
            include_once $template;
        }
    }

    // Add New Payment Type
    public function add_new_payment_type() {

        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( 'You are not allowed to access this page!' );
        }
        if ( !wp_verify_nonce( $_POST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_die( "Nonce verification failed!" );
        }

        $type_name = ( isset( $_POST['name'] ) ) ? sanitize_text_field( $_POST['name'] ) : '';

        if ( empty( $type_name ) ) {
            wp_send_json_error( 'Name is required.' );
        
        } else {
            
            if ( ! $this->is_table_exists($this->tablename) ){
                $installer = new Installer();
                $installer->create_tables();
            }

            $data = [
                'payment_type' => $type_name,
                'payment_type_slug' => str_replace(' ', '-', strtolower($type_name)),
            ];

            // Check if payment type already exists
            if ( exp_man_is_data_exists( $this->tablename, 'payment_type_slug', $data['payment_type_slug']) ){
                wp_send_json_error("$type_name already exists. Please try a different name.");
            }

            if ( isset( $_POST['type_id'] ) && $_POST['type_id'] != 0 ) {
                update_data( $this->tablename, $data, array( 'id' => $_POST['type_id'] ) );
            } else {
                insert_data( $this->tablename, $data );
            }

            $updated_data = get_all( $this->tablename, 'payment_type', 'ASC');
            wp_send_json_success( $updated_data );
        }
    }

    // Render add new Payment Type Form
    public function render_payment_type_form()
    {
        $form = '';
        $form .= '<div id="newPaymentTypeForm" class="cat_source_paytype_form">';
       
        $form .= '<label for="pay_type_name">' . __("Payment Type", EXP_MAN_TXT_DOMAIN) . '</label><br><input type="text" name="pay_type_name" id="pay_type_name" class="regular-text" value="" maxlength="200"><br> <span class="pay_type_message"></span>';
            
        $form .= '<input type="hidden" name="pay_type_id" id="pay_type_id" value="0">';

        $form .= get_submit_button('Add Payment Type', 'primary', 'submit_payment_type');


        $form .= '</div>';

        return $form;
    }

    // Render Payment Type data table
    public function render_data_table() {
        $this->payment_types = get_all( $this->tablename, 'payment_type', 'ASC' );
        $html = '';

        if ( count( $this->payment_types ) > 0 ) {
            ob_start();
            foreach ( $this->payment_types as $payment_type ) {
                ?>
                <tr class="data">
                    <td><?php echo $payment_type->payment_type; ?>

                        <div class="row-actions">
                            <span class="edit"><a href="#" data-id="<?php echo $payment_type->id; ?>" data-name="<?php echo $payment_type->payment_type; ?>">Edit</a>
                                | </span>

                            <span class="delete"><a href="#" id="<?php echo $payment_type->id; ?>" class="delete_btn">Delete</a> |
                            </span>
                        </div>
                    </td>
                </tr>
<?php

            }

            $html = ob_get_clean();
        } else {
            $html = '<tr class="data"><td>';
            $html .= __( 'No Payment Type Founds.', EXP_MAN_TXT_DOMAIN );
            $html .= '</td></tr>';
        }

        return $html;
    }

    // Delete Payment Type
    public function delete_payment_type()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], 'exp-manager-admin-nonce')) {
            wp_send_json_error('Nonce Verification failed!');
        } else {
            delete_data($this->tablename, $_REQUEST['id']);

            update_data( $this->income_table, array( 'income_pay_type_id' => 0 ), array( 'income_pay_type_id' => $_REQUEST['id'] ) );

            $updated_data = get_all( $this->tablename );

            wp_send_json_success( $updated_data );
        }
    }
}