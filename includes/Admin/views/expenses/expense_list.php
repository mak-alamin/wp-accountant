<?php
if ( file_exists( __DIR__ . '/expense_popup_form.php' ) ) {
    require_once __DIR__ . '/expense_popup_form.php';
}

if ( file_exists( __DIR__ . '/expense_view.php' ) ) {
    require_once __DIR__ . '/expense_view.php';
}

$currency_symbol = ( !empty( get_option( 'select_currency' ) ) ) ? get_option( 'select_currency' ) : '$';

$load_more_class = '';
if ( count( $this->get_this_month_expenses() ) <= $this->exp_settings->expense_limit ) {
    $load_more_class = ' hidden';
}
?>

<div class="wrap expense-page">
    <h2 class="wp-heading-inline"><?php _e( 'Expenses', EXP_MAN_TXT_DOMAIN );?> <a href="#" id="add_new_btn"
            class="page-title-action">Add New Expense</a> </h2>

    <br>

    <!-- Expense Filter -->
    <?php
if ( file_exists( __DIR__ . '/expense_filter.php' ) ) {
    require_once __DIR__ . '/expense_filter.php';
}
?>
    <!-- Expense Filter ends -->
    <table class="wp-list-table widefat fixed striped" id="exepenses_table">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" name="select_all" id="select_all" value="1">
                    <strong>Paid For</strong>
                </th>
                <th><strong>Amount</strong></th>
                <th><strong>Category</strong></th>
                <th><strong>Description</strong></th>
                <th><strong>Added By</strong></th>
                <th><strong>Date</strong></th>
            </tr>
        </thead>

<?php
if ( count( $this->expenses ) > 0 ) {
    foreach ( $this->expenses as $expense ) {
        $cat_arr = get_data_by_id( $this->cat_tablename, $expense->expense_cat_id );

        if ( !empty( $cat_arr ) ) {
            $category = $cat_arr[0]->category_name;
        } else {
            $category = '';
        }
        ?>
            <tr class="data">
                <td>
                    <input type="checkbox" name="select_<?php echo $expense->id; ?>" id="select_<?php echo $expense->id; ?>" value="1">

                    <span class='paid_to'><?php echo ( !empty( $expense->expense_paid_to ) ) ? $expense->expense_paid_to : ''; ?>
                    </span>

                    <div class="row-actions">
                        <span class="view"><a href="" data-id="<?php echo $expense->id; ?>">View</a> |
                        </span>

                        <span class="edit"><a href="" data-id="<?php echo $expense->id; ?>">Edit</a> |
                        </span>

                        <span class="delete"><a href="#" class="delete_btn" id="<?php echo $expense->id; ?>">Delete</a> </span>
                    </div>
                </td>

                <td><?php echo $currency_symbol . ' '; ?> <span class="amount"> <?php echo number_format( $expense->expense_amount ); ?></span>
                </td>

                <td><span class='category'> <?php echo $category; ?> </span></td>

                <td class='short_desc'><?php echo ( !empty( $expense->expense_short_desc ) ) ? $expense->expense_short_desc : ''; ?>
                </td>

                <td class='add_by'><?php echo ( !empty( $expense->expense_add_by ) ) ? $expense->expense_add_by : ''; ?>
                </td>

                <td class='date'><?php echo ( !empty( $expense->created_date ) ) ? date_format( date_create( $expense->created_date ), 'j F, Y' ) : ''; ?>
                </td>
            </tr>
            <?php
}
} else {
    ?>
            <tr class="data">
                <td><?php _e( 'No Expense Founds.', EXP_MAN_TXT_DOMAIN );?></td>
            </tr>
            <?php
}
?>
    </table>
    <br>

    <div class="bottom-area">
        <div class="row-total">
            <h3><?php _e( 'Total: ', EXP_MAN_TXT_DOMAIN );
echo $currency_symbol . ' ';?><span id="total_expense"><?php echo number_format( $this->get_this_month_total() ); ?></span></h3>
        </div>
        <div class="load-more<?php echo $load_more_class; ?>">
            <a href="#" class="button button-primary" id="load_more">Load More</a>
            <div class="loader"></div>
        </div>
    </div>
</div>