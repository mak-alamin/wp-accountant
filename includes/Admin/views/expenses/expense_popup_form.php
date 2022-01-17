<div id="expense_popup" class="modal">
    <form action="" method="POST" class="modal-content" id="expenseForm" enctype="multipart/form-data">
        <div class="container">
            <span class="close" title="Close Modal">&times;</span>
            <h3><?php _e( 'Add New Expense', EXP_MAN_TXT_DOMAIN );?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td>
                            <label for="paid_to"><?php _e( 'Paid For', EXP_MAN_TXT_DOMAIN );?></label>
                            <input type="text" name="paid_to" id="paid_to" class="regular-text" value="" required>
                            <span class="paid_for_err error_text"></span>

                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="amount"><?php _e( 'Amount', EXP_MAN_TXT_DOMAIN );?></label>
                            <input type="number" name="amount" id="amount" class="regular-text" min="0" required><span class="amount_err error_text"></span>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="purpose"><?php _e( 'Category', EXP_MAN_TXT_DOMAIN );?></label>
                            
                            <div class="exp_man_row">
                                <select name="purpose" id="purpose">
                                    <option value="0" disabled selected>Select a Category</option>
    <?php
        foreach ( $this->categories as $key => $category ) {
            echo '<option value="' . $category->id . '">' . $category->category_name . '</option>';
        }
    ?>
                                </select>
                                
                                <a href="#" id="toggle_category_form" class="button-secondary toggle_button"> + </a>
                            </div>

                            <?php echo $this->category_obj->render_category_form(); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="uploaded_file_url"><?php _e( 'Document (optional)', EXP_MAN_TXT_DOMAIN );?></label>
                            
                            <input type="text" name="uploaded_file_url" id="uploaded_file_url" value="" placeholder="Paste URL or upload"> 
                            <span class="document_err error_text"></span><br>
                            
                            <button id="doc_upload_button" class="button button-secondary">Upload Document</button>
                            
                            <button id="doc_remove_button" class="button hidden button-secondary">Remove</button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="short_desc"><?php _e( 'Description (optional)', EXP_MAN_TXT_DOMAIN );?></label>
                            <textarea name="short_desc" id="short_desc" rows="4"></textarea>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="expense_date"><?php _e( 'Date', EXP_MAN_TXT_DOMAIN );?></label>
                            <input type="date" name="expense_date" id="expense_date" class="regular-text" value="<?php echo date( 'Y-m-d' ); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <input type="hidden" name="expense_form" value="1">
            <input type="hidden" name="expense_id" id="expense_id" value="0">
            <input type="hidden" name="action" value="add_new_expense">

            <div class="row submit_btns">
                <p class="message"></p>
                <p class="error_text"></p>
                <div class="loader"></div>
<?php
    wp_nonce_field( 'add-new-expense-nonce' );
    submit_button( __( 'Save & Add More', EXP_MAN_TXT_DOMAIN ), 'primary', 'save_and_add' );
    submit_button( __( 'Save & Close', EXP_MAN_TXT_DOMAIN ), 'primary', 'save_and_close' );

    submit_button('Update', 'button button-primary hidden', 'update_expense');
?>
            </div>
        </div>
    </form>
</div>