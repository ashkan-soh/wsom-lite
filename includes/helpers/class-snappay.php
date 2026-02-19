<?php

namespace WSOM\Helpers;

defined( 'ABSPATH' ) || exit;

class Snappay {

    public static function get_first_snappay_order_date(): ?int {

        $orders = wc_get_orders( [
            'limit'   => 1,
            'orderby' => 'date_created',
            'order'   => 'ASC',
            'status'  => 'any',
            'return'  => 'objects',
            'payment_method' => 'WC_Gateway_SnappPay',
        ] );

        if ( empty( $orders ) ) {
            return null;
        }

        $order = $orders[0];
        $date  = $order->get_date_created();

        return $date ? $date->getTimestamp() : null;
    }
      
    /**
     * Count Snappay orders
     *
     * @return int
     */
    public static function count_snappay_orders(): int {

        $query = new \WC_Order_Query( [
            'status'         => 'any',
            'payment_method' => 'WC_Gateway_SnappPay',
            'limit'          => 1,
            'paginate'       => true,
            'return'         => 'ids',
        ] );

        $results = $query->get_orders();

        return isset( $results->total ) ? (int) $results->total : 0;
    }


    /**
     *
     * @return array{orders: array, total: int}
     */
    public static function get_snappay_orders_page( array $query_args): array {

        $result = wc_get_orders( $query_args );

        if ( is_array( $result ) && isset( $result['orders'] ) ) {
            return [
                'orders'        => $result['orders'],
                'total'         => (int) ( $result['total'] ?? 0 ),
            ];
        }

        if ( is_object( $result ) && isset( $result->orders ) ) {
            return [
                'orders'        => is_array( $result->orders ) ? $result->orders : [],
                'total'         => (int) ( $result->total ?? 0 ),
            ];
        }

        return [
            'orders'        => is_array( $result ) ? $result : [],
            'total'         => is_array( $result ) ? count( $result ) : 0,
        ];
    }
}
