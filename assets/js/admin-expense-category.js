/**
 * =============================================
 * Add New Expense Category
 * =============================================
 */
 jQuery("#submit_expense_category").on("click", function (e) {
    e.preventDefault();

    let data = {};

    let name = jQuery("#newExpenseCatForm input[name='name']").val();
    let cat_id = jQuery("#newExpenseCatForm input[name='cat_id']").val();

    data.name = name;
    data.cat_id = cat_id;
    data.action = 'add_new_expense_cat';
    data.nonce = exp_manager_ajax_obj.nonce;

    jQuery.ajax({
        type: "POST",
        url: exp_manager_ajax_obj.ajax_url,
        data: data,
        success: function (response) {

            console.log(response);

            if ( ! response.success ){
                jQuery("span.category_message").removeClass("success");
                jQuery("span.category_message").addClass("error_text");
                jQuery("span.category_message").text(response.data);
                return;
            
            } else {
                jQuery("span.category_message").removeClass("error_text");
                jQuery("span.category_message").addClass("success");

                if ( 0 == jQuery("#cat_id").val()){
                    jQuery("span.category_message").text("Category Added.");
                } else {
                    jQuery("span.category_message").text("Category Updated.");
                }
            }

            jQuery("#newExpenseCatForm input").each(function (i, item) {
                jQuery(this).val('');
            }); 
            
            if ( expense_cat_page == page ){
                update_data_table( "#exepense_category_table",response );
            } else {
                update_select_box( "category", response );
            }

            jQuery("#cat_id").val(0);
            jQuery("#submit_expense_category").val("Add Category");
        },
        error: function (err) {
            console.log(err);
        },
    });
});


/**
 * =========================================
 * Update Expense Category
 * =========================================
 */
jQuery("#exepense_category_table").on("click", ".edit a",function (e) {
    e.preventDefault();

    jQuery("span.category_message").text("");

    let id = jQuery(this).attr('data-id');
    let name = jQuery(this).attr('data-name');
    
    jQuery("#name").val(name);
    jQuery("#cat_id").val(id);
    jQuery("#submit_expense_category").val("Update Category");
});