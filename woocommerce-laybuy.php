<?php
/*
Plugin Name: Woocommerce Laybuy
Plugin URI: https://www.laybuy.com/
Description:  Payment gateway extension for laybuy.com
Version: 3.4.0
Author: Carl Bowden, Larry Watene
Author URI: carl@16hands.co.nz
Text Domain: woocommerce_laybuy
License: GPL2

Woocommerce Laybuy is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Woocommerce Laybuy is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Woocommerce Laybuy. If not, see https://www.laybuy.com/.
*/


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exit when woocommerce is not active.
 */

$laybuy_active_plugins = (array) get_option('active_plugins', []);

if (is_multisite()) {
    $laybuy_active_plugins = array_merge($laybuy_active_plugins, get_site_option('active_sitewide_plugins', []));
}

if (!(array_key_exists('woocommerce/woocommerce.php', $laybuy_active_plugins) || in_array('woocommerce/woocommerce.php', $laybuy_active_plugins, FALSE))) {
    return;
}

require_once 'constants.php';

// init early so that we don't get caught with other plugisn tring to grab the order and erroring which stops us
add_action('plugins_loaded', 'woocommerce_laybuy_gateway_init', 1);

function woocommerce_laybuy_gateway_init() {
    
    require_once 'includes/woocommerce-laybuy-functions.php';
    require_once 'includes/woocommerce-laybuy-form-handler.php';
    require_once 'includes/Woocommerce_Laybuy_Logger.php';
    require_once 'woocommerce-laybuy-gateway.php';
    
    /**
     * Check if WooCommerce is active
     **/
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        
        // Add the Gateway to WooCommerce
        add_filter('woocommerce_payment_gateways', 'woocommerce_laybuy_add_gateway');
        
        function woocommerce_laybuy_add_gateway($gateways) {
            
            
            if (function_exists('get_woocommerce_currency')) {
                $list             = [
                    'NZD',
                    'AUD',
                    'GBP'
                ]; // to hook up to web service and cache
                $current_currency = get_woocommerce_currency();
                
                
                //$settings = woocommerce_laybuy_get_settings();
                //$laybuy_currency = $settings['laybuy_currency'];
                
                $current_settings = woocommerce_laybuy_get_settings();
                
                
                if (isset($current_currency) && strlen($current_currency) > 2) {
                    
                    if ($current_settings['enable_multi_currency'] === 'yes') {
                        
                        $laybuy_currency = get_woocommerce_currency();
                        $enabled         = FALSE;
                        
                        foreach (
                            [
                                'NZD' => 'New Zealand Dollars',
                                'AUD' => 'Australian Dollars',
                                'GBP' => 'Great British Pounds'
                            ] as $currencry => $name
                        ) {
                            
                            if ($current_settings[$currencry . '_enabled'] === 'yes' && $currencry === get_woocommerce_currency()) {
                                $enabled = TRUE;
                            }
                            
                        }
                        
                        if ($enabled) {
                            $gateways[] = 'Woocommerce_Laybuy_Gateway';
                        }
                        else if (is_admin()) {
                            $gateways[] = 'Woocommerce_Laybuy_Gateway';
                        }
                    }
                    else if (is_admin()) {
                        $gateways[] = 'Woocommerce_Laybuy_Gateway';
                    }
                    else {
                        
                        if ($laybuy_currency = $current_settings['laybuy_currency']) {
                            $gateways[] = 'Woocommerce_Laybuy_Gateway';
                        }
                        
                    }
                    
                    
                }
                
            }
            
            
            return $gateways;
        }
        
    }
    
}

