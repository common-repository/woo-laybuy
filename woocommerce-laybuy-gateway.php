<?php

if( !defined( 'ABSPATH' ) ) exit;

class Woocommerce_Laybuy_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'laybuy';
        $this->icon = apply_filters( 'woocommerce_laybuy_gateway_icon', plugin_dir_url(__FILE__) . 'images/laybuy_logo_small.svg' );
        $this->has_fields   = false;
        $this->method_title = 'Laybuy';
    
        $this->supports = ['products'];
    
        $this->init_form_fields();
        $this->init_settings();

        $this->title        = $this->get_option( 'title' );
        $this->description  = '<br>'; //$this->get_option('description');
        
        /*$markup = '<link href="https://fonts.googleapis.com/css?family=Montserrat:300,700&text=.abeilmnoprstuwy1234567890$P" rel="stylesheet">';
        $markup .= '<div style="font-family: Montserrat, sans-serif; color: black; font-weight: 300; font-size: 14px;">';
        $markup .= 'Receive your order now, pay over <br><span style="font-weight: 700;">6 weeks</span> interest free!<br>Selecting Laybuy will re-direct you to a secure checkout facility. ';
        $markup .= '<a style="font-weight: 300; color:black; text-decoration: underline;" target="_blank" href="https://laybuy.com"> Learn More</a>';
        
        if ($this->is_sandbox_enabled()) {

            //<img style="font-weight: 700; display: inline-block;" src="' . plugin_dir_url(__FILE__) . 'images/laybuy_logo_transparent.png" alt="">
            $this->description = "SANDBOX Enabled: <br>" . $markup; //$this->get_option('description');
            
        }
        else {
            $this->description = $markup; // $this->get_option('description');
        }*/
        
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

    }
    
   
     /**
      *
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {
        
        
        $current_settings = woocommerce_laybuy_get_settings();
        
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'woocommerce_laybuy' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Laybuy', 'woocommerce_laybuy' ),
                'default' => 'no'
            ),
            'title' => array(
                'title'       => __( 'Title', 'woocommerce_laybuy' ),
                'type'        => 'text',
                'description' => __( 'This is the title for this payment method. The customer will see this during checkout.', 'woocommerce_laybuy' ),
                'default'     => __( 'Laybuy', 'woocommerce_laybuy' ),
                'desc_tip'    => true,
            ),
           'description' => array(
                'title'       => __( 'Description', 'woocommerce_laybuy' ),
                'type'        => 'textarea',
                'description' => __( 'This is the description for this payment method. The customer will see this during checkout.', 'woocommerce_laybuy' ),
                'default'     => __( 'Receive your order now, pay over 6 weeks interest free!<br>Selecting Laybuy will re-direct you to a secure checkout facility.', 'woocommerce_laybuy' ),
                'desc_tip'    => true,
            ),
            
            'laybuy_fontsize_in_breakdowns' => array(
                'title'       => __('Font Size Breakdowns', 'woocommerce_laybuy'),
                'type'        => 'select',
                'default'     => '12px',
                'description' => __('Select the Font Size in the breakdowns', 'woocommerce_laybuy'),
                'options'     => array(
                    '10px'  => __('10px', 'woocommerce_laybuy'),
                    '12px'  => __('12px', 'woocommerce_laybuy'),
                    '14px'  => __('14px', 'woocommerce_laybuy'),
                    '16px'  => __('16px', 'woocommerce_laybuy'),
                    '18px'  => __('18px', 'woocommerce_laybuy'),
                    '20px'  => __('20px', 'woocommerce_laybuy'),
                )
            ),
            
            'price_breakdown_option_product_page' => array(
                'title'       => __( 'Price breakdown on products', 'woocommerce_laybuy' ),
                'type'        => 'select',
                'description' => __( 'Select how you want to display the price breakdown on each product page.', 'woocommerce_laybuy' ),
                'default'     => 'disable',
                'options'     => array(
                    'disable' => __( 'Disable', 'woocommerce_laybuy' ),
                    'text_only' => __( 'Text Only', 'woocommerce_laybuy' ),
                    'text_and_table' => __( 'Text and Table', 'woocommerce_laybuy' ),
                )
            ),

            'price_breakdown_option_product_page_position' => array(
                'title'       => __('Product Price breakdown Position', 'woocommerce_laybuy'),
                'type'        => 'select',
                'description' => 'Select where on the Product page you would like the breakdown to display, see <a href="https://businessbloomer.com/woocommerce-visual-hook-guide-single-product-page/" target="_blank">here</a> for a visual guide',
                'default'     => 'disable',
                'options'     => array(
                    'woocommerce_single_product_summary'        => __('woocommerce_single_product_summary'    ),
                    'woocommerce_before_add_to_cart_form'       => __('woocommerce_before_add_to_cart_form'   ),
                    'woocommerce_before_variations_form'        => __('woocommerce_before_variations_form'    ),
                    'woocommerce_before_add_to_cart_button'     => __('woocommerce_before_add_to_cart_button' ),
                    'woocommerce_before_single_variation'       => __('woocommerce_before_single_variation'   ),
                    'woocommerce_single_variation'              => __('woocommerce_single_variation'          ),
                    'woocommerce_after_single_variation'        => __('woocommerce_after_single_variation'    ),
                    'woocommerce_after_add_to_cart_button'      => __('woocommerce_after_add_to_cart_button'  ),
                    'woocommerce_after_add_to_cart_form'        => __('woocommerce_after_add_to_cart_form'    ),
                    'woocommerce_product_meta_start'            => __('woocommerce_product_meta_start'        ),
                    'woocommerce_product_meta_end'              => __('woocommerce_product_meta_end'          ),
                ),
            ),
            
            'price_breakdown_option_checkout_page' => array(
                'title'       => __( 'Price breakdown in checkout', 'woocommerce_laybuy' ),
                'type'        => 'select',
                'description' => __( 'Select how you want to display the price breakdown in the checkout page.', 'woocommerce_laybuy' ),
                'default'     => 'disable',
                'options'     => array(
                    'disable' => __( 'Disable', 'woocommerce_laybuy' ),
                    'text_only' => __( 'Text Only', 'woocommerce_laybuy' ),
                    'text_and_table' => __( 'Text and Table', 'woocommerce_laybuy' ),
                )
            ),
            
            'laybuy_currency_prefix_in_breakdowns' => array(
                'title'       => __('Show Currency code in Breakdowns', 'woocommerce_laybuy'),
                'type'        => 'checkbox',
                'default'     => 'yes',
                'description' => __('Show the currency code (ie NZD) in the breakdowns', 'woocommerce_laybuy'),

            ),
            
            'enable_multi_currency' => array(
                'title'       => __('Multi Currency', 'woocommerce_laybuy'),
                'type'        => 'checkbox',
                'label'       => __('Enable Multi Currency', 'woocommerce_laybuy'),
                'description' => __('Allows laybuy to transact in one or more supported currencies (NZD, AUD, GBP).<br>Note that you need a Laybuy Merchant ID and Key for each currency/country.',
                                    'woocommerce_laybuy'),
                'default'     => 'no'
            ),

            
        
        );
        
        
        $sandbox = array(
                'title'       => __( 'Environment', 'woocommerce_laybuy' ),
                'type'        => 'select',
                'description' => __( 'Select the sandbox environment for testing purposes only.', 'woocommerce_laybuy' ),
                'default'     => 'production',
                'options'     => array(
                    'sandbox' => __( 'Sandbox', 'woocommerce_laybuy' ),
                    'production' => __( 'Production', 'woocommerce_laybuy' )
                ),
                'desc_tip'    => true,
        );
        
        if($current_settings['enable_multi_currency'] === 'yes'){
    
            $this->form_fields['environment'] = $sandbox;
    
            foreach (array('NZD' => 'New Zealand Dollars','AUD' => 'Australian Dollars', 'GBP'=> 'Great British Pounds') as $currencry => $name) {
                $this->form_fields[$currencry . '_enabled' ] =  array(
                    'title'   => __( 'Enable/Disable ' . $name .' ('. $currencry.')' , 'woocommerce_laybuy' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable laybuy payment with '. $name, 'woocommerce_laybuy' ),
                    'default' => 'no'
                );
                
                if($current_settings[$currencry . '_enabled'] === 'yes') {
                    $this->form_fields[$currencry . '_merchant_id'] = [
                        'title'       => __($currencry . ' Merchant ID', 'woocommerce_laybuy'),
                        'type'        => 'text',
                        'description' => __('This will be supplied by laybuy.com', 'woocommerce_laybuy'),
                        'default'     => '',
                        'desc_tip'    => TRUE
                    ];
                    $this->form_fields[$currencry . '_api_key']     = [
                        'title'       => __($currencry . ' API Key', 'woocommerce_laybuy'),
                        'type'        => 'text',
                        'description' => __('This will be supplied by laybuy.com', 'woocommerce_laybuy'),
                        'default'     => '',
                        'desc_tip'    => TRUE
                    ];
                }
    
            }
        }
        else {
    
            $this->form_fields['environment'] = $sandbox;
    
            $this->form_fields['laybuy_currency'] = array(
                'title'       => __('Currency', 'woocommerce_laybuy'),
                'type'        => 'select',
                'description' => __('Select the Currency your Laybuy account uses.', 'woocommerce_laybuy'),
                'default'     => 'NZD',
                'options'     => array(
                    'NZD'        => __('New Zealand Dollars', 'woocommerce_laybuy'),
                    'AUD'      => __('Australian Dollars', 'woocommerce_laybuy'),
                    'GBP' => __('Pounds Sterling', 'woocommerce_laybuy'),
                )
            );
            
            $this->form_fields['merchant_id'] = array(
                'title'       => __('Merchant ID', 'woocommerce_laybuy'),
                'type'        => 'text',
                'description' => __('This will be supplied by laybuy.com', 'woocommerce_laybuy'),
                'default'     => '',
                'desc_tip'    => TRUE
            );
            $this->form_fields['api_key'] = array(
                'title'       => __('API Key', 'woocommerce_laybuy'),
                'type'        => 'text',
                'description' => __('This will be supplied by laybuy.com', 'woocommerce_laybuy'),
                'default'     => '',
                'desc_tip'    => TRUE
            );
            
        }
       
        
        // add logging and debug at the end of form
        $this->form_fields['logging'] =  array(
            'title'       => __('Logging', 'woocommerce_laybuy'),
            'label'       => __('Log debug messages', 'woocommerce_laybuy'),
            'type'        => 'checkbox',
            'description' => __('Save debug messages to the WooCommerce System Status log.', 'woocommerce_laybuy'),
            'default'     => 'no',
            'desc_tip'    => TRUE
        );
        
        
        
    }
    
    public function payment_fields() {
        
        Woocommerce_Laybuy_Logger::log('payment_fields -> START ');
        
        if ($description = $this->get_description()) {
          
            $description = apply_filters('laybuy_modify_payment_description', $description, $this->get_order_total());
    
            Woocommerce_Laybuy_Logger::log('payment_fields -> UPDATED get_description: ' . print_r($description, TRUE));
            echo wpautop(wptexturize($description));
        }
    }

    /**
     * This is the method called when they select this gateway as mode of payment.
     */
    public function process_payment( $order_id ) {

        //error_log($order_id);
       
        Woocommerce_Laybuy_Logger::log('Incoming process_payment: ' . print_r($order_id, TRUE));
    
        $settings = woocommerce_laybuy_get_settings();
        
        //$laybuy_currency   =  $settings['laybuy_currency'];
        $laybuy_currency = get_woocommerce_currency();
    
        if (!in_array(get_woocommerce_currency(), [
            'NZD',
            'AUD',
            'GBP'
        ])) {
            $laybuy_currency = 'NZD'; // just in case the default doesn't come though ;-)
        }
    
       
        /* if(!in_array($laybuy_currency, ['NZD','AUD', 'GBP'])){
            $laybuy_currency = 'NZD'; // just in case the default doesn't come though ;-)
        }*/
        
        /*
         * Don't reuse the payment token as these are really single use
         *
         * if( get_post_meta( $order_id, '_laybuy_token', true ) ) {
    
            Woocommerce_Laybuy_Logger::log("Incoming $order_id _laybuy_token _request:" . print_r($_REQUEST, TRUE));
            
            if( $this->is_sandbox_enabled() ) {
                $redirect = WCLAYBUY_SANDBOX_PAY_API_ENDPOINT . 'pay/' . get_post_meta( $order_id, '_laybuy_token', true );
            } else {
                $redirect = WCLAYBUY_PRODUCTION_PAY_API_ENDPOINT . 'pay/' . get_post_meta( $order_id, '_laybuy_token', true );
            }

            return array(
                'result'   => 'success',
                'redirect' => $redirect,
            );
        }*/

        $order = wc_get_order( $order_id );

        /**
         * We need to make our own return url in order to handle the cancel state correctly
         * If the user wants to cancel the order explicitly(intended) then we need to handle this
         */
        $phone = $order->get_billing_phone();
        if (empty($phone) || $phone == '' || strlen(preg_replace('/[^0-9+]/i', '', $phone)) <= 6) {
            $phone = "00 000 000";
        }
        
        $total = $order->get_total();
        $items_total = $total;
        $request_data = array(
            'amount'    => $total,
            'currency'  => $laybuy_currency, // from simple setting value (not using Laybuys currency list yet)
            'returnUrl' => $this->get_custom_return_url( $order ),
            'merchantReference' => '#' . uniqid() . $order_id . time(),
            'customer' => array(
                'firstName' => $order->get_billing_first_name(),
                'lastName' => $order->get_billing_last_name(),
                'email' => $order->get_billing_email(),
                'phone' => $phone
            ),
            'billingAddress' => array(
                "address1" => $order->get_billing_address_1(),
                "city" => $order->get_billing_city(),
                "postcode" => $order->get_billing_postcode(),
                "country" => $order->get_billing_country(),
            ),
            'items' => array()
        );
    
    
        if ('yes' === get_option('woocommerce_prices_include_tax') || $laybuy_currency == 'GBP') {
        
            $request_data['tax'] = $order->get_total_tax();
        
        }
        
        if( $order->get_shipping_total() ) {
            $request_data['items'][] = array(
                'id' => 'shipping_fee_for_order#' . $order_id,
                'description' => 'Shipping fee for this order',
                'quantity' => '1',
                'price' =>  $order->get_shipping_total() + ($order->get_shipping_tax() ? $order->get_shipping_tax() : 0 )
            );
            $items_total = ($items_total - ($order->get_shipping_total() + ($order->get_shipping_tax() ? $order->get_shipping_tax() : 0)));
        }

        if( $order->get_total_tax() && 'no' === get_option( 'woocommerce_prices_include_tax' ) ) {
            $request_data['items'][] = array(
                'id' => 'total_tax_amount_for_order#' . $order_id,
                'description' => 'Tax amount for this order',
                'quantity' => '1',
                'price' => $order->get_total_tax()
            );
            $items_total = ($items_total - $order->get_total_tax() );
            
        }
        $request_data['items'][] = [
            'id'          => 'item_for_order___#' . $order_id,
            'description' => 'Purchase from ' . get_bloginfo('name'),
            'quantity'    => 1,
            'price'       => $items_total
        ];
      
        
        /*foreach ($order->get_items() as $product) {
            $request_data['items'][] = array(
                'id' => $product['product_id'],
                'description' => $product['name'],
                'quantity' => $product['quantity'],
                'price' => $this->get_active_price($product['product_id'])
            );
        }*/

        if( $this->is_sandbox_enabled() ) {
            $create_order_api_endpoint = WCLAYBUY_SANDBOX_API_ENDPOINT . WCLAYBUY_CREATE_ORDER_SUFFIX;
        } else {
            $create_order_api_endpoint = WCLAYBUY_PRODUCTION_API_ENDPOINT . WCLAYBUY_CREATE_ORDER_SUFFIX;
        }
    
        Woocommerce_Laybuy_Logger::log("Laybuy GET REDIRECT URL request data:" . print_r($request_data, TRUE));
        Woocommerce_Laybuy_Logger::log("request auth:\n " . print_r($this->get_merchant_id() . ':' . $this->get_api_key(), 1));
    
        $request = wp_remote_post(
            $create_order_api_endpoint,
            array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( $this->get_merchant_id() . ':' . $this->get_api_key() )
                ),
                'body' => json_encode( $request_data )
            )
        );
    
        Woocommerce_Laybuy_Logger::log("Laybuy GET REDIRECT URL response:" . print_r($request, TRUE));

        if( is_wp_error( $request ) ) {
            $order->update_status( 'failed', __( $request->get_error_message(), 'woocommerce_laybuy' ) );
            wc_add_notice( __( 'Please try to place the order again, Error message: ', 'woocommerce_laybuy' ) . $request->get_error_message(), 'error' );
            return;
        }

        $response = json_decode( $request['body'] );

        if( 'error' == strtolower( $response->result ) ) {
            $order->update_status( 'failed', __( $response->error, 'woocommerce_laybuy' ) );
            wc_add_notice( __( 'Payment error with Laybuy system: ', 'woocommerce_laybuy' ) . $response->error, 'error' );
            return;
        } else {
            return array(
                'result'   => 'success',
                'redirect' => $response->paymentUrl,
            );
        }

    }

    public function get_merchant_id()  {
       
        $current_settings = woocommerce_laybuy_get_settings();
        
        
        if ($current_settings['enable_multi_currency'] === 'yes') {
    
            $laybuy_currency = get_woocommerce_currency();
    
            if (!in_array($laybuy_currency, [
                'NZD',
                'AUD',
                'GBP'
            ])) {
                $laybuy_currency = 'NZD'; // just in case the default doesn't come though ;-)
            }
            
             if ($current_settings[$laybuy_currency . '_enabled'] === 'yes') {
                 return $this->get_option($laybuy_currency . '_merchant_id');
             }
        }
        else {
            return $this->get_option('merchant_id');
        }
    
    
    }

    public function get_api_key()  {
    
        $current_settings = woocommerce_laybuy_get_settings();
    
    
        if ($current_settings['enable_multi_currency'] === 'yes') {
        
            $laybuy_currency = get_woocommerce_currency();
        
            if (!in_array($laybuy_currency, [
                'NZD',
                'AUD',
                'GBP'
            ])) {
                $laybuy_currency = 'NZD'; // just in case the default doesn't come though ;-)
            }
        
            if ($current_settings[$laybuy_currency . '_enabled'] === 'yes') {
                return $this->get_option($laybuy_currency . '_api_key');
            }
        }
        
        return $this->get_option('api_key');
        
    }

    public function is_sandbox_enabled() {
        return 'sandbox' == $this->get_option( 'environment' );
    }

    /**
     * Since Laybuy only accepts a return url we need to somehow create our own to accommodate cancelling.
     */
    public function get_custom_return_url($order) {
        // when creating the cancel or success form see this file WC_Form_Handler line 30
        $home = get_home_url();
        
        $custom_return_url = add_query_arg( array(
            'gateway_id'   => WCLAYBUY_WOOCOMMERCE_LAYBUY_SLUG,
            'order'        => $order->get_order_key(),
            'order_id'     => $order->get_id(),
        ), trailingslashit($home) );

        return $custom_return_url;
    }


    public function get_active_price( $product_id ) {
        $product = wc_get_product( $product_id );

        if( 'yes' == get_option( 'woocommerce_prices_include_tax' ) ) {
            return wc_get_price_including_tax( $product );
        } else {
            return $product->get_price();
        }
    }

}