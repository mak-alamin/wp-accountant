<?php

/**
 * Plugin Name: WP Accountant
 * Plugin URI: 
 * Description: A useful WordPress plugin to manage your personal/business accounting easily.
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Mak Alamin
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https: //www.gnu.org/licenses/gpl-2.0.html
 * Update URI: 
 * Text Domain: wp-accountant
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Autoload necessary files.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class.
 */
final class Expense_Manager
{
    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0';

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->define_constants();

        register_activation_hook(__FILE__, [$this, 'activate']);

        add_action('plugins_loaded', [$this, 'init_plugin_classes']);
    }

    /**
     * Initializes a single instance of this class
     *
     * @return \Expense_Manager
     */
    public static function init()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define necessary constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('EXP_MAN_VERSION', self::version);
        define('EXP_MAN_TXT_DOMAIN', 'wp-expense-manager');
        define('EXP_MAN_PATH', __DIR__);
        define('EXP_MAN_FILE', __FILE__);
        define('EXP_MAN_URL', plugins_url('', EXP_MAN_FILE));
        define('EXP_MAN_ASSETS', EXP_MAN_URL . '/assets');
    }

    /**
     * Initializes the required plugin classes
     *
     * @return void
     */
    public function init_plugin_classes()
    {
        if (is_admin()) {
            new ExpenseManager\Admin();
        }
    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate()
    {
        $installer = new ExpenseManager\Installer();
        $installer->install();
    }
}

/**
 * Initializes the main plugin
 *
 * @return \Expense_Manager
 */
Expense_Manager::init();
