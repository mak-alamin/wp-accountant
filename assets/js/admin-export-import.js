// Export Data
jQuery("#export_form").on('submit', function(e){
    e.preventDefault();

    let data = jQuery(this).serialize();

    jQuery.ajax({
        type: 'POST',
        url: exp_manager_ajax_obj.ajax_url,
        data: data,
        global: false,
        cache: false,
        dataType: "text",
        success: function(data){

            let filename = JSON.parse(data).filename;

            let blob = new Blob( [data], {
                type: 'application/text'
            });

            let url = window.URL || window.webkitURL;
            let link = url.createObjectURL(blob);

            let a = jQuery("<a />");
            a.attr("download", filename);
            a.attr("href", link);
            jQuery("body").append(a);
            a[0].click();
            jQuery("body").remove(a);
        },
        error: function(err){
            console.log(err);
        }
    });
});

// Check file type when uploading
jQuery("#file_to_import").on("change", function(e){
    let file_ext = getFileExtension(jQuery(this).val());
    if (file_ext != 'json'){
        jQuery("#file_to_import").val(null);
        jQuery("p.message").addClass("error_text");
        jQuery("p.message").text("Please choose a JSON file");
    }
});

// Import Data
jQuery("#import_form").on("submit", function(e){
    e.preventDefault();

    jQuery('[name="import_submit"]').val("Importing...");

    jQuery("p.message").text("Don't close this window before all data imported successfully. Thank you for being patient :)");

    jQuery(".loader").css('display','inline-block');
   
    let form_data = new FormData(jQuery(this)[0]);
        
    form_data.append('action','exp_man_import');
    form_data.append('nonce',exp_manager_ajax_obj.nonce);
    
    jQuery.ajax({
        url: exp_manager_ajax_obj.ajax_url,
        type: 'POST',
        contentType: false,
        processData: false,
        data: form_data,
        success: function(response){

            // console.log(response);

            if ( !response.success ){
                jQuery("p.message").text("Please check the following issues:");

                jQuery.each(response.data, function(key, value){
                    jQuery(".import-area").append("<p class='error_text'>" + value + "</p><br/>");
                });

            } else {      
                jQuery("p.message").addClass("success");
                jQuery("p.message").text(response.data.congrats);

                console.log(response.data.messages);

                if ( ! jQuery.isEmptyObject(response.data.messages) ){
                    jQuery(".import-area").append("<p>Please check the following issues:</p>");

                    jQuery.each(response.data.messages, function(key, value){
                        jQuery(".import-area").append("<p class='error_text'>" + value + "</p><br/>");
                    });
                }
            }

            jQuery(".loader").hide();
            jQuery('[name="import_submit"]').val("Import");
        },
        error: function(err){
            console.log(err);
            jQuery("#file_to_import").val(null);
            jQuery("p.message").addClass("error_text");
            jQuery("p.message").text(err.data);
        }
    });
});