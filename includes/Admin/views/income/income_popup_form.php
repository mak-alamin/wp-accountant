<div id="income_popup" class="modal">
    <form action="" method="POST" class="modal-content" id="incomeForm">
        <div class="container">
            <span class="close" title="Close Modal">&times;</span>
            <h3><?php _e('Add New income', EXP_MAN_TXT_DOMAIN); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td>
                            <label for="income_source"><?php _e('Source', EXP_MAN_TXT_DOMAIN); ?></label> <br>
                            <div class="exp_man_row">
                                <select name="income_source" id="income_source">
                                    <option value="0" disabled selected>Select a source</option>
                                    <?php
                                    foreach ($this->sources as $key => $source) {
                                        echo '<option value="' . $source->id . '">' . $source->source_name . '</option>';
                                    }
                                    ?>
                                </select>
                                <a href="#" class="button-secondary toggle_button"> + </a>

                            </div>
                            <?php echo $this->source_obj->render_source_form(); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="amount"><?php _e('Amount', EXP_MAN_TXT_DOMAIN); ?></label> <br>
                            <input type="number" name="amount" id="amount" class="regular-text" min="0" required><span class="amount_err error_text"></span>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="payment_type"><?php _e('Payment Type', EXP_MAN_TXT_DOMAIN); ?></label> <br>
                            <div class="exp_man_row">
                                <select name="payment_type" id="payment_type">
                                    <option value="0" disabled selected>Select a Payment Type</option>
                                    <?php
                                    foreach ($this->pay_types as $key => $pay_type) {
                                        echo '<option value="' . $pay_type->id . '">' . $pay_type->payment_type . '</option>';
                                    }
                                    ?>
                                </select>
                                <a href="#" class="button-secondary toggle_button"> + </a>
                            </div>

                            <?php echo $this->pay_type_obj->render_payment_type_form(); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="short_desc"><?php _e( 'Description', EXP_MAN_TXT_DOMAIN );?></label> <br>
                            <textarea name="short_desc" id="short_desc" rows="4"></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label for="name"><?php _e('Date', EXP_MAN_TXT_DOMAIN); ?></label> <br>
                            <input type="date" name="income_date" id="income_date" class="regular-text" value="<?php echo date('Y-m-d'); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>

            <input type="hidden" name="income_form" value="1">
            <input type="hidden" name="income_id" id="income_id" value="0">
            <input type="hidden" name="action" value="save_income">

            <div class="row submit_btns">
                <p class="message"></p>
                <p class="error_text"></p>
                <div class="loader"></div>
                <?php
                wp_nonce_field('save-income-nonce');
                submit_button(__('Save & Add More', EXP_MAN_TXT_DOMAIN), 'primary', 'save_and_add');
                submit_button(__('Save & Close', EXP_MAN_TXT_DOMAIN), 'primary', 'save_and_close');

                submit_button('Update', 'button button-primary hidden', 'update_income');
                ?>
            </div>
        </div>
    </form>
</div>