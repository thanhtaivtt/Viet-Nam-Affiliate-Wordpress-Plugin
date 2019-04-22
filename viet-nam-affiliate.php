<?php
/**
 * VIET NAM Affiliate
 *
 * @link              https://toidicode.com
 * @since             1.0.0
 * @package           Toidicode.com
 *
 * @wordpress-plugin
 * Plugin Name:       Viet Nam AFFILIATE
 * Plugin URI:        https://github.com/thanhtaivtt/Viet-Nam-Affiliate-Wordpress-Plugin
 * Description:       Viet Nam Affiliate - công cụ cực kì tốt cho publisher. Hỗ trợ tất cả các network tính đến thời điểm hiện tại
 * Version:           1.0.0
 * Author:            Toidicode.com (thanhtaivtt)
 * Author URI:        https://toidicode.com
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       Viet-Nam-Affiliate
 */
defined('ABSPATH') or die('No script kiddies please!');

include(plugin_dir_path(__FILE__) . 'core/TDC.php');
include_once(plugin_dir_path(__FILE__) . 'core/config/constant.php');
include_once(plugin_dir_path(__FILE__) . 'core/function/admin.php');

/**
 * handle when user active plugin
 *
 */
function vnaOnActivation()
{
    $temp = get_option(TDC_OPTION_NAME);

    if (!$temp) {
        $defaultOption = include(plugin_dir_path(__FILE__) . 'core/config/option.php');

        add_option(TDC_OPTION_NAME, $defaultOption);
    }

    /**
     * Create table for checking
     *
     */
    global $wpdb;

    $sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'tdc_link ( id INT NOT NULL AUTO_INCREMENT , link VARCHAR(500) NOT NULL , post_id INT NOT NULL , date INT(8) NOT NULL , ip VARCHAR(50) NOT NULL , created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (id))';
    $wpdb->query($sql);
}

/**
 * Add redirect hook
 *
 */
function vnaAddRedirectLink()
{
    add_filter('template_redirect', ['TDC', 'checkRedirect']);
}

/**
 * add menu to admin layout
 */
function vnaAddMenu()
{
    add_options_page(
        'Viet Nam Affiliate',
        'Viet Nam Affiliate',
        'manage_options',
        __FILE__,
        'vnaLoadOption'
    );
    add_submenu_page(
        null,
        'Viet Nam Affiliate Report',
        'Viet Nam Affiliate Report',
        'manage_options',
        'viet-nam-afffiliate-report.php',
        'vnaLoadReport'
    );
}

//add_action(
//    'admin_init',
//    'loadReport'
//);

function vnaLoadOption()
{
    echo vnaUpdateOption();
}

/**
 * queue script to site
 *
 */
function scriptQueue()
{
    wp_enqueue_style('datatable', plugins_url('/assets/css/datatables.min.css', __FILE__));
    wp_enqueue_script('jquery');
    wp_enqueue_script('datatables.min.js', plugins_url('/assets/js/datatables.min.js', __FILE__));
    wp_enqueue_script('init.min.js', plugins_url('/assets/js/init.js', __FILE__));

}

/**
 *
 * add load report to hook
 *
 */
function vnaLoadReport()
{

    vnaShowReport();
}

//handle register to WP core

add_action('admin_enqueue_scripts', 'scriptQueue');
add_action('admin_menu', 'vnaAddMenu');
add_filter('the_content', ['TDC', 'changeContent']);
add_action('init', 'vnaAddRedirectLink');

register_activation_hook(__FILE__, 'vnaOnActivation');
