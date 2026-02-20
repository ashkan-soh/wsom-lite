<?php

namespace WSOM\Admin;

defined( 'ABSPATH' ) || exit;

use WSOM\Admin\Orders_Report;

class Admin_Menu {

    const MENU_SLUG = 'wsom-snappay';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
    }

    public function register_menus() {

        add_menu_page(
            __( 'Snappay Installment Payments Management', 'snappay-orders-manager' ),
            __( 'Snappay Orders Report', 'snappay-orders-manager' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_orders_report' ],
            'dashicons-money-alt',
            57
        );

    }

    public function render_orders_report() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'snappay-orders-manager' ) );
    }

    $table = new Orders_Report();
    $table->prepare_items();

    echo '<div class="wrap wsom-content">';

    echo '<h1 class="wsom-title">' . esc_html__( 'Snappay Orders Report (Free)', 'snappay-orders-manager' ) . '</h1>';

    $desc  = esc_html__( 'This version shows Snappay orders from the last 7 days (up to 200 latest orders).', 'snappay-orders-manager' );
    $desc .= ' ';
    $desc .= esc_html__( 'Additional reporting features are available in a separate Premium version.', 'snappay-orders-manager' );

    $links  = '<br>';
    $links .= sprintf(
        '<a href="%s" target="_blank" class="extr-link" rel="noopener noreferrer">
            <span class="dashicons dashicons-book"></span>
        %s</a>',
        esc_url( 'https://ashkan-soh.github.io/wsom-lite/' ),
        esc_html__( 'Documentation', 'snappay-orders-manager' )
    );

    $links .= ' | ';

    $links .= sprintf(
        '<a href="%s" target="_blank" class="extr-link" rel="noopener noreferrer">
            <span class="dashicons dashicons-external"></span>
        %s</a>',
        esc_url( 'https://ashkan-soh.github.io/wsom-lite/#get-pro' ),
        esc_html__( 'Get Premium Version', 'snappay-orders-manager' )
    );

    echo '<p class="description">' . wp_kses_post( $desc . $links ) . '</p>';

    $table->display();

    echo '</div>';
}

}
