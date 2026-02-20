<?php

namespace WSOM;

defined( 'ABSPATH' ) || exit;

use WSOM\Admin\Admin_Menu;

final class Plugin {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->check_dependencies();
        $this->includes();
        $this->init_hooks();
    }

    private function check_dependencies() {

        //Woocommerce
        if ( ! class_exists( 'WooCommerce' ) ) {
            $this->deactivate_with_notice(
                __( 'Woo Snappay Orders Manager requires WooCommerce to be installed and active.', 'snappay-orders-manager' )
            );
            return;
        }

        //Snappay gateway - official version
        if ( ! class_exists( 'WC_Gateway_SnappPay' ) ) {
            $this->deactivate_with_notice(
                __( 'Woo Snappay Orders Manager requires the official Snappay payment gateway plugin to be installed and active.', 'snappay-orders-manager' )
            );
            return;
        }
    }

    private function deactivate_with_notice( $message ) {

        add_action( 'admin_notices', function () use ( $message ) {
            echo '<div class="notice notice-error"><p><strong>';
            echo esc_html( $message );
            echo '</strong></p></div>';
        } );

        add_action( 'admin_init', function () {
            if ( ! function_exists( 'deactivate_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            deactivate_plugins(
                plugin_basename( WSOM_FILE )
            );
        } );
    }

    private function includes() {
        if ( is_admin() ) {
            require_once WSOM_PATH . 'includes/helpers/class-snappay.php';
            require_once WSOM_PATH . 'includes/admin/class-orders-report.php';
            require_once WSOM_PATH . 'includes/class-admin-menu.php';
        }
    }

    private function is_allowed_user(): bool {

        if ( is_multisite() && is_super_admin() ) {
            return true;
        }

        //Admin-only access (WP.org friendly): rely on a capability instead of role string.
        return current_user_can( 'manage_options' );
    }

    private function add_no_access_notice(): void {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__( 'You do not have permission to access this page. (Admin-only)', 'snappay-orders-manager' );
            echo '</p></div>';
        } );
    }

    private function init_hooks() {
        if ( is_admin() ) {
            if ( ! $this->is_allowed_user() ) {
                $this->add_no_access_notice();
                return;
            }

            new Admin_Menu();

            add_action( 'admin_enqueue_scripts', function( $hook_suffix ) {
                            $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
                if ( $page !== \WSOM\Admin\Admin_Menu::MENU_SLUG ) {
                    return;
                }

                wp_enqueue_style(
                    'wsom-admin',
                    plugin_dir_url( WSOM_FILE ) . 'assets/css/admin.css',
                    [],
                    WSOM_VERSION
                );
            } );
        }
    }

}
