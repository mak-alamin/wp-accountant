<?php

namespace ExpenseManager\Admin;

use ExpenseManager\Installer;

/**
 * Class ExpenseCategory
 */

class ExpenseCategory extends Common {
    public $messages = [];
    public $tablename;
    public $expense_table;

    public function __construct() {
        $this->expense_table = 'exp_man_expenses';
        $this->tablename = 'exp_man_expenses_category';

        add_action( 'wp_ajax_add_new_expense_cat', [$this, 'add_new_expense_category'] );

        add_action( 'wp_ajax_delete_expense_cat', [$this, 'delete_expense_category'] );
    }

    // Render Category Form
    public function render_category_form() {
        $form = '';
        $form .= '<div id="newExpenseCatForm" class="cat_source_paytype_form">';
        $form .= '<label for="name">Category Name</label> <br> <input type="text" name="name" id="name" class="regular-text" value="" maxlength="200" required> <br><span class="category_message"></span>';

        $form .= '<br><input type="hidden" name="cat_id" id="cat_id" value="0">';
        $form .= get_submit_button( __( 'Add Category', EXP_MAN_TXT_DOMAIN ), 'primary', 'submit_expense_category' );
        $form .= '</div>';

        return $form;
    }

    // Render Category Page
    public function render_page() {
        if ( 'wealcoder-expense-manager-expense-categories' != $_GET['page'] ) {
            return;
        }

        $template = __DIR__ . '/views/expense_category/category_new.php';

        if ( file_exists( $template ) ) {
            include_once $template;
        }
    }

    // Add Category with Ajax
    public function add_new_expense_category() {

        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( 'You are not allowed to access this page!' );
        }
        if ( !wp_verify_nonce( $_POST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_die( "Nonce verification failed!" );
        }

        if ( !$this->is_table_exists( $this->tablename ) ) {
            $installer = new Installer();
            $installer->create_tables();
        }

        $cat_name = ( isset( $_POST['name'] ) ) ? sanitize_text_field( trim( $_POST['name'] ) ) : '';

        if ( empty( $cat_name ) ) {
            wp_send_json_error( 'Name is required.' );
        } else {

            $data = [
                'category_name' => $cat_name,
                'category_slug' => implode( '-', explode( ' ', strtolower( $cat_name ) ) ),
            ];

            // Check if category already exists
            if ( exp_man_is_data_exists( $this->tablename, 'category_slug', $data['category_slug'] ) ) {
                wp_send_json_error( "$cat_name already exists. Please try a different name." );
            }

            if ( isset( $_POST['cat_id'] ) && $_POST['cat_id'] != 0 ) {
                update_data( $this->tablename, $data, array( 'id' => $_POST['cat_id'] ) );
            } else {

                insert_data( $this->tablename, $data );
            }

            $updated_data = get_all( $this->tablename, 'category_name', 'ASC' );
            wp_send_json_success( $updated_data );
        }
    }

    // Get total Expense Categories
    public function total_expense_categories() {
        $categories = get_all( $this->tablename );
        return count( $categories );
    }

    // Render Category Data Table
    public function render_data_table() {
        $html = '';

        $categories = get_data( $this->tablename, "category_slug != 'uncategorized'" ,'category_name', 'ASC' );

        if ( count( $categories ) > 0 ) {
            ob_start();
            foreach ( $categories as $expense_category ) {
                ?>
                <tr class="data">
                    <td><?php _e( $expense_category->category_name, EXP_MAN_TXT_DOMAIN );?>

                        <div class="row-actions">
                            <span class="edit"><a href="#" data-id="<?php echo $expense_category->id; ?>" data-name="<?php echo $expense_category->category_name; ?>">Edit</a>
                                | </span>

                            <span class="delete"><a href="#" id="<?php echo $expense_category->id; ?>" class="delete_btn">Delete</a> |
                            </span>
                        </div>
                    </td>
                </tr>
<?php

            }

            $html = ob_get_clean();
        } else {
            $html = '<tr class="data"><td>';
            $html .= __( 'No Expense Category Founds.', EXP_MAN_TXT_DOMAIN );
            $html .= '</td></tr>';
        }

        return $html;
    }

    public function delete_expense_category() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'exp-manager-admin-nonce' ) ) {
            wp_send_json_error( 'Nonce Verification failed!' );

        } else {
            delete_data( $this->tablename, $_REQUEST['id'] );

            $uncat_arr = get_data( $this->tablename, "category_slug='uncategorized'" );

            if ( !empty( $uncat_arr ) ) {
                $data = array( 'expense_cat_id' => $uncat_arr[0]->id );
                $where = array( 'expense_cat_id' => $_REQUEST['id'] );

                update_data( $this->expense_table, $data, $where );
            }

            $updated_data = get_data( $this->tablename, "category_slug != 'uncategorized'" ,'category_name', 'ASC' );

            wp_send_json_success( $updated_data );
        }
    }
}
