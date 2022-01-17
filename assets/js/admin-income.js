/**
 * =======================================
 * PDF Download
 * =======================================
 */
 jQuery(document).ready(function(){ 
    jQuery("#download_pdf").on("click", function(e){
        e.preventDefault();

        let table_rows = jQuery("#incomes_table tr.data");

        let data = [];
        let single_data = [];

        jQuery.each(table_rows, function(i, item){
            single_data = [
                jQuery(item).find(".source").text().trim(),
                jQuery(item).find(".amount").text().trim(),
                jQuery(item).find(".short_desc").text().trim(),
                jQuery(item).find(".pay_type").text().trim(),
                jQuery(item).find(".date").text().trim(),
            ]

            data.push(single_data);
        });

        let total_amount = jQuery("#total_income").text();
        data.push(['Total Income: ', total_amount]);

        let pdf = new jsPDF();


        pdf.autoTable({
            head: [['Source', 'Amount', 'Description', 'Payment Type', 'Date']],
            body: data,
            didDrawPage: function (res){
                // Header
                pdf.setFontSize(18);
                pdf.setTextColor(40);
                pdf.setFontStyle('normal');
                //pdf.addImage(headerImgData, 'JPEG', res.settings.margin.left, 20, 50, 50);
                pdf.text("Incomes", res.settings.margin.left, 20);
            },
            willDrawCell: function (res) {
                if (res.row.index === data.length-1) {
                    pdf.setFillColor(58, 58, 58);
                    pdf.setTextColor(255, 255, 255);
                    pdf.setFontStyle("bold");
                }
            },
            margin: {
                top: 30
            }
        });
        
        pdf.save('incomes.pdf');
    });
 });


/**
 * =============================================
 * Add New Income Source
 * =============================================
 */
 jQuery("#submit_income_source").on("click", function (e) {
    e.preventDefault();
    let data = {};

    let name = jQuery("#newIncomeSourceForm input[name='source_name']").val();
    let source_id = jQuery("#newIncomeSourceForm input[name='source_id']").val();

    data.name = name;
    data.source_id = source_id;
    data.action = 'add_new_income_source';
    data.nonce = exp_manager_ajax_obj.nonce;

    jQuery.ajax({
        type: "POST",
        url: exp_manager_ajax_obj.ajax_url,
        data: data,
        success: function (response) {
            if ( ! response.success ){
                jQuery("span.source_message").removeClass("success");
                jQuery("span.source_message").addClass("error_text");
                jQuery("span.source_message").text(response.data);
                return;
            } else {
                jQuery("span.source_message").removeClass("error_text");
                jQuery("span.source_message").addClass("success");
            
                if ( 0 == jQuery("#source_id").val()){
                    jQuery("span.source_message").text("Source Added.");
                } else {
                    jQuery("span.source_message").text("Source Updated.");
                }
            }
            
            jQuery("#newIncomeSourceForm input").each(function (i, item) {
                jQuery(this).val('');
            }); 
            
            if ( income_source_page == page ){
                update_data_table( "#income_source_table",response );
            } else {
                update_select_box( "source", response );
            }

            jQuery("#source_id").val(0);
            jQuery("#submit_income_source").val("Add Source");
        },
        error: function (err) {
            console.log(err);
        },
    });
});

/**
 * =========================================
 * Update Income Source
 * =========================================
 */
 jQuery("#income_source_table").on("click", ".edit a",function (e) {
    e.preventDefault();
    let id = jQuery(this).attr('data-id');
    let name = jQuery(this).attr('data-name');

    jQuery("#source_name").val(name);
    jQuery("#source_id").val(id);
    jQuery("#submit_income_source").val("Update Source");
});

/**
 * ========================================
 * View Income 
 * ========================================
 */
 jQuery("#incomes_table").on("click", ".view a", function(e){
    e.preventDefault();
    
    let id = jQuery(this).attr("data-id");

    jQuery.ajax({
        type: 'GET',
        url: exp_manager_ajax_obj.ajax_url,
        data: {
            action: 'get_single_income',
            nonce: exp_manager_ajax_obj.nonce,
            id: id,
        }, 
        success: function(response){
            let income = response.data;
            console.log(income);
            let d = new Date(income.created_date);
            let datestring =
                d.getDate() +
                " " +
                months[d.getMonth()] +
                ", " +
                d.getFullYear();

            jQuery(".view_source").text(income.income_source);
            jQuery(".view_amount").text(income.income_amount);
            jQuery(".view_short_desc").text(income.income_short_desc);
            jQuery(".view_pay_type").text(income.payment_type);

            jQuery(".view_date").text(datestring);
        
            jQuery("#income_view").fadeIn();

        },
        error: function(err){
            console.log(err);
        }
    });
});



/**
 * ========================================
 * Update Income 
 * ========================================
 */

// Open Update Modal
jQuery("#incomes_table").on("click", ".edit a", function(e){
    e.preventDefault();

    let id = jQuery(this).attr("data-id");  

    jQuery.ajax({
        type: "GET",
        url: exp_manager_ajax_obj.ajax_url,
        data: {
            action: 'get_single_income',
            nonce: exp_manager_ajax_obj.nonce,
            id: id,
        },
        success: function (response) {
            let income = response.data;

            console.log(income);

            let date = income.created_date.substring(0, 10);
            
            jQuery("[name='income_id']").val(id);
            jQuery("[name='income_date']").val(date);
            jQuery("[name='amount']").val(income.income_amount);
            jQuery("[name='income_source']").val(income.income_source_id);
            jQuery("[name='short_desc']").val(income.income_short_desc);
            jQuery("[name='payment_type']").val(income.income_pay_type_id);
        
            jQuery("#save_and_add").hide();
            jQuery("#save_and_close").hide();
            jQuery("#update_income").show();
        
            jQuery("#income_popup").fadeIn();
        },
        error: function (err) {
            console.log(err);
        },
    });
});


// Update Income Data on click "Update" button
jQuery("#update_income").on("click", function(e){
    e.preventDefault();
    save_form_data(form_id, modal_id, data_table_id, jQuery(this)[0].id)
    .done(function(){
        update_income_row();
    });
});
// Update Income Row (Frontend)
function update_income_row(){
    let id = jQuery("[name='income_id']").val();
                
    jQuery.ajax({
        type: "GET",
        url: exp_manager_ajax_obj.ajax_url,
        data: {
            action: 'get_single_income',
            nonce: exp_manager_ajax_obj.nonce,
            id: id,
        },
        success: function (response) {

            let income = response.data;

            console.log(income);
              
            let edit_row = jQuery("#incomes_table").find(jQuery('a#'+id)).closest("tr.data");

            // Update source
            jQuery(edit_row).find(".source").text(income.income_source);
            
             // Update amount
             jQuery(edit_row).find(".amount").text(new Intl.NumberFormat().format(income.income_amount));
        
             
            // Update short description
            jQuery(edit_row).find(".short_desc").text(income.income_short_desc);
        
            // Update Payment Type
            jQuery(edit_row).find(".pay_type").text(income.payment_type);

            // Update date
            let d = new Date(income.created_date);
            let datestring =
                d.getDate() +
                " " +
                months[d.getMonth()] +
                ", " +
                d.getFullYear();

            jQuery(edit_row).find(".date").text(datestring);     
        },
        error: function (err) {
            console.log(err);
        },
    });
}

/**
 * ========================================
 * Load More incomes
 * ========================================
 */
 let income_filter_data = {
    action: 'filter_incomes',
    nonce: exp_manager_ajax_obj.nonce,
    load_click: 0
}
jQuery("#load_more").on("click", function(e){
    e.preventDefault();
    income_filter_data.load_click++;

    jQuery(this).text("Loading...");
    jQuery(".load-more .loader").show();

    filter_incomes(income_filter_data)
    .done(function(){
        jQuery("#load_more").text("Load More");
        jQuery(".load-more .loader").hide();
    });
});

/**
 * ========================================
 * Filter incomes on select Sources
 * ========================================
 */
 jQuery("#all_source").on("click", function(e){
    jQuery("#multi_checkboxes input[type='checkbox']").prop('checked', jQuery(e.target).prop('checked'));
   
    income_filter_data.load_click = 0;
    jQuery("[name='search_income']").val('');
    delete income_filter_data.search;

    filter_incomes( income_filter_data );
    jQuery("#clear_filter").show();
});

jQuery("#multi_checkboxes input.source").each(function(i, item){
    jQuery(this).on("click", function(){
        jQuery("#all_source").prop('checked', false);

        income_filter_data.load_click = 0;
    
        jQuery("[name='search_income']").val('');
        delete income_filter_data.search;
    
        filter_incomes( income_filter_data );
        jQuery("#clear_filter").show();
    });
});

/**
 * ========================================
 * Filter incomes on change Year,
 * Month, Custom Date
 * ========================================
 */
jQuery("#filter_by_time, #year, #month, #start_date, #end_date").on("change","", function(e){
    income_filter_data.load_click = 0;
    
    jQuery("[name='search_income']").val('');
    delete income_filter_data.search;

    filter_incomes( income_filter_data );
    jQuery("#clear_filter").show();
});


/**
 * ========================================
 * Search income 
 * ========================================
 */
 jQuery("#search_income").on("keyup", function(e){
    e.preventDefault();
    income_filter_data.search = jQuery("[name='search_income']").val();
    filter_incomes(income_filter_data);
});

// Filter incomes function
function filter_incomes(data){
    let source_inputs = document.querySelectorAll("#multi_checkboxes input:checked");

    data.sources = [];
    
    jQuery.each(source_inputs, function(i, item){
        data.sources.push(jQuery(this).val());
    });

    console.log(data.sources);

    data.filter_type = jQuery("#filter_by_time").val();

    if ( data.filter_type == 'this_month' || data.filter_type == 'last_month' || data.filter_type == 'last_3_months') {
        jQuery(".yearly_filter").css('display','none');
        jQuery(".custom_date_filter").css('display','none');

        delete data.year;
        delete data.month;
        delete data.start_date;
        delete data.end_date;

    } else if ( data.filter_type == 'yearly' ) {
        jQuery(".yearly_filter").css('display','inline-block');
        jQuery(".custom_date_filter").css('display','none');
      
        delete data.start_date;
        delete data.end_date;

        data.year = jQuery("[name='year']").val();
        data.month = jQuery("[name='month']").val();
    
    } else if ( data.filter_type == 'custom_date' ) {
        jQuery(".yearly_filter").css('display','none');
        jQuery(".custom_date_filter").css('display','inline-block');

        delete data.year;
        delete data.month;

        data.start_date = jQuery("#start_date").val();
        data.end_date = jQuery("#end_date").val();
    
    } else {
        jQuery(".custom_date_filter").css('display','none');
        jQuery(".yearly_filter").css('display','none');

        delete data.year;
        delete data.month;
        delete data.start_date;
        delete data.end_date;
    }
   
    return jQuery.ajax({
        type: "GET",
        url: exp_manager_ajax_obj.ajax_url,
        data: data,
        success: function (response) {
            update_data_table(data_table_id, response.data);
        },
        error: function (err) {
            console.log(err);
        },
    });
}


// Clear Filter 
jQuery("#clear_filter").on("click", function(e){
    e.preventDefault();
    clear_filter();
    jQuery(this).hide();
})

// Clear Filter Function
function clear_filter(){
    jQuery("#multi_checkboxes input[type='checkbox']").prop('checked', false);
    jQuery("#multi_checkboxes").hide();

    jQuery("[name='search_income']").val('');
    jQuery("[name='filter_source']").val(0);
    jQuery("#filter_by_time").val('this_month');

    delete income_filter_data.this_month;
    delete income_filter_data.year;
    delete income_filter_data.month;
    delete income_filter_data.start_date;
    delete income_filter_data.end_date;
    delete income_filter_data.search;

    income_filter_data.filter_type = 'this_month';

    filter_incomes( income_filter_data );
}