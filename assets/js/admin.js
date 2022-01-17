let data,
    updated_data = [],
    data_html = "",
    data_limit,
    no_data_text = "",
    total_income = 0,
    total_expense = 0,
    action = "",
    url = new URL(window.location.href),
    page = url.searchParams.get("page"),
    expense_cat_page = "wealcoder-expense-manager-expense-categories",
    expense_page = "wealcoder-expense-manager-expenses",
    income_source_page = "wealcoder-expense-manager-income-sources",
    income_page = "wealcoder-expense-manager-income",
    pay_type_page = "wealcoder-expense-manager-payment-types",
    form_id, modal_id, data_table_id;

if (expense_page == page) {
    (form_id = "#expenseForm"),
        (modal_id = "#expense_popup"),
        (data_table_id = "#exepenses_table"),
        data_limit = exp_manager_ajax_obj.expense_limit,
        (no_data_text = exp_manager_ajax_obj.no_expense_text);

} else if (expense_cat_page == page) {
    data_table_id = "#exepense_category_table";
    no_data_text = exp_manager_ajax_obj.no_exp_cat_text;

} else if (income_page == page) {
    (form_id = "#incomeForm"), (modal_id = "#income_popup");
    (data_table_id = "#incomes_table"),
    data_limit = exp_manager_ajax_obj.income_limit,
        (no_data_text = exp_manager_ajax_obj.no_income_text);

} else if (income_source_page == page) {
    data_table_id = "#income_source_table";
    no_data_text = exp_manager_ajax_obj.no_income_source_text;

} else if (pay_type_page == page) {
    data_table_id = "#payment_type_table";
    no_data_text = exp_manager_ajax_obj.no_pay_type_text;
}

// Date Formating
const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
];

// Get File Extension
function getFileExtension(filename){
    const extension = filename.split('.').pop();
    return extension;
}

// Check valid http url
function isValidHttpUrl(string) {
    let url;
    
    try {
      url = new URL(string);
    } catch (_) {
      return false;  
    }
  
    return url.protocol === "http:" || url.protocol === "https:";
}

/**
 * ================================
 * Multi Checkboxes
 * ================================
 */
var checkboxes_expanded = false;
function showCheckboxes() {
   var checkboxes = document.getElementById("multi_checkboxes");
   if ( ! checkboxes_expanded ) {
     jQuery(checkboxes).show();
     checkboxes_expanded = true;
   } else {
    jQuery(checkboxes).hide();
     checkboxes_expanded = false;
   }
}

/**
 * ==========================================
 * Show Popup Form when Add New Button Click
 * ==========================================
 */
 
 jQuery("#add_new_btn").on("click", function (e) {
    e.preventDefault();

    clear_form("#expenseForm");
    jQuery("#expense_popup").fadeIn();

    clear_form("#incomeForm");
    jQuery("#income_popup").fadeIn();
});

let doc_upload_button = jQuery("#doc_upload_button");
let doc_remove_button = jQuery("#doc_remove_button");

// Upload Document
jQuery("#uploaded_file_url").on("keyup", function(e){
    if ( '' == jQuery(this).val() ){
        jQuery(doc_upload_button).text("Upload Document");
        jQuery(doc_remove_button).hide();
    } else {
        jQuery(doc_upload_button).text("Replace Document");
        jQuery(doc_remove_button).show();
    }
});

// Remove Document
jQuery(doc_remove_button).on("click",function(e){
    e.preventDefault();
    jQuery(this).siblings('#uploaded_file_url').val('');
    jQuery(this).siblings(".document_err").text("");
    jQuery(doc_upload_button).text("Upload Document");
    jQuery(this).hide();
});


// Save and Close
jQuery(document).on("click", "#save_and_close", function (e) {
    e.preventDefault();
    save_form_data(form_id, modal_id, data_table_id, jQuery(this)[0].id);
});

// Save and Add More
jQuery(document).on("click", "#save_and_add", function (e) {
    e.preventDefault();
    save_form_data(form_id, modal_id, data_table_id, jQuery(this)[0].id);
});

// Toggle Category ,Source, Payment type form
jQuery(".exp_man_row a.toggle_button").on("click",function(e){
    e.preventDefault();
    
    if ( '+' == e.target.innerText ){
        jQuery(this).text("-");
        jQuery(this).closest("tr").find(".cat_source_paytype_form").slideDown(300);

        jQuery(this).closest("tr").find(".cat_source_paytype_form input[type='text']").focus();
    } else {
        jQuery(this).text("+");
        jQuery(this).closest("tr").find(".cat_source_paytype_form").slideUp(300);
    }
});

/**
 * ==================================
 * Close Popup Form
 * ==================================
 */
jQuery(".close").on("click", function () {   
    jQuery(".submit_btns .message").text("");
    jQuery(".submit_btns .error_text").text("");

    jQuery("#expense_popup").fadeOut();
    jQuery("#income_popup").fadeOut();
    jQuery("#expense_view").fadeOut();
    jQuery("#income_view").fadeOut();
});



// Clear Form Data
function clear_form(form_id) {
    jQuery(form_id).each(function (i, input) {
        input.reset();
    });

    jQuery("[name='expense_id']").val(0);
    jQuery("[name='income_id']").val(0);

    jQuery(".submit_btns .message").text("");

    jQuery(".error_text").text("");
    jQuery(".error_text").hide();
    
    jQuery("#doc_upload_button").text("Upload Document");

    jQuery("#save_and_add").show();
    jQuery("#save_and_close").show();

    jQuery("#update_expense").hide();
    jQuery("#update_income").hide();
}

// Clear Errors
function exp_man_clear_errors(){
    jQuery(".error_text").text("");
    jQuery(".error_text").hide();
    jQuery(".submit_btns .message").text("");
}

/**
 * ============================================================
 * Insert / Update Form Data Function
 *
 * @params form_id, modal_id, data_table_id, btn_id, edit_btn_id
 * ============================================================
 */
function save_form_data(form_id, modal_id, data_table_id, btn_id) {
    
    exp_man_clear_errors();

    let form_data = new FormData(jQuery(form_id)[0]);

    for (let [key, value] of form_data.entries()) { 
        if ( 'uploaded_file_url' == key && '' != value ){
            if ( !isValidHttpUrl(value) ){
                jQuery(".error_text").show();
                jQuery(".document_err").text("Please provide a valid document URL.");
                return;
            }

            let file_type = getFileExtension(value);
            
            if ( 'jpg' == file_type || 'png' == file_type ||'pdf' == file_type){
                // Do nothing
            
            } else {
                jQuery(".document_err").text("Document should be a valid jpg, png or pdf format file.");
                return;
            }
        }
    }
   
    jQuery(".loader").show();
    
    return jQuery.ajax({
        type: "POST",
        contentType: false,
        processData: false,
        url: exp_manager_ajax_obj.ajax_url,
        data: form_data,
        success: function (response) {
            jQuery(".loader").hide();

            console.log(response);
            
            if ( ! response.success ){
                jQuery(".error_text").show();

                if ("paid_for_err" in response.data) {
                    jQuery(".paid_for_err").text(response.data.paid_for_err);
                }
    
                if ("amount_err" in response.data) {
                    jQuery(".amount_err").text(response.data.amount_err);
                }

                if ( "message" in response.data ){
                    jQuery(".submit_btns .error_text").show();
                    jQuery(".submit_btns .error_text").text(response.data.message);
                }

                return;
            }

            jQuery(".submit_btns .message").addClass("success");

            if (btn_id == "update_expense" || btn_id == "update_income") {
                // It's an Update
                jQuery(".submit_btns .message").text("Updated successfully!");
            } else {
                // It's an Insert
                jQuery(form_id).each(function () {
                    this.reset();
                });
                
                jQuery(".submit_btns .message").text("Saved successfully!");
                update_data_table(data_table_id, response.data);
            }

            if ( btn_id == "save_and_close" ) {
                jQuery(modal_id).fadeOut();
                setTimeout(clear_form(form_id), 500);
            }
        },
        error: function (err) {
            console.log(err);
            jQuery(".submit_btns .error_text").show();
            jQuery(".submit_btns .error_text").text("Something went wrong!");
        },
    });
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
/**
 * ==================================
 * Update Select Box Function
 * ==================================
 */
function update_select_box(box_type, response){
    if ( response.data.length == 0 ){
        return;
    }

    let options = '';
    options += '<option value="0" disabled selected>Select a '+ capitalizeFirstLetter(box_type.replace('_', ' '))  +'</option>';
    
    Array.prototype.forEach.call(response.data, function(data){
        switch (box_type) {
            case 'category':
                select_box = 'purpose';
                options += '<option value="' + data.id + '"> '+ data.category_name +' </option>';
                break;
        
            case 'source':
                select_box = 'income_source';
                options += '<option value="' + data.id + '"> '+ data.source_name +' </option>';
                break;
        
            default:
                select_box = 'payment_type';
                options += '<option value="' + data.id + '"> '+ data.payment_type +' </option>';
                break;
        }
    });  

    jQuery("#"+select_box).html(options);
}


/**
 * ==================================
 * Update Data Table Function
 * ==================================
 */
function update_data_table(data_table_id, response) {
    total_expense = 0;
    total_income = 0;

    console.log(response);

    if(response.data.length == undefined){
        updated_data = Object.values(response.data);
    } else {
        updated_data = response.data;
    }

    if( response.data.length == 0){
        data_html +=  '<tr class="data"><td>';
        data_html += no_data_text;
        data_html += '</td></tr>';
    } else {
        generate_data_table_rows(updated_data);
    }

    if ( response.total_rows <= Number(data_limit) ) {
        jQuery(".load-more").hide();
    } else {
        jQuery(".load-more").show();
    }

    if ( response.end_of_data != undefined && response.end_of_data ) {
        jQuery(".load-more").hide();
    } else if(response.end_of_data != undefined && !response.end_of_data) {
        jQuery(".load-more").show();
    }
    

    if (page == expense_page) {
        jQuery("#total_expense").text(new Intl.NumberFormat().format(total_expense));
    }
    if (page == income_page) {
        jQuery("#total_income").text(new Intl.NumberFormat().format(total_income));
    }

    jQuery("tr.data").remove();
    jQuery(data_table_id).append(data_html);
    data_html = "";
}

function generate_data_table_rows(updated_data){

    updated_data = updated_data.filter(function(data){
       return data.category_slug != "uncategorized";
    });

    Array.prototype.forEach.call(updated_data, (data) => {
        data_html += "<tr class='data'>";

        // 1st column
        data_html += "<td>";

        // 1st Column For Expense Category table
        if ( page == expense_cat_page ) {
            data_html += data.category_name;

            data_html += '<div class="row-actions">';
            data_html +=
                '<span class="edit"><a href="" data-id="' +
                data.id +
                '" data-name="' +
                data.category_name +
                '">Edit</a> | </span>';
        }

        // 1st Column For Income Source table
        if ( page == income_source_page ){
            data_html += data.source_name;

            data_html += '<div class="row-actions">';
            data_html +=
                '<span class="edit"><a href="" data-id="' +
                data.id +
                '" data-name="' +
                data.source_name +
                '">Edit</a> | </span>';
        }

        // 1st Column For Payment Type table
        if( page == pay_type_page){
            data_html += data.payment_type;

            data_html += '<div class="row-actions">';
            data_html +=
                '<span class="edit"><a href="" data-id="' +
                data.id +
                '" data-name="' +
                data.payment_type +
                '">Edit</a> | </span>';
        }

        // 1st Column For Expenses table
        if (page == expense_page) {
            data_html+= '<input type="checkbox" name="select_'+ data.id +'" id="select_'+ data.id +'" value="1">';

            data_html += "<span class='paid_to'>";
            data_html += data.expense_paid_to;
            data_html += "</span>";
 
            data_html += '<div class="row-actions">';
            data_html += '<span class="view"><a href="" data-id="' +
            data.id +
            '">View</a> | </span>';
            data_html +=
                '<span class="edit"><a href="" data-id="' +
                data.id +
                '">Edit</a> | </span>';

            total_expense += parseFloat(data.expense_amount);
        }

        // 1st Column for Incomes table
        if (page == income_page) {
            data_html+= '<input type="checkbox" name="select_'+ data.id +'" id="select_'+ data.id +'" value="1">';

            data_html += "<span class='source'>";
            data_html += data.income_source;

            data_html += '</span><div class="row-actions">';
            data_html += '<span class="view"><a href="" data-id="' +
            data.id +
            '">View</a> | </span>';
            data_html +=
                '<span class="edit"><a href="" data-id="' +
                data.id  + '">Edit</a> | </span>';
        }

        data_html +=
            '<span class="delete"><a href="#" id="' +
            data.id +
            '" class="delete_btn">Delete</a> | </span>';
        data_html += "</div> </td>";
        // 1st Column ends

        // Other columns For Expense Page
        if (page == expense_page) {
            // 2nd Column
            data_html += "<td>";
            data_html +=
                exp_manager_ajax_obj.currency_symbol +
                " " +
                '<span class="amount">';
            data_html += new Intl.NumberFormat().format(data.expense_amount);
            data_html += "</span>";
            data_html += "</td>";

            // 3rd column
            data_html += "<td class='category'>";
            data_html += data.expense_category;
            data_html += "</td>";

            // 4th column
            data_html += "<td class='short_desc'>";
            data_html += data.expense_short_desc;
            data_html += "</td>";
            
            // 5th column
            data_html += "<td class='add_by'>";
            data_html += data.expense_add_by;
            data_html += "</td>";

            // 6th column
            let d = new Date(data.created_date);
            let datestring =
                d.getDate() +
                " " +
                months[d.getMonth()] +
                ", " +
                d.getFullYear();

            data_html += "<td class='date'>";
            data_html += datestring;
            data_html += "</td>";
        }

         // Other columns For Income Page
         if (page == income_page) {
            // 2nd column
            data_html += "<td>" +
            exp_manager_ajax_obj.currency_symbol +
            " " +
            '<span class="amount">';
            data_html += new Intl.NumberFormat().format(data.income_amount);
            data_html += "</span></td>";
            total_income += parseFloat(data.income_amount);
            
            // 3rd column
            data_html += "<td class='short_desc'>";
            data_html += data.income_short_desc;
            data_html += "</td>";
             

            // 4rth column
            data_html += "<td class='pay_type'>";
            data_html += data.payment_type;
            data_html += "</td>";
            
            // 5th Column
            let d = new Date(data.created_date);
            let datestring = d.getDate() + " " + months[d.getMonth()] + ", " + d.getFullYear();
            
            data_html += "<td class='date'>";
            data_html += datestring;
            data_html += "</td>";
        }

        data_html += "</tr>";
    });
}


/**
 * =========================================
 *  Delete Data
 * =========================================
 */
jQuery(document).on("click", "a.delete_btn", function (e) {
    e.preventDefault();

    if (!confirm("Are you sure you want to delete?")) {
        return;
    }

    delete_single_data(this.id);
});

function delete_single_data(id, delete_delay=300){
    let start_time = new Date();

    if (expense_cat_page == page) {
        action = "delete_expense_cat";
        
    } else if (expense_page == page) {
        let amount;
        action = "delete_expense";

    } else if (income_source_page == page) {
        action = "delete_income_source";

    } else if (income_page == page) {
        let amount;
        action = "delete_income";

    } else if ( pay_type_page == page ){
        action = "delete_payment_type";
    }
 
    jQuery.ajax({
        type: "POST",
        url: exp_manager_ajax_obj.ajax_url,
        data: {
            action: action,
            nonce: exp_manager_ajax_obj.nonce,
            id: id,
        },
        success: function (response) {
            console.log(response);
            
            let end_time = new Date();

            let time_elapsed = (end_time - start_time);

            delete_delay = Math.max(time_elapsed, delete_delay);

            jQuery("#" + id).closest("tr").css("background-color", "#b32d2e").hide(delete_delay, function(){
                
                if (page == expense_page) {
                    total_expense = Number(jQuery("#total_expense").text().replace(/\,/g,''));

                    amount = jQuery("#" + id).closest("tr.data").find(".amount").text();

                    total_expense -= Number( amount.replace(/\,/g,'') );
                  
                    jQuery("#total_expense").text(new Intl.NumberFormat().format(total_expense));
                }
                
                if ( page == income_page ) {
                    total_income = Number(jQuery("#total_income").text().replace(/\,/g,''));
                   
                    amount = jQuery("#" + id).closest("tr.data").find(".amount").text();

                    total_income -= Number( amount.replace(/\,/g,'') );

                    jQuery("#total_income").text(new Intl.NumberFormat().format(total_income));
                }

                jQuery(this).remove();

                jQuery("#" + id).closest("tr").css("background-color", "#b32d2e");

                if( response.data.total_rows == 0 ){  
                    jQuery("tr.data").remove();

                    data_html +=  '<tr class="data"><td>';
                    data_html += no_data_text;
                    data_html += '</td></tr>';
    
                    jQuery(data_table_id).append(data_html);
    
                    data_html = '';
                }
            });
        },
        error: function (err) {
            console.log(err);
        },
    });
}

/**
 * Prevent search submit
 */
jQuery("#search_form").on("submit", function(e){
    e.preventDefault();
});


/**
 * =========================================
 *  Bulk Actions
 * =========================================
 */
jQuery("#exepenses_table, #incomes_table").on("click", "#select_all", function(e){
        jQuery("tr.data input[type='checkbox']").prop('checked', jQuery(e.target).prop('checked'));
});


jQuery("#bulk_action_form").on("submit", function(e){
    e.preventDefault();

    let bulk_action = jQuery("#bulk_action").val();

    if ( bulk_action == -1 ){
        return;
    }
    
    if ( bulk_action == "bulk_delete" ){
        bulk_delete_data();

        jQuery("#select_all").removeAttr("checked");
    }
});

function bulk_delete_data(){
    if ( !confirm("You are about to permanently delete the selected items. Are you sure?") ) {
        return;
    }

    let items = jQuery("tr.data input[type='checkbox']:checked");

    jQuery(items).each(function(i, item){
        
        let id = item.id.split("select_")[1];

        delete_single_data(id);
    });
}