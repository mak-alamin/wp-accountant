let profit = jQuery(".total-box.total-profit h1 span").text();
let profit_box = jQuery(".total-box.total-profit");

function change_colors(profit){
    if(parseFloat(profit) <= 0){
        profit_box.css({
            'backgroundColor': 'rgb(191 5 48)',
        });
    } else {
        profit_box.css({
            'backgroundColor': '#00BAB0',
        })
    }
}
change_colors(profit);


/**
 * ========================================
 * Filter Overview
 * ========================================
 */

// Filter Overview on change
jQuery("[name='filter_overview'], [name='year'], [name='month'], [name='start_date'], [name='end_date']").on("change", function(e){
    e.preventDefault();
    filter_overview();
});


// Filter Overview Function
function filter_overview(){
    let data = {
        action: 'filter_overview',
        nonce: exp_manager_ajax_obj.nonce,
        filter_type: jQuery("[name='filter_overview']").val()
    }

    if ( data.filter_type == 'yearly' ) {
        jQuery(".yearly_filter").css('display','inline-block');
        jQuery(".custom_date_filter").css('display','none'); 
        filter_overview_yearly(data);
        
    } else if ( data.filter_type == 'custom_date' ) {
        jQuery(".yearly_filter").css('display','none');
        jQuery(".custom_date_filter").css('display','inline-block');
        filter_overview_custom_date(data); 

    } else {
        jQuery(".yearly_filter").css('display','none');
        jQuery(".custom_date_filter").css('display','none'); 

        jQuery.ajax({
            type: "GET",
            url: exp_manager_ajax_obj.ajax_url,
            data: data,
            success: function (response) {
                update_overview(response.data);
            },
            error: function (err) {
                console.log(err);
            },
        });
    }
}

// Yearly Filter
function filter_overview_yearly( data = {} ){
    data.action = 'filter_overview_yearly';
    data.year = jQuery("[name='year']").val();
    data.month = jQuery("[name='month']").val();

    jQuery.ajax({
        type: "GET",
        url: exp_manager_ajax_obj.ajax_url,
        data: data,
        success: function (response) {
            update_overview(response.data);
        },
        error: function (err) {
            console.log(err);
        },
    });
}

// Filter Overview Custom Date
function filter_overview_custom_date( data = {} ){
    data.action = 'filter_overview_custom_date';
    data.start_date = jQuery("[name='start_date']").val();
    data.end_date = jQuery("[name='end_date']").val();

    jQuery.ajax({
        type: "GET",
        url: exp_manager_ajax_obj.ajax_url,
        data: data,
        success: function (response) {
            console.log(response);
            update_overview(response.data);
        },
        error: function (err) {
            console.log(err);
        },
    });
}

// Update Overview Function
function update_overview( data = {} ){
    jQuery(".total-income span").text( new Intl.NumberFormat().format(data.income) );
    jQuery(".total-expense span").text( new Intl.NumberFormat().format(data.expense) );
    jQuery(".total-profit span").text( new Intl.NumberFormat().format(data.profit));

    change_colors(data.profit);

    jQuery(".total-box p.current_txt").text(data.current_text);
}