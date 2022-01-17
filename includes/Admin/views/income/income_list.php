<?php
if ( file_exists( __DIR__ . '/income_popup_form.php' ) ) {
    require_once __DIR__ . '/income_popup_form.php';
}
if ( file_exists( __DIR__ . '/income_view.php' ) ) {
    require_once __DIR__ . '/income_view.php';
}
$currency_symbol = ( !empty( get_option( 'select_currency' ) ) ) ? get_option( 'select_currency' ) : '$';

$load_more_class = '';

if ( count( $this->get_this_month_incomes() ) <= $this->exp_settings->income_limit ) {
    $load_more_class = ' hidden';
}
?>

<div class="wrap">
    <h2 class="wp-heading-inline"><?php _e( 'Incomes', EXP_MAN_TXT_DOMAIN );?> <a href="#" id="add_new_btn" class="page-title-action">Add New Income</a> </h2>
    <br>

     <!-- Income Filter -->
     <?php
if ( file_exists( __DIR__ . '/income_filter.php' ) ) {
    require_once __DIR__ . '/income_filter.php';
}
?>
    <!-- Income Filter ends -->

    <table class="wp-list-table widefat fixed striped" id="incomes_table">
        <tr>
            <th><input type="checkbox" name="select_all" id="select_all" value="1"> <strong>Source</strong></th>
            <th><strong>Amount</strong></th>
            <th><strong>Description</strong></th>
            <th><strong>Payment Type</strong></th>
            <th><strong>Date</strong></th>
        </tr>
        <?php echo $this->render_data_table(); ?>
    </table>

    <br>
    <div class="bottom-area">
        <div class="row-total">
            <h3>Total: <?php echo $currency_symbol . ' '; ?><span id="total_income"><?php echo number_format( $this->get_this_month_total() ); ?></span></h3>
        </div>
        <div class="load-more<?php echo $load_more_class; ?>">
            <a href="#" class="button button-primary" id="load_more">Load More</a>
            <div class="loader"></div>
        </div>
    </div>
</div>