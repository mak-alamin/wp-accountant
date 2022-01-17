/**
 * =======================================
 * PDF Download
 * =======================================
 */
 jQuery(document).ready(function(){ 
    jQuery("#download_pdf").on("click", function(e){
        e.preventDefault();

        let table_rows = jQuery("#exepenses_table tr.data");

        let data = [];
        let single_data = [];

        jQuery.each(table_rows, function(i, item){
            single_data = [
                jQuery(item).find(".paid_to").text().trim(),
                jQuery(item).find(".amount").text().trim(),
                jQuery(item).find(".category").text().trim(),
                jQuery(item).find(".short_desc").text().trim(),
                jQuery(item).find(".add_by").text().trim(),
                jQuery(item).find(".date").text().trim(),
            ]

            data.push(single_data);
        });

        let total_amount = jQuery("#total_expense").text();
        data.push(['Total Expense: ', total_amount]);

        let pdf = new jsPDF();

        pdf.autoTable({
            head: [['Paid For', 'Amount', 'Category', 'Description', 'Added By', 'Date']],
            body: data,
            didDrawPage: function (res){
                // Header
                pdf.setFontSize(18);
                pdf.setTextColor(40);
                pdf.setFontStyle('normal');
                //pdf.addImage(headerImgData, 'JPEG', res.settings.margin.left, 20, 50, 50);
                pdf.text("Expenses", res.settings.margin.left, 20);
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

        pdf.save('exepenses.pdf');
    });
 });


/**
 * ========================================
 * View Expense 
 * ========================================
 */

jQuery("#exepenses_table").on("click", ".view a", function(e){
    e.preventDefault();
    
    let file_extension = '', file_url = '';
    let id = jQuery(this).attr("data-id");

    jQuery.ajax({
        type: 'GET',
        url: exp_manager_ajax_obj.ajax_url,
        data: {
            action: 'get_single_expense',
            nonce: exp_manager_ajax_obj.nonce,
            id: id,
        }, 
        success: function(response){
            let expense = response.data;
            let d = new Date(expense.created_date);
            let datestring =
                d.getDate() +
                " " +
                months[d.getMonth()] +
                ", " +
                d.getFullYear();

            jQuery(".view_purpose").text(expense.expense_category);
            jQuery(".view_amount").text(expense.expense_amount);
            jQuery(".view_short_desc").text(expense.expense_short_desc);
            jQuery(".view_paid_to").text(expense.expense_paid_to);        

            
            file_url = expense.expense_doc_name;
            file_extension = getFileExtension(file_url);
            
            // console.log(file_extension);
            
            if ( 'pdf' == file_extension ) {
                jQuery(".view_doc img").hide();
                jQuery(".view_doc span").text("");

                jQuery(".download_pdf").show();
                jQuery(".download_pdf").attr('href', file_url );
            } else if ( 'jpg' == file_extension || 'png' == file_extension) {
                jQuery(".download_pdf").hide();
                jQuery(".view_doc span").text("");

                jQuery(".view_doc img").show();
                jQuery(".view_doc img").attr( "src", file_url );              
            } else {
                jQuery(".download_pdf").hide();
                jQuery(".view_doc img").hide();
                jQuery(".view_doc span").text("N/A");
            }

            jQuery(".view_add_by").text(expense.expense_add_by);
            jQuery(".view_date").text(datestring);
        
            jQuery("#expense_view").fadeIn();

        },
        error: function(err){
            console.log(err);
        }
    });
});


/**
 * ========================================
 * Update Expense 
 * ========================================
 */
// Open Modal To Update Expenses
jQuery("#exepenses_table").on("click", ".edit a", function(e){
    e.preventDefault();
    
    let id = jQuery(this).attr("data-id");

    jQuery.ajax({
        type: 'GET',
        url: exp_manager_ajax_obj.ajax_url,
        data: {
            action: 'get_single_expense',
            nonce: exp_manager_ajax_obj.nonce,
            id: id,
        }, 
        success: function(response){
            let expense = response.data;

            let date = expense.created_date.substring(0, 10);
          
            jQuery("[name='expense_id']").val(id);
            jQuery("[name='expense_date']").val(date);
            jQuery("[name='amount']").val(expense.expense_amount);
            jQuery("[name='short_desc']").val(expense.expense_short_desc);
            jQuery("[name='purpose']").val(expense.expense_cat_id);
            jQuery("[name='paid_to']").val(expense.expense_paid_to);

            if ( '' == expense.expense_doc_name ){
                doc_upload_button.text('Upload Document');
                doc_remove_button.hide();
            } else {
                doc_upload_button.text('Replace Document');
                doc_remove_button.show();
                jQuery("[name='uploaded_file_url']").val(expense.expense_doc_name);
            }
           
            jQuery("#save_and_add").hide();
            jQuery("#save_and_close").hide();
            jQuery("#update_expense").show();
        
            jQuery("#expense_popup").fadeIn();

        },
        error: function(err){
            console.log(err);
        }
    });
});

// Update Expense on click "Update" button
jQuery("#update_expense").on("click", function(e){
    e.preventDefault();
    
    save_form_data(form_id, modal_id, data_table_id, jQuery(this)[0].id)
    .done(function(){
        update_expense_row();
    });
});

// Update Expense Row
function update_expense_row(){
    let id = jQuery("[name='expense_id']").val();
    
    jQuery.ajax({
        type: "GET",
        url: exp_manager_ajax_obj.ajax_url,
        data: {
            action: 'get_single_expense',
            nonce: exp_manager_ajax_obj.nonce,
            id: id,
        },
        success: function(response){
            let expense = response.data;   
            let edit_row = jQuery("#exepenses_table").find(jQuery('a#'+id)).closest("tr.data");
            
            // Update amount
            jQuery(edit_row).find(".amount").text(new Intl.NumberFormat().format(expense.expense_amount));
            
            // Update category
            jQuery(edit_row).find(".category").text(expense.expense_category);
            
            // Update Paid To
            jQuery(edit_row).find(".paid_to").text(expense.expense_paid_to);
            
            // Update short description
            jQuery(edit_row).find(".short_desc").text(expense.expense_short_desc);

            // Update date
            let date = new Date(expense.created_date);
            let datestring =
                date.getDate() +
                " " +
                months[date.getMonth()] +
                ", " +
                date.getFullYear();

            jQuery(edit_row).find(".date").text(datestring);
        }, 
        error: function(err){
            console.log(err);
        }
    });
}

/**
 * ========================================
 * Load More Expenses
 * ========================================
 */
 let expense_filter_data = {
    action: 'filter_expenses',
    nonce: exp_manager_ajax_obj.nonce,
    load_click: 0
}
jQuery("#load_more").on("click", function(e){
    e.preventDefault();
    expense_filter_data.load_click++;

    jQuery(this).text("Loading...");
    jQuery(".load-more .loader").show();

    filter_expenses(expense_filter_data)
    .done(function(){
        jQuery("#load_more").text("Load More");
        jQuery(".load-more .loader").hide();
    });
});

/**
 * ========================================
 * Filter Expenses on select Categories
 * ========================================
 */
jQuery("#all_category").on("click", function(e){
    jQuery("#multi_checkboxes input[type='checkbox']").prop('checked', jQuery(e.target).prop('checked'));
   
    expense_filter_data.load_click = 0;
    jQuery("[name='search_expense']").val('');
    delete expense_filter_data.search;

    filter_expenses( expense_filter_data );
    jQuery("#clear_filter").show();
});

jQuery("#multi_checkboxes input.category").each(function(i, item){
    jQuery(this).on("click", function(){
        jQuery("#all_category").prop('checked', false);

        expense_filter_data.load_click = 0;
        jQuery("[name='search_expense']").val('');
        delete expense_filter_data.search;
    
        filter_expenses( expense_filter_data );
        jQuery("#clear_filter").show();
    });
});

/**
 * =======================================
 * Filter Expenses on change Year, Month
 * =======================================
 */
jQuery("#filter_expense, #year, #month, #start_date, #end_date").on("change", function(e){
    expense_filter_data.load_click = 0;
    jQuery("[name='search_expense']").val('');
    delete expense_filter_data.search;

    filter_expenses( expense_filter_data );
    jQuery("#clear_filter").show();
});

/**
 * ========================================
 * Search expense
 * ========================================
 */
 jQuery("#search_expense").on("keyup", function(e){
    e.preventDefault();
    expense_filter_data.search = jQuery("[name='search_expense']").val();
    filter_expenses( expense_filter_data );
});


// Filter expenses function
function filter_expenses(data){
    let category_inputs = document.querySelectorAll("#multi_checkboxes input:checked");

    data.categories = [];
    
    jQuery.each(category_inputs, function(i, item){
        data.categories.push(jQuery(this).val());
    });

    data.filter_type = jQuery("#filter_expense").val();
 
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
        success: function ( response ) { 
            console.log(response);
            update_data_table( data_table_id, response.data );
        },
        error: function ( err ) {
            console.log( err );
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

    jQuery("[name='search_expense']").val('');
    jQuery("[name='category']").val(0);
    jQuery("#filter_expense").val('this_month');

    delete expense_filter_data.this_month;
    delete expense_filter_data.year;
    delete expense_filter_data.month;
    delete expense_filter_data.start_date;
    delete expense_filter_data.end_date;
    delete expense_filter_data.search;

    expense_filter_data.filter_type = 'this_month';

    filter_expenses( expense_filter_data );
}