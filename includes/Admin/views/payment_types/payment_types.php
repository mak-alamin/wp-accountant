<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Payment Types', EXP_MAN_TXT_DOMAIN); ?>
    </h1>
    
    <hr>

    <div id="col-container">
        <div id="col-left">
            <div class="col-wrap">
                <h3><?php _e('Add New Payment Type', EXP_MAN_TXT_DOMAIN); ?></h3>
                <form action="" method="post">
                    <?php echo $this->render_payment_type_form(); ?>
                </form>
            </div>
        </div>

        <div id="col-right">
            <div class="col-wrap">
                <table class="wp-list-table widefat fixed striped" id="payment_type_table">
                    <tr>
                        <th><strong>Payment Types</strong></th>
                    </tr>

                    <?php echo $this->render_data_table(); ?>
                </table>
            </div>
        </div>
    </div>

</div>