;(function($){
    $(document).ready(function(){

        var button = $("#doc_upload_button");
        var doc_remove_button = jQuery("#doc_remove_button");
          
        button.on("click", function(e){
            e.preventDefault();

            let data = {
                action: 'set_custom_upload_dir'
            };


            jQuery.post(exp_manager_ajax_obj.ajax_url, data, function(response) {
               
                console.log('Set up custom upload folder: ' + response);
           
            }).done(function(){

                var tm_wp_media = wp.media({
                
                    title: 'Select Document',
                    
                    button: {
                        text: 'Select Document'
                    },
    
                    multiple: false
                });
    
                tm_wp_media.open();
    
                tm_wp_media.on( "select", function(){

                    var attachment = tm_wp_media.state().get('selection').first().toJSON();
    
                    button.siblings('#uploaded_file_url').val(attachment.url);
                    
                    button.text('Replace Document');
                    doc_remove_button.show();
    
                }).on('close',function() {

                    let data = {
                        action: 'reset_upload_dir'
                    };

                    jQuery.post(exp_manager_ajax_obj.ajax_url, data, function(response) {
                        console.log('Set up default upload folder: ' + response);
                    });
                    
                });
            });  
        });
    })
})(jQuery);