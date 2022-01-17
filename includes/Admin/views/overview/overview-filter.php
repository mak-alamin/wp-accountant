<div class="filter-container">
    <div class="col-left">
        <h2>Overview</h2>
    </div>
    <div class="col-right">
        <form action="#" method="get">
            <h3 class="filter_text">Filter: </h3>
            <label for="filter_overview"> </label>
            <select name="filter_overview" id="filter_overview" class="postform">
                <option value="this_month">Current Month</option>
                <option value="last_month">Last Month</option>
                <option value="last_3_months">Last 3 Months</option>
                <option value="yearly">Yearly</option>
                <option value="lifetime">Life Time</option>
                <option value="custom_date">Custom</option>
            </select>

            <div class="custom_date_filter">
                <div class="start-date">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo date( 'Y-m-01' ); ?>">
                </div>

                <div class="end-date">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo date( 'Y-m-t' ); ?>">
                </div>
            </div>

            <div class="yearly_filter">
                <label for="year"> </label>
                <select name="year" id="year" class="postform">
                    <option value="0" selected>All Year</option>
                    
                    <?php render_years(); ?>
                </select>

                <label for="month"></label>
                <select name="month" id="month" class="postform">
                    <option value="0" selected>All Month</option>
                
                    <?php render_months(); ?>
                </select>
            </div>
        </form>
    </div>
</div>