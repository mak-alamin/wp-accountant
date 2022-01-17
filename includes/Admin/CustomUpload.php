<?php

namespace ExpenseManager\Admin;

/**
 * Custom Upload Folder Class
 */
class CustomUpload{
    public function __construct()
    {
        add_action('wp_ajax_reset_upload_dir', [$this, 'reset_upload_dir']);
       
        add_action('wp_ajax_set_custom_upload_dir', [$this, 'set_custom_upload_dir']);
    }

    public static function reset_upload_dir() {
        update_option('upload_path',null);
        update_option('upload_url_path',null);
        update_option('uploads_use_yearmonth_folders', true);
        wp_die();
    }

    public function set_custom_upload_dir(){
        update_option('upload_path',WP_CONTENT_DIR.'/uploads/wp_expense_manager_uploads');
        update_option('upload_url_path', WP_CONTENT_URL . '/uploads/wp_expense_manager_uploads');
        update_option('uploads_use_yearmonth_folders', false);
    }
}