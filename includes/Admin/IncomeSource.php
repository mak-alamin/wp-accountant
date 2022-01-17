<?php

namespace ExpenseManager\Admin;

use ExpenseManager\Installer;

/**
 * Class Income
 */

class IncomeSource extends Common{
    public $tablename;
    public $income_table;

    public function __construct()
    {
        $this->tablename = 'exp_man_income_sources';
        $this->income_table = 'exp_man_income';

        add_action('wp_ajax_add_new_income_source', [$this, 'add_new_income_source']);
        add_action('wp_ajax_delete_income_source', [$this, 'delete_income_source']);
    }

    public function render_page()
    {
        if ('wealcoder-expense-manager-income-sources' != $_GET['page']) {
            return;
        }

        $template = __DIR__ . '/views/income_sources/income_sources.php';

        if (file_exists($template)) {
            include_once $template;
        }
    }

    // Add New Income Source with Ajax
    public function add_new_income_source()
    {

        if (!current_user_can('manage_options')) {
            wp_die('You are not allowed to access this page!');
        }
        if (!wp_verify_nonce($_POST['nonce'], 'exp-manager-admin-nonce')) {
            wp_die("Nonce verification failed!");
        }

        $source_name = ( isset($_POST['name']) ) ? sanitize_text_field( trim( $_POST['name'] ) ) : '';

        if (empty($source_name)) {
            wp_send_json_error('Name is required.');
        } else {

            if ( ! $this->is_table_exists($this->tablename) ){
                $installer = new Installer();
                $installer->create_tables();
            }

            $data = [
                'source_name' => $source_name,
                'source_slug' => implode('-', explode(' ', strtolower($source_name) )),
            ]; 

            // Check if Source already exists
            if ( exp_man_is_data_exists( $this->tablename, 'source_slug', $data['source_slug']) ){
                wp_send_json_error("$source_name already exists. Please try a different name.");
            }

            if (isset($_POST['cat_id']) && $_POST['cat_id'] != 0) {
                update_data($this->tablename, $data, array('id' => $_POST['cat_id']));
            } else {
                insert_data($this->tablename, $data);
            }

            $updated_data = get_all($this->tablename, 'source_name', 'ASC');
            wp_send_json_success($updated_data);
        }
    }

    public function total_income_sources()
    {
        $sources = get_all($this->tablename);
        return count($sources);
    }

    // Render add new Source Form
    public function render_source_form()
    {
        $form = '';
        $form .= '<div id="newIncomeSourceForm" class="cat_source_paytype_form">';
       
        $form .= '<label for="source_name">' . __("Source Name", EXP_MAN_TXT_DOMAIN) . '</label><br><input type="text" name="source_name" id="source_name" class="regular-text" value="" maxlength="200"><br> <span class="source_message"></span>';
            
        $form .= '<input type="hidden" name="source_id" id="source_id" value="0">';
       
        $form .= get_submit_button('Add Source', 'primary', 'submit_income_source');

        $form .= '</div>';

        return $form;
    }


    // Render Income Source Data Table
    public function render_data_table()
    {
        $html = '';

        $sources = get_all($this->tablename, 'source_name', 'ASC');

        if (count($sources) > 0) {
            ob_start();
            foreach ($sources as $source) {
?>
                <tr class="data">
                    <td><?php echo $source->source_name; ?>

                        <div class="row-actions">
                            <span class="edit"><a href="#" data-id="<?php echo $source->id; ?>" data-name="<?php echo $source->source_name; ?>">Edit</a>
                                | </span>

                            <span class="delete"><a href="#" id="<?php echo $source->id; ?>" class="delete_btn">Delete</a> |
                            </span>
                        </div>
                    </td>
                </tr>
<?php

            }

            $html = ob_get_clean();
        } else {
            $html = '<tr class="data"><td>';
            $html .= __('No Income Source Founds.', EXP_MAN_TXT_DOMAIN);
            $html .= '</td></tr>';
        }

        return $html;
    }

    // Delete Income Source
    public function delete_income_source()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], 'exp-manager-admin-nonce')) {
            wp_send_json_error('Nonce Verification failed!');
        } else {
            delete_data($this->tablename, $_REQUEST['id']);

            update_data( $this->income_table, array( 'income_source_id' => 0 ), array( 'income_source_id' => $_REQUEST['id'] ) );

            $updated_data = get_all( $this->tablename );
            wp_send_json_success( $updated_data );
        }
    }
}
