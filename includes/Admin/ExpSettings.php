<?php

namespace ExpenseManager\Admin;

/**
 * Class ExpSettings
 */

class ExpSettings
{
    public $page;

    public $expense_limit;
    public $income_limit;
    public $load_more;

    private $currency_symbols = array(
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'BDT' => '৳',
    );

    public function __construct()
    {
        $this->page = "wealcoder-expense-manager-settings";
        
        $this->expense_limit = (!empty(get_option('exp_man_expense_limit')))? get_option('exp_man_expense_limit') : 100;
        
        $this->income_limit = (!empty(get_option('exp_man_income_limit')))? get_option('exp_man_income_limit') : 100;

        $this->load_more = (!empty(get_option('exp_man_load_more')))? get_option('exp_man_load_more') : 10;

        add_action('admin_init', array($this, 'exp_man_admin_settings_init'));
    }

    public function render_page()
    {
        if ('wealcoder-expense-manager-settings' != $_GET['page']) {
            return;
        }

        $template = __DIR__ . '/views/exp_settings/exp_settings.php';

        if (file_exists($template)) {
            include_once $template;
        }
    }

    public function exp_man_admin_settings_init()
    {
        /**
         * ==================================
         * General Settings Section
         * ==================================
         */
        add_settings_section('general_settings_section', 'General Settings', array($this, 'general_settings_section_cb'), $this->page);
       
        
        // ================== Add Fields ==================
        // Add "Select Currency" Field
        add_settings_field('select_currency', "Select Currency", array($this, "select_currency_callback"), $this->page, 'general_settings_section', array('label_for' => 'select_currency'));
        
        // Add "Expense Limit" Field
        add_settings_field('exp_man_expense_limit', "Expense Page shows at most", array($this, "expense_limit_callback"), $this->page, 'general_settings_section', array('label_for' => 'exp_man_expense_limit'));
       
        // Add "Income Limit" Field
        add_settings_field('exp_man_income_limit', "Income Page shows at most", array($this, "income_limit_callback"), $this->page, 'general_settings_section', array('label_for' => 'exp_man_income_limit'));
        
        // Add "load more" Field
        add_settings_field('exp_man_load_more', "Load More button loads", array($this, "load_more_item_callback"), $this->page, 'general_settings_section', array('label_for' => 'exp_man_load_more'));
      
        
        // ================== Register Fields ==================
        // Register "Select Currency" Field
        register_setting("exp_man_settings_group", "select_currency");
        
        // Register "Expense Limit" Field
        register_setting("exp_man_settings_group", "exp_man_expense_limit");
        
        // Register "Income Limit" Field
        register_setting("exp_man_settings_group", "exp_man_income_limit");
       
        // Register "Load More" Field
        register_setting("exp_man_settings_group", "exp_man_load_more");
        

        /**
         * =====================================
         * Company Settings Sections
         * =====================================
         */
        add_settings_section('company_settings_section', 'Company Information', array($this, 'company_settings_section_cb'), $this->page);
        

        // ================== Add Fields ==================
        // Add "Company Name" Field 
        add_settings_field('exp_man_company_name', "Company Name", array($this, "company_name_callback"), $this->page, 'company_settings_section', array('label_for'=> 'exp_man_company_name'));
        
        // Add "Company website" Field 
        add_settings_field('exp_man_company_website', "Company Website", array($this, "company_website_callback"), $this->page, 'company_settings_section', array('label_for' => 'exp_man_company_website'));
       
        // Add "Company Facebook" Field 
        add_settings_field('exp_man_company_facebook', "Facebook", array($this, "company_facebook_callback"), $this->page, 'company_settings_section', array('label_for' => 'exp_man_company_facebook'));
        
        // Add "Company linkedin" Field 
        add_settings_field('exp_man_company_linkedin', "LinkedIn", array($this, "company_linkedin_callback"), $this->page, 'company_settings_section', array('label_for' => 'exp_man_company_linkedin'));
        
        // Add "Company twitter" Field 
        add_settings_field('exp_man_company_twitter', "Twitter", array($this, "company_twitter_callback"), $this->page, 'company_settings_section', array('label_for' => 'exp_man_company_twitter'));


       
        // ================== Register Fields ==================
        // Register "Company Name" Field
        register_setting("exp_man_settings_group", "exp_man_company_name");

        // Register "Company Website" Field
        register_setting("exp_man_settings_group", "exp_man_company_website");    
          
        // Register "Company Facebook" Field
        register_setting("exp_man_settings_group", "exp_man_company_facebook");    
       
        // Register "Company linkedin" Field
        register_setting("exp_man_settings_group", "exp_man_company_linkedin");  
           
        // Register "Company twitter" Field
        register_setting("exp_man_settings_group", "exp_man_company_twitter");  
    }

    // "Load More" Field callback
    public function load_more_item_callback()
    {
        $load_more = (!empty(get_option('exp_man_load_more')))? get_option('exp_man_load_more') : 10;

        echo "<input type='number' name='exp_man_load_more' id='exp_man_load_more' value='". esc_attr($load_more) ."' /><span> items</span>";
    }
   
    // "Income Limit" Field callback
    public function income_limit_callback()
    {
        $income_limit = (!empty(get_option('exp_man_income_limit')))? get_option('exp_man_income_limit') : 100;

        echo "<input type='number' name='exp_man_income_limit' id='exp_man_income_limit' value='". esc_attr($income_limit) ."' /><span> items</span>";
    }
    
    // "Expense Limit" Field callback
    public function expense_limit_callback()
    {
        $expense_limit = (!empty(get_option('exp_man_expense_limit')))? get_option('exp_man_expense_limit') : 100;

        echo "<input type='number' name='exp_man_expense_limit' id='exp_man_expense_limit' value='". esc_attr($expense_limit) ."' /><span> items</span>";
    }
    
    // "Company twitter" Field callback
    public function company_twitter_callback()
    {
        $company_twitter = (!empty(get_option('exp_man_company_twitter')))? get_option('exp_man_company_twitter') : '';

        echo "<input type='text' name='exp_man_company_twitter' id='exp_man_company_twitter' value='". esc_attr($company_twitter) ."' />";
    }

    // "Company linkedin" Field callback
    public function company_linkedin_callback()
    {
        $company_linkedin = (!empty(get_option('exp_man_company_linkedin')))? get_option('exp_man_company_linkedin') : '';

        echo "<input type='text' name='exp_man_company_linkedin' id='exp_man_company_linkedin' value='". esc_attr($company_linkedin) ."' />";
    }

    // "Company facebook" Field callback
    public function company_facebook_callback()
    {
        $company_fb = (!empty(get_option('exp_man_company_facebook')))? get_option('exp_man_company_facebook') : '';

        echo "<input type='text' name='exp_man_company_facebook' id='exp_man_company_facebook' value='". esc_attr($company_fb) ."' />";
    }
   
    // "Company website" Field callback
    public function company_website_callback()
    {
        $company_website = (!empty(get_option('exp_man_company_website')))? get_option('exp_man_company_website') : '';

        echo "<input type='text' name='exp_man_company_website' id='exp_man_company_website' value='". esc_attr($company_website) ."' />";
    }

    // "Company Name" Field callback
    public function company_name_callback()
    {
        $company_name = (!empty(get_option('exp_man_company_name')))? get_option('exp_man_company_name') : '';

        echo "<input type='text' name='exp_man_company_name' id='exp_man_company_name' value='". esc_attr($company_name) ."' />";
    }

    // Company Settings Section
    public function company_settings_section_cb(){}


    // Currency Settings Section
    public function general_settings_section_cb(){}

    // Select Currency Field callback
    public function select_currency_callback()
    {

        $currency = (!empty(get_option('select_currency'))) ? get_option('select_currency') : '&#36;';

        echo "<select name='select_currency' id='select_currency'>";
        foreach ($this->currency_symbols as $key => $symbol) {
            $selected = '';

            echo $currency . ' ' . $symbol . '<br>';

            if ($currency == $symbol) {
                $selected = 'selected';
            }

            echo "<option value='$symbol' $selected> " . $symbol . ' ' .  strtoupper($key) . " </option>";
        }
        echo "</select>";
    }
}
