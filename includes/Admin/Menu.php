<?php

namespace ExpenseManager\Admin;

// Menu handler class.
class Menu
{
    public $overview;
    public $expense;
    public $expense_category;
    public $income;
    public $income_source;
    public $payment_type;
    public $settings;
    public $export_import;

    // Constructor
    public function __construct()
    {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menus'));

        $this->overview = new Overview();

        $this->expense = new Expenses();
        $this->expense_category = new ExpenseCategory();

        $this->income = new Income();
        $this->income_source = new IncomeSource();

        $this->payment_type = new PaymentType();

        $this->settings = new ExpSettings();

        $this->export_import = new ExportImport();
    }

    public function add_admin_menus()
    {
        $parent_slug = 'wealcoder-expense-manager';
        $capability = 'manage_options';

        add_menu_page(
            'WP Accountant',
            'WP Accountant',
            $capability,
            $parent_slug,
            array($this->overview, 'render_page'),
            'dashicons-money-alt',
            6
        );

        add_submenu_page($parent_slug, 'Overview', 'Overview', $capability, $parent_slug, array($this->overview, 'render_page'), null);

        add_submenu_page($parent_slug, 'Expenses', 'Expenses', $capability, $parent_slug . '-expenses', array($this->expense, 'render_page'), null);

        add_submenu_page($parent_slug, 'Expense Category', 'Expense Category', $capability, $parent_slug . '-expense-categories', array($this->expense_category, 'render_page'), null);

        add_submenu_page($parent_slug, 'Income', 'Income', $capability, $parent_slug . '-income', array($this->income, 'render_page'), null);

        add_submenu_page($parent_slug, 'Income Sources', 'Income Sources', $capability, $parent_slug . '-income-sources', array($this->income_source, 'render_page'), null);

        add_submenu_page($parent_slug, 'Payment Types', 'Payment Types', $capability, $parent_slug . '-payment-types', array($this->payment_type, 'render_page'), null);

        add_submenu_page($parent_slug, 'Settings', 'Settings', $capability, $parent_slug . '-settings', array($this->settings, 'render_page'), null);

        add_submenu_page($parent_slug, 'Export / Import', 'Export / Import', $capability, $parent_slug . '-export-import', array($this->export_import, 'render_page'), null);
    }
}
