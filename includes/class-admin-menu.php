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
            'مدیریت پرداخت‌های اقساطی اسنپ‌پی',
            'مدیریت اقساط اسنپ‌پی',
            'manage_woocommerce',
            self::MENU_SLUG,
            [ $this, 'render_orders_report' ],
            'dashicons-money-alt',
            57
        );

    }

    public function render_orders_report() {

        $table = new Orders_Report();
        $table->prepare_items();

        echo '<div class="wrap wsom-content">';
        echo '<h1 class="wsom-title">گزارش سفارش‌های اسنپ‌پی (نسخه رایگان)</h1>';
        echo '<p class="description">در نسخه‌رایگان، فقط می‌توانید سفارش‌های ۷ روز گذشته را تا سقف ۲۰۰سفارش مشاهده کنید.';
        echo '<br> <a href="https://github.com/ashkan-soh/wsom-lite/docs/index.html" target="_blank">مـستندات نسخه‌ی پـرمیوم</a>';
        echo ' | <a href="https://www.zhaket.com/web/woo-snappay-orders-manager-pro-plugin/" target="_blank">خرید و ارتقا به نسخه پرمیوم</a>';
        echo '</p>';
        $table->display();

        echo '</div>';
    }

}
