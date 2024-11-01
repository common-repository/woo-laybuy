<?php
// see cconstants.php

if( !defined( 'ABSPATH' ) ) exit;

include_once 'Woocommerce_Laybuy_Logger.php';


class Woocommerce_Laybuy_Form_Handler {

    public static function init() {
        add_action( 'wp_loaded', array( __CLASS__, 'handle_return_url' ), 20 );

    }

    public static function handle_return_url() {
        
        // this is filtering all WP urls, we need to fins the one we need
        
        // /?gateway_id=woocommerce-laybuy&order=wc_order_5ad82b288815b&order_id=3361&status=SUCCESS&token=8i6OSrTRcFgFC73dHPMklCVSdbbUHy1n3LayUaIk
        // /?gateway_id=woocommerce-laybuy&order=wc_order_5b54c5d34a8c0&order_id=20&status=ERROR&token=oMLozNUMHKi55TE3H2IsBzByMPrOdZqiXYAMrwDv
        // /?gateway_id=woocommerce-laybuy&order=wc_order_ZsQPKcrLlJM9d&order_id=74&status=SUCCESS&token=bchLjz3CJ5enoQ5kjdDvBjHpMnTFaeeheDKFYq1h
        
        
        
        if( isset( $_GET['gateway_id'] )
            && WCLAYBUY_WOOCOMMERCE_LAYBUY_SLUG == $_GET['gateway_id']
            && isset( $_GET['order'] )
            && isset( $_GET['order_id'] )
            && isset( $_GET['token']
            ) ) {
    
            $order_id = absint($_GET['order_id']);
            $order    = wc_get_order($order_id);
            $status   = strtoupper($_GET['status']);
    

            if( !$order ) {
                wc_add_notice( 'Invalid order.' );
                $redirect = WC()->cart->get_cart_url();
                wp_redirect( html_entity_decode( $redirect ) );
                exit;
            }
            
            // save the received token from laybuy
            update_post_meta($order_id, '_laybuy_token', $_GET['token'] );
            update_post_meta($order_id, '_laybuy_return_status', $status);
    
            // force pending, make sure the order can't leave us as proccesing unless we get a success & confirmed or id
            $order->set_status('wc-pending');
    
            // save to the DB
            $order->save();
    
            $redirect = $order->get_cancel_order_url(); //default redirect so if we can't find a match we don't change anything
            
            if( empty($status) || 'CANCELLED' === strtoupper($status ) ) {
                $redirect = $order->get_cancel_order_url();
            }
            else if ( 'ERROR' === strtoupper($status)) {
                wc_add_notice(__('Error: payment did not complete.', 'woocommerce'), 'error');
                $redirect = $order->get_cancel_order_url();
            }
            else if( 'SUCCESS' === strtoupper($status) ) {
                
                // do the laybuy conformation here
                // so if the thankyou page doesn't happen we have a correct order in place
                // This is when the 1st installment is charged so it can and often fails here
                $confimed = self::confirmOrder($order_id);
    
                
                // returns a WP error object
                if(is_wp_error($confimed)){
                    
                    // show why confirm failed
                    wc_add_notice($confimed->get_error_message('ERROR') , 'error');
                   
                    try{
                        // maybe declined confirm, force pending
                        $order->set_status('wc-pending');
    
                        // save a note on the order
                        $order->set_customer_note('Laybuy payment failed on first instalment charge: ' . $confimed->get_error_message('ERROR'));
    
                        // save to the DB
                        $order->save();
    
                        $redirect = add_query_arg([
                              'order_id'      => $order_id,
                              'order'         => $order->get_order_key(),
                              'decline_order' => 'true'
                        ], $order->get_cancel_endpoint());
                        
                    }
                    catch (\WC_Data_Exception $e){
                        // fall though to redirect
                    }

                }
                //
                // we have a successful payment
                //
                elseif( intval($confimed) > 0 )  {
                    // order id from laybuy
                    $laybuy_id  = $confimed;
                    
                    // complete the order and reduce stock if required
                    $order->payment_complete($laybuy_id);
                    
                    //wc_reduce_stock_levels($order); hooked into complete
                    WC()->cart->empty_cart();
                    $redirect = woocommerce_laybuy_get_return_url($order);
                }
                
                // if theres a problem fall though
                
            }
            else if( 'DECLINED' === strtoupper( $status ) ) {

                
                $order_key = $_GET['order'];
                $user_can_decline  = current_user_can( 'cancel_order', $order_id );
                $order_can_cancel = $order->has_status( array( 'pending', 'failed' ) );

                if ( $user_can_decline && $order->get_id() === $order_id && $order->get_order_key() === $order_key ) {
                    wc_add_notice( 'Your order is declined.' );
                } else {
                    wc_add_notice( __( 'Invalid Order.', 'woocommerce' ), 'error' );
                }

                $redirect = add_query_arg( array(
                    'order_id' => $order_id,
                    'order' => $order->get_order_key(),
                    'decline_order' => 'true'
                ), $order->get_cancel_endpoint() );
            }
    
            wp_redirect( html_entity_decode( $redirect ) );
            exit;
        }
    }

    public static function handle_order_cancelled( $order_id ) {
        
        /**
         * We need to use the token, we need to save it
         */
        $gateway = new Woocommerce_Laybuy_Gateway();
        
        $order = wc_get_order( $order_id );
        
        if( 'laybuy' == $order->get_payment_method() && !empty( get_post_meta( $order_id, '_laybuy_token', true ) ) ) {
            
            $token = get_post_meta( $order_id, '_laybuy_token', true );

            $settings = woocommerce_laybuy_get_settings();
            
            if( woocommerce_laybuy_is_sandbox_enabled() ) {
                $endpoint = WCLAYBUY_SANDBOX_API_ENDPOINT . WCLAYBUY_CANCEL_ORDER_SUFFIX;
            } else {
                $endpoint = WCLAYBUY_PRODUCTION_API_ENDPOINT . WCLAYBUY_CANCEL_ORDER_SUFFIX;
            }

            $endpoint .= '/' . $token;

            $request = wp_remote_get( $endpoint,
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode($gateway->get_merchant_id() . ':' . $gateway->get_api_key() )
                    )
                )
            );
            
            $response = json_decode($request['body']);
            
            if( is_wp_error( $request ) ) {
                $order->add_order_note( 'The Laybuy system failed to process your request to cancel the order.. msg: ' . $response->error );
            }

            if( 'success' == strtolower( $response->result ) ) {
                $order->add_order_note( 'The Laybuy system has processed your request to cancel the order...' );
            } else if( 'error' == strtolower( $response->result ) ) {
                $order->add_order_note( 'The Laybuy system failed to process your request to cancel the order.. msg: ' . $response->error );
            }
        }
    }

    // at this point laybuy has no refunding through the API
    public static function handle_order_refunded( $order_id ) {
        $order = wc_get_order( $order_id );

        if( 'laybuy' == $order->get_payment_method() && !empty( get_post_meta( $order_id, '_laybuy_order_id', true ) ) ) {

            $settings = woocommerce_laybuy_get_settings();
            if( woocommerce_laybuy_is_sandbox_enabled() ) {
                $endpoint = WCLAYBUY_SANDBOX_API_ENDPOINT . WCLAYBUY_REFUND_ORDER_SUFFIX;
            } else {
                $endpoint = WCLAYBUY_PRODUCTION_API_ENDPOINT . WCLAYBUY_REFUND_ORDER_SUFFIX;
            }

            $order_id = get_post_meta( $order_id, '_laybuy_order_id', true );

            $request_data = array(
                'orderId' => $order_id,
                'amount'  => $order->get_total()
            );

            $request = wp_remote_post( $endpoint,
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode( $settings['merchant_id'] . ':' . $settings['api_key'] )
                    ),
                    'body' => json_encode( $request_data )
                )
            );

            $response = json_decode( $request['body'] );

            if( 'error' == strtolower( $response->result ) ) {
                 $order->add_order_note( 'The Laybuy system failed to process your request to refund the order. Error: ' . $response->error );
            } else if( 'success' == strtolower( $response->result ) ) {
                $order->add_order_note( 'The Laybuy system has processed your request to refund the order.' );
            }
        }
    }
    
    public static function confirmOrder($order_id) {
        
        $gateway = new Woocommerce_Laybuy_Gateway() ;
    
        $settings = woocommerce_laybuy_get_settings();
        
        if (woocommerce_laybuy_is_sandbox_enabled()) {
            $endpoint = WCLAYBUY_SANDBOX_API_ENDPOINT;
        }
        else {
            $endpoint = WCLAYBUY_PRODUCTION_API_ENDPOINT;
        }
      
        $endpoint .= WCLAYBUY_CONFIRM_ORDER_SUFFIX;
    
        $order = wc_get_order($order_id);
        //$order->get_currency();
        Woocommerce_Laybuy_Logger::log("Confirm endpoint:\n " . print_r($endpoint, 1));
        
        
        if (!get_post_meta($order_id, '_laybuy_token', TRUE)) {
            return [
                'result' => FALSE,
                'error'  => 'Token not found error'
            ];
        }
        
        $request_data = [
            'token' => get_post_meta($order_id, '_laybuy_token', TRUE),
            'currency' => $order->get_currency()
        ];
    
        Woocommerce_Laybuy_Logger::log("Confirm request data:\n " . print_r($request_data, 1));
        Woocommerce_Laybuy_Logger::log("Confirm settings:\n " . print_r($settings, 1));
        
        $request = wp_remote_post($endpoint, [
                                               'headers' => [
                                                   //'Authorization' => 'Basic ' . base64_encode($settings['merchant_id'] . ':' . $settings['api_key'])
                                                   'Authorization' => 'Basic ' . base64_encode($gateway->get_merchant_id() . ':' . $gateway->get_api_key())
                                               ],
                                               'body'    => json_encode($request_data)
                                           ]);
        
        Woocommerce_Laybuy_Logger::log("Confirm response:\n ". print_r($request, 1));
        
        
        $response = json_decode($request['body']);
    
        update_post_meta($order_id, '_laybuy_confirm_result', $request['body'] );
    
    
        //
        // IMPORTANT NOTE
        //
        // The order will often fail here
        // the card is only charged $1 when the customer is at laybuy,
        // the 1st installment is charged here and this often fails due to a lack of funds
        //
        // https://docs.laybuy.com/#ConfirmOrder
        //
        if ('SUCCESS' === strtoupper($response->result)) {
            update_post_meta($order_id, '_laybuy_order_id', $response->orderId);
            return $response->orderId;
        }
        else {
            // return a WP_Error we can catch on the url handler
            update_post_meta($order_id, '_laybuy_order_failed', $response->result);
            
            $error = new \WP_Error($response->result, $response->error);
            return $error;
        }
        
    }
    
}

Woocommerce_Laybuy_Form_Handler::init();