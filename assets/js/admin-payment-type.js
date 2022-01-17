/**
 * =============================================
 * Add New Payment Type
 * =============================================
 */
 jQuery("#submit_payment_type").on("click", function (e) {
    e.preventDefault();
    
    let data = {};

    let name = jQuery("#newPaymentTypeForm input[name='pay_type_name']").val();
    let pay_type_id = jQuery("#newPaymentTypeForm input[name='pay_type_id']").val();

    console.log(data);

    data.name = name;
    data.type_id = pay_type_id;
    data.action = 'add_new_payment_type';
    data.nonce = exp_manager_ajax_obj.nonce;

    jQuery.ajax({
        type: "POST",
        url: exp_manager_ajax_obj.ajax_url,
        data: data,
        success: function (response) {
            console.log(response);
            if ( ! response.success ){
                jQuery("span.pay_type_message").removeClass("success");
                jQuery("span.pay_type_message").addClass("error_text");
                jQuery("span.pay_type_message").text(response.data);
                return;
            } else {
                jQuery("span.pay_type_message").removeClass("error_text");
                jQuery("span.pay_type_message").addClass("success");

                if ( 0 == jQuery("#pay_type_id").val()){
                    jQuery("span.pay_type_message").text("Payment Type Added.");
                } else {
                    jQuery("span.pay_type_message").text("Payment Type Updated.");
                }
            }
            
            jQuery("#newPaymentTypeForm input").each(function (i, item) {
                jQuery(this).val('');
            }); 
            
            if ( pay_type_page == page ){
                update_data_table( "#payment_type_table",response );
            } else {
                update_select_box( "payment_type", response );
            }

            jQuery("#type_id").val(0);
            jQuery("#submit_payment_type").val("Add Payment Type");
        },
        error: function (err) {
            console.log(err);
        },
    });
});

/**
 * =========================================
 * Update Payment Type
 * =========================================
 */
 jQuery("#payment_type_table").on("click", ".edit a",function (e) {
    e.preventDefault();
    let id = jQuery(this).data('id');
    let name = jQuery(this).data('name');

    jQuery("#pay_type_name").val(name);
    jQuery("#pay_type_id").val(id);
    jQuery("#submit_payment_type").val("Update Source");
});