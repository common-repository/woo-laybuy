<?php
if( !defined( 'ABSPATH' ) ) exit;

define( 'WCLAYBUY_PRODUCTION_API_ENDPOINT', 'https://api.laybuy.com/' );
define( 'WCLAYBUY_SANDBOX_API_ENDPOINT', 'https://sandbox-api.laybuy.com/' );
define( 'WCLAYBUY_PRODUCTION_PAY_API_ENDPOINT', 'https://payment.laybuy.com/' );
define( 'WCLAYBUY_SANDBOX_PAY_API_ENDPOINT', 'https://sandbox-payment.laybuy.com/' );
define( 'WCLAYBUY_CREATE_ORDER_SUFFIX', 'order/create' );
define( 'WCLAYBUY_CANCEL_ORDER_SUFFIX', 'order/cancel' );
define( 'WCLAYBUY_CONFIRM_ORDER_SUFFIX', 'order/confirm' );
define( 'WCLAYBUY_REFUND_ORDER_SUFFIX', 'order/refund' );
define( 'WCLAYBUY_WOOCOMMERCE_LAYBUY_SLUG', 'woocommerce-laybuy');
define( 'WCLAYBUY_WOOCOMMERCE_LAYBUY_PLUGIN_PATH', plugin_dir_url(__FILE__));