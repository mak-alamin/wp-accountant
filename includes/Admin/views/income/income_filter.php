<div class="table-title">
    <h3>Income List</h3>
    <a href="" id="download_pdf" class="button-primary">Download PDF</a>
</div>

<div class="table_nav_top">
    <form action="" method="post" id="bulk_action_form">
        <select name="bulk_action" id="bulk_action" class="postform">
            <option value="-1">Bulk Actions</option>
            <option value="bulk_delete">Delete</option>
        </select>
        <input type="submit" value="Apply" class="button action">
    </form>

    <div class="filter-container">
        <form action="#" method="get" id="income_filter_form">
            <h3 class="filter_text">Filter: </h3>
            <div class="multiselect">
                <div class="selectBox" onclick="showCheckboxes()">
                    <select>
                        <option>Sources</option>
                    </select>
                    <div class="overSelect"></div>
                </div>
                <div id="multi_checkboxes">
                    <label for="all_source">
                    <input type="checkbox" id="all_source" value="all" />Select All</label>
                    <?php
                        foreach ($this->sources as $key => $source) {
                            echo '<label for="source_id_' . $source->id . '">';
                         
                            echo '<input class="source" type="checkbox" id="source_id_' . $source->id . '" value="' . $source->id . '" />' . $source->source_name . '</label>';
                        }
                    ?>
                     <label for="source_not_defined">
                    <input type="checkbox" id="source_not_defined" class="source" value="0" />Not Defined</label>
                </div>
            </div>

            <select name="filter_by_time" id="filter_by_time" class="postform">
                <option value="this_month">Current Month</option>
                <option value="lifetime">Life Time</option>
                <option value="last_month">Last Month</option>
                <option value="last_3_months">Last 3 Months</option>
                <option value="yearly">Yearly</option>
                <option value="custom_date">Custom</option>
            </select>

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
            <button class="button button-secondary hidden" id="clear_filter">Clear Filter</button>
        </form>


        <!-- Search Form -->
        <form action="#" method="GET" id="search_form">
            <label for="search_income"></label>
            <input type="text" name="search_income" id="search_income" placeholder="Search income" value="">
        </form>
    </div>
</div>