<div class="wrap">
    <h2 class="wp-heading-inline"><?php _e( 'Export / Import', EXP_MAN_TXT_DOMAIN );?> </h2>
    <p class="description">Export or Import Incomes and Expenses</p>
    <hr>

    <div class="export-area">
        <h3>Export</h3>

        <form action="" method="post" id="export_form">
            <input type="radio" name="export_submit" id="export_all" value="export_all" checked> 
            <label for="export_all">All</label>
            <br>
            
            <input type="radio" name="export_submit" id="export_incomes" value="export_incomes">
            <label for="export_incomes">Incomes</label>
            <br>
            
            <input type="radio" name="export_submit" id="export_expenses" value="export_expenses"> 
            <label for="export_expenses">Expenses</label>
            <br> <br>

            <input type="hidden" name="action" value="exp_man_export">

            <input type="submit" value="Download Export File" class="button button-primary">
        </form>
    </div>

    <div class="import-area">
    <h3>Import</h3>
        <form action="" method="post" id="import_form" enctype="multipart/form-data">
            <label for="file_to_import">Choose an exported file (JSON format) : </label>
            <input type="file" name="file_to_import" id="file_to_import" accept=".json" required />
            <br>

            <input type="hidden" name="action" value="exp_man_import">
            <br>

            <?php
                wp_nonce_field('exp_man_import');
            ?>

            <input type="submit" name="import_submit" value="Import" class="button button-primary">
            <div class="loader"></div>
        </form>

        <p class="message"></p>
        <p class="error_text"></p>
    </div>
</div>