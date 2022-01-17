<?php
    $currency_symbol = ( !empty( get_option( 'select_currency' ) ) ) ? get_option( 'select_currency' ) : '$';

    $total_income = $this->this_month_income();
    $total_expense = $this->this_month_expense();
    $total_profit = $total_income - $total_expense;
?>
<div class="wrap">
    <h1>Expense Manager</h1>
    <hr>

    <?php require_once __DIR__ . '/overview-filter.php'; ?>
    
    <div class="overview-boxes">
        <div class="total-box total-income">
            <h1> <p class="current_txt">Current Month</p> <?php _e('Total Income', EXP_MAN_TXT_DOMAIN); echo '</h1><h1>' . $currency_symbol . ' ' . '<span>' . number_format($total_income) . '</span>'; ?> </h1>
        </div>

        <div class="total-box total-expense">
            <h1> <p class="current_txt">Current Month</p> <?php _e('Total Expense', EXP_MAN_TXT_DOMAIN); echo '</h1><h1>' . $currency_symbol . ' ' . '<span>' . number_format($total_expense) . '</span>'; ?> </h1>
        </div>
        
        <div class="total-box total-profit">
            <h1> <p class="current_txt">Current Month</p> <?php _e('Total Profit', EXP_MAN_TXT_DOMAIN); echo '</h1><h1>' . $currency_symbol . ' ' . '<span>' . number_format($total_profit) . '</span>'; ?></h1>
        </div>

        <div class="total-box expense-cat">
            
                <?php
                    echo '<h2 class="count expense">';

                    _e('Total Expense Categories', EXP_MAN_TXT_DOMAIN);
                    
                    echo '</h2> <h1><a href="'. admin_url('admin.php?page=wealcoder-expense-manager-expense-categories') .'">' . $this->expense_categories->total_expense_categories() . '</a> </h1>';
                
                    echo '<h1><small><a href="admin.php?page=wealcoder-expense-manager-expense-categories">View All</a></small></h1>';
                ?>   
        </div>
       
        <div class="total-box income-source">
                <?php
                    echo '<h2 class="count">';

                    _e('Total Income Sources', EXP_MAN_TXT_DOMAIN); echo  '</h2> <h1><a href="'. admin_url('admin.php?page=wealcoder-expense-manager-income-sources') .'">' . $this->income_sources->total_income_sources() . '</a></h1>';

                    echo '<h1><small><a href="admin.php?page=wealcoder-expense-manager-income-sources">View All</a></small>';
                ?>  
            </h1>
        </div>
    </div>

    <br><br><br>
    <div class="recent-expenses">
        <h2>Recent Expenses <a href="<?php echo admin_url('admin.php?page=wealcoder-expense-manager-expenses')?>">View All</a> </h2>
    
        <table class="wp-list-table widefat fixed striped">
            <tr>
                <th><strong>Paid For</strong></th>
                <th><strong>Amount</strong></th>
                <th><strong>Category</strong></th>
                <th><strong>Description</strong></th>
                <th><strong>Added By</strong></th>
                <th><strong>Date</strong></th>
            </tr>

            <?php
                $expenses = get_all( 'exp_man_expenses', 'id', 'DESC', 5 );
                if ( count( $expenses ) > 0 ) {
                    foreach ( $expenses as $expense ) {
                        $cat_arr = get_data_by_id( 'exp_man_expenses_category', $expense->expense_cat_id );

                        $category = ( !empty($cat_arr) ) ?  $cat_arr[0]->category_name : '';
                    ?>
                        <tr class="data">
                            <td class='paid_to'><?php echo ( !empty( $expense->expense_paid_to ) ) ? $expense->expense_paid_to : ''; ?>
                            </td>
 
                            <td><?php echo $currency_symbol . ' '; ?> <span class="amount"> <?php echo number_format($expense->expense_amount); ?></span></td>
                        
                            <td class='category'><?php echo $category; ?></td>
                        
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
    </div>

    <br><br>
    <div class="recent-incomes">
        <h2>Recent Incomes <a href="<?php echo admin_url('admin.php?page=wealcoder-expense-manager-income')?>">View All</a> </h2>
        <table class="wp-list-table widefat fixed striped">
            <tr>
                <th><strong>Source</strong></th>
                <th><strong>Amount</strong></th>
                <th><strong>Description</strong></th>
                <th><strong>Payment Type</strong></th>
                <th><strong>Date</strong></th>
            </tr>
            <?php echo $this->render_recent_incomes_table(); ?>

        </table>
    </div>
</div>