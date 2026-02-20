<?php

namespace WSOM\Admin;

defined( 'ABSPATH' ) || exit;

use WP_List_Table;
use WSOM\Helpers\Snappay;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Orders_Report extends WP_List_Table {

    /** @var int */
    protected $total_items = 0;

    /** @var int */
    protected $total_all_snappay = 0;


    public function __construct() {
        parent::__construct( [
            'singular' => 'order',
            'plural'   => 'orders',
            'ajax'     => false,
        ] );
    }

    public function get_columns() {
        return [
            'customer'       => esc_html__( 'Customer', 'snappay-orders-manager' ),
            'order_id'       => esc_html__( 'Order ID', 'snappay-orders-manager' ),
            'order_status'   => esc_html__( 'Order Status', 'snappay-orders-manager' ),
            'transaction_id' => esc_html__( 'Transaction ID', 'snappay-orders-manager' ),
            'paid_date'      => esc_html__( 'Paid Date', 'snappay-orders-manager' ),
            'amount'         => esc_html__( 'Amount', 'snappay-orders-manager' ),
            'payment_status' => esc_html__( 'Payment Status', 'snappay-orders-manager' ),
        ];
    }

    public function prepare_items() {

        $query_args = [
        'type'           => 'shop_order',
        'status'         => 'any',
        'orderby'        => 'date_created',
        'order'          => 'DESC',
        'return'         => 'objects',
        'payment_method' => 'WC_Gateway_SnappPay',
        'limit'          => 200, //Lite: fixed limit, no pagination
        ];

        $from = date( 'Y-m-d', strtotime( '-7 days', current_time( 'timestamp' ) ) );
        $to   = current_time( 'Y-m-d' );

        $after  = $from ? ( $from . ' 00:00:00' ) : '';
        $before = $to   ? ( $to   . ' 23:59:59' ) : '';

        if ( $after && $before ) {
            $query_args['date_created'] = $after . '...' . $before;
        }

        $result = Snappay::get_snappay_orders_page( $query_args );

        $orders = $result['orders'] ?? [];

        //In Lite with limit, total equals retrieved count (not store-wide)
        $this->total_items       = is_array( $orders ) ? count( $orders ) : 0;
        $this->total_all_snappay = Snappay::count_snappay_orders();

        $items = [];

        foreach ( $orders as $order ) {

            if ( ! $order || ! is_a( $order, '\WC_Order' ) ) {
                continue;
            }

            $paid_date = $order->get_date_paid();

            $transaction_id =
                $order->get_meta( 'transaction_id' )
                ?: $order->get_meta( '_transactionId' )
                ?: '—';

            $items[] = [
                'customer'       => $order->get_formatted_billing_full_name(),
                'order_id'       => $order->get_id(),
                'order_status'   => wc_get_order_status_name( $order->get_status() ),
                'transaction_id' => $transaction_id,
                'paid_date'      => $paid_date ? $paid_date->date_i18n( 'Y/m/d H:i' ) : '—',
                'amount'         => wc_price( $order->get_total() ),
                'payment_status' => $paid_date ? 'success' : 'failed',
            ];
        }

        $this->items = $items;

        $this->_column_headers = [ $this->get_columns(), [], [] ];
    }

    protected function column_order_id( $item ) {

        $order_id = absint( $item['order_id'] );

        $url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

        return sprintf(
            '<a href="%s" class="wsom-order-id" target="_blank">%d</a>',
            esc_url( $url ),
            $order_id
        );
    }

    protected function column_payment_status( $item ) {

        //payment_status => paid_date ? success : failed
        $is_success = $item['payment_status'] === 'success';

        $class = $is_success
            ? 'wsom-status-success'
            : 'wsom-status-failed';

        $icon = $is_success
            ? 'dashicons-yes-alt'
            : 'dashicons-no-alt';

        return sprintf(
            '<span class="wsom-payment-status %s">
                <span class="dashicons %s"></span>
            </span>',
            esc_attr( $class ),
            esc_attr( $icon )
        );
    }

    protected function column_default( $item, $column_name ) {
        $value = $item[ $column_name ] ?? '—';

        switch ( $column_name ) {
            case 'customer':
            case 'order_status':
            case 'transaction_id':
            case 'paid_date':
                return esc_html( (string) $value );

            case 'amount':
                //wc_price() returns safe HTML.
                return wp_kses_post( (string) $value );
        }

        return esc_html( (string) $value );
    }

    public function extra_tablenav( $which ) {

    if ( $which !== 'top' ) {
        return;
    }

    $first_snappay_order_ts = Snappay::get_first_snappay_order_date();
    $first_snappay_order_label = $first_snappay_order_ts
        ? ( function_exists( 'wp_date' )
            ? wp_date( 'Y/m/d', $first_snappay_order_ts )
            : date( 'Y/m/d', $first_snappay_order_ts )
        )
        : '—';

    ?>
    <div class="wsom-filter-bar" style="margin-top: 10px;">
        <div class="wsom-report-summary" style="margin-top: 15px;">
            <span class="wsom-report-summary__label" style="margin-right: 12px;"><?php echo esc_html__( 'First Snappay order date:', 'snappay-orders-manager' ); ?></span>
            <span class="wsom-report-summary__count">
                <?php echo esc_html( $first_snappay_order_label ); ?>
            </span>

            <span class="wsom-report-summary__label" style="margin-right: 12px;"><?php echo esc_html__( 'Total Snappay orders in store:', 'snappay-orders-manager' ); ?></span>
            <span class="wsom-report-summary__count">
                <?php echo number_format_i18n( (int) $this->total_all_snappay ); ?>
            </span>

            <span class="wsom-report-summary__label" style="font-weight:bold;"><?php echo esc_html__( 'Snappay orders in last 7 days:', 'snappay-orders-manager' ); ?></span>
            <span class="wsom-report-summary__count" style="font-weight:bold;">
                <?php echo number_format_i18n( (int) $this->total_items ); ?>
            </span>
        </div>
    </div>
    <?php
}

}

