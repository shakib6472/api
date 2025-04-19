<?php
/*
 * Plugin Name:      Credit Report Analizer
 * Plugin URI:        https://github.com/shakib6472/
 * Description:       A plugin to analyze credit reports and provide insights.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Shakib Shown
 * Author URI:        https://github.com/shakib6472/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       core-helper
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Composer autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load plugin classes
use CRA\Class_Upload_Handler;
use CRA\Report_Manager;

// Enqueue Tailwind CSS
function cra_enqueue_assets() {
    
    wp_enqueue_style('cra-tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', [], null);
    wp_enqueue_style('cra-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');
    wp_enqueue_style('cra-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', [], '6.0.0-beta3');
    wp_enqueue_style('cra-bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css', [], '1.5.0');
    wp_enqueue_script('cra-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    wp_enqueue_script('cra-popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js', [], '2.11.6', true);
    wp_enqueue_script('cra-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js', ['cra-popper'], '5.3.0', true);
    wp_enqueue_script('jquery'); 
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);

    wp_enqueue_style('cra-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0.0');
    wp_enqueue_script('cra-main-js', plugin_dir_url(__FILE__) . 'assets/js/main.js', ['jquery'], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'cra_enqueue_assets');


add_action('admin_enqueue_scripts', 'cra_enqueue_admin_assets');

function cra_enqueue_admin_assets($hook) {
    // Load only on our plugin admin page
    if (strpos($hook, 'cra_admin_dashboard') === false) {
        return;
    }

    // Tailwind CDN
    wp_enqueue_style(
        'cra-admin-tailwind',
        'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
        [],
        '2.2.19'
    );

    // Optional: Chart.js for graphs
    wp_enqueue_script(
        'cra-admin-chartjs',
        'https://cdn.jsdelivr.net/npm/chart.js',
        [],
        '4.4.0',
        true
    );
}



// Plugin activation: create DB table
function cra_activate_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'credit_reports';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        file_name VARCHAR(255),
        file_type VARCHAR(50),
        report_data LONGTEXT,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cra_activate_plugin');


new CRA\Class_Upload_Handler();
new \CRA\Class_Dashboard();
new \CRA\Class_Report_History();
CRA\Class_Analytics::init();
CRA\Class_Admin_Dashboard::init();



add_action('admin_post_cra_download_pdf', ['CRA\Class_Dashboard', 'generate_pdf']);
add_action('admin_post_nopriv_cra_manual_entry', ['CRA\Class_Upload_Handler', 'handle_manual_entry']);
add_action('admin_post_cra_manual_entry', ['CRA\Class_Upload_Handler', 'handle_manual_entry']);
