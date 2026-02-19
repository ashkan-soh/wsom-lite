<?php
/**
 * Plugin Name: مدیریت اقساط اسنپ‌پی ووکامرس
 * Plugin URI:  https://github.com/ashkan-soh/wsom-lite/
 * Description: یک ابزار ساده و جامع برای نمایش و مدیریت پرداخت‌های اسنپ‌پی در ووکامرس - نسخه رایگان(بدون ویژگی‌های کاربردی)
 * Version:     1.0.0
 * Author:      Ashkan Sohrevardi
 * Author URI: https://github.com/ashkan-soh
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woo-snappay-orders-manager
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'WSOM_VERSION', '1.0.0' );
define( 'WSOM_PATH', plugin_dir_path( __FILE__ ) );
define( 'WSOM_URL', plugin_dir_url( __FILE__ ) );
define( 'WSOM_FILE', __FILE__ );

require_once WSOM_PATH . 'includes/class-wsom.php';

// Declare HPOS (High-Performance Order Storage) compatibility.
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

function wsom_run_plugin() {
    \WSOM\Plugin::instance();
}
add_action( 'plugins_loaded', 'wsom_run_plugin' );