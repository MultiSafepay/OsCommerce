<?php

$dir = dirname(dirname(dirname(dirname(__FILE__))));
require_once($dir . "/mspcheckout/include/API/Autoloader.php");

class multisafepay_klarna {

    var $code;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $plugin_name;
    var $icon = "klarna.png";
    var $order_id;
    var $public_title;
    var $status;
    var $taxes = array();
    var $_customer_id = 0;
    var $msp;
    var $pluginversion = '3.0.0';
    var $liveurl = 'https://api.multisafepay.com/v1/json/';
    var $testurl = 'https://testapi.multisafepay.com/v1/json/';

    /**
     * 
     * @global type $order
     * @param type $order_id
     */

    function __construct($order_id = -1)
    {
        global $order;

        $this->code = 'multisafepay_klarna';
        $this->title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_TITLE);
        $this->enabled = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS == 'True';
        $this->sort_order = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER;
        $this->plugin_name = $this->pluginversion . '(' . PROJECT_VERSION . ')';

        if (is_object($order) || is_object($GLOBALS['order']))
        {
            $this->update_status();
        }

        $this->order_id = $order_id;
        $this->public_title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_TITLE);
        $this->status = null;
    }

    /**
     * 
     * @param type $admin
     * @return type
     */
    
    function getTitle($admin = 'title')
    {

        if (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ICON_DISABLED != 'False')
        {

            $title = ($this->checkView() == "checkout") ? $this->generateIcon($this->getIcon()) . " " : "";
        } else
        {
            $title = "";
        }


        $title .= ($this->checkView() == "admin") ? "MultiSafepay - " : "";
        if ($admin && $this->checkView() == "admin")
        {
            $title .= $admin;
        } else
        {
            $title .= $this->getLangStr($admin);
        };
        return $title;
    }

    /**
     * 
     * @param type $str
     * @return type
     */
    function getLangStr($str)
    {
        return $str;
    }

    /**
     * 
     * @param type $icon
     * @return type
     */
    function generateIcon($icon)
    {
        //return tep_image($icon, '', 60, 23, 'style="float:left;margin-right:10px;"');
        return tep_image($icon, '', 50, 23, 'style="display:inline-block;vertical-align: middle;height:100%;margin-right:10px;"');  
    }

    /**
     * 
     * @global type $PHP_SELF
     * @return type
     */
    function getScriptName()
    {
        global $PHP_SELF;

        return basename($PHP_SELF);
    }

    /**
     * 
     * @return string
     */
    function getIcon()
    {
        $icon = DIR_WS_IMAGES . "multisafepay/en/" . $this->icon;

        if (file_exists(DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon))
        {
            $icon = DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon;
            return $icon;
        }
    }

    /**
     * 
     * @global type $languages_id
     * @param type $savedSetting
     * @return string
     */
    function getUserLanguage($savedSetting)
    {
        if ($savedSetting != "DETECT")
        {
            return $savedSetting;
        }

        global $languages_id;

        $query = tep_db_query("select languages_id, name, code, image, directory from " . TABLE_LANGUAGES . " where languages_id = " . (int) $languages_id . " limit 1");
        if ($languages = tep_db_fetch_array($query))
        {
            return strtoupper($languages['code']);
        }

        return "EN";
    }

    /**
     * 
     * @return string
     */
    function checkView()
    {
        $view = "admin";

        if (!tep_session_is_registered('admin'))
        {
            if ($this->getScriptName() == 'checkout_payment.php') //FILENAME_CHECKOUT_PAYMENT)
            {
                $view = "checkout";
            } else
            {
                $view = "frontend";
            }
        }
        return $view;
    }

    /*
     * Check whether this payment module is available
     */

    function update_status()
    {
        // always disable
        //$this->enabled = false;
        if ($this->enabled && ((int) MODULE_PAYMENT_MSP_BANKTRANS_ZONE > 0))
        {
            $check_flag = false;
            $check_query = tep_db_query("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_MSP_BANKTRANS_ZONE . "' AND zone_country_id = '" . $GLOBALS['order']->billing['country']['id'] . "' ORDER BY zone_id");

            while ($check = tep_db_fetch_array($check_query))
            {
                if ($check['zone_id'] < 1)
                {
                    $check_flag = true;
                    break;
                } else if ($check['zone_id'] == $GLOBALS['order']->billing['zone_id'])
                {
                    $check_flag = true;
                    break;
                }
            }

            if (!$check_flag)
            {
                $this->enabled = false;
            }
        }
    }

    // ---- select payment module ----

    /*
     * Client side javascript that will verify any input fields you use in the
     * payment method selection page
     */
    function javascript_validation()
    {
        return false;
    }
           
    /*
     * Outputs the payment method title/text and if required, the input fields
     */

    function selection()
    {
        global $customer_id;
        global $languages_id;
        global $order;
        global $order_totals;
        global $order_products_id;

        // check if transaction is possible
        //Not needed obviously?
        //Previously: if(empty($this->api_url)... / $this->api_url has already been removed.
        if (empty($this->liveurl) || empty($this->testurl))
        {
            return;
        }

        return array(
            'id' => $this->code,
            'module' => $this->public_title
        );
    }

    /*
     * Any checks of any conditions after payment method has been selected
     */

    function pre_confirmation_check()
    {
        return false;
    }

    // ---- confirm order ----

    /*
     * Any checks or processing on the order information before proceeding to
     * payment confirmation
     */
    function confirmation()
    {
        return false;
    }

    /*
     * Outputs the html form hidden elements sent as POST data to the payment
     * gateway
     */

    function process_button()
    {
        return false;
    }

    // ---- process payment ----

    /*
     * Payment verification
     */
    function before_process()
    {
        $this->_save_order();
        tep_redirect($this->_start_klarna());
    }

    /*
     * Post-processing of the payment/order after the order has been finalised
     */

    function after_process()
    {
        return false;
    }

    // ---- error handling ----

    /*
     * Advanced error handling
     */
    
    /**
     * 
     * @return boolean
     */
    
    function output_error()
    {
        return false;
    }

    /**
     * 
     * @return type
     */
    
    function get_error()
    {
        $error = array(
            'title' => 'Error: ',
            'error' => $this->_get_error_message($_GET['error'])
        );

        return $error;
    }
    
    /**
     * 
     * @param type $message
     * @return type
     */
    
    function _get_error_message($message)
    {
        return $message;
    }
    
    /**
     * 
     * @param type $street_address
     * @return type
     */
    public function parseAddress($street_address)
    {
        $address = $street_address;
        $apartment = "";

        $offset = strlen($street_address);

        while (($offset = $this->rstrpos($street_address, ' ', $offset)) !== false)
        {
            if ($offset < strlen($street_address) - 1 && is_numeric($street_address[$offset + 1]))
            {
                $address = trim(substr($street_address, 0, $offset));
                $apartment = trim(substr($street_address, $offset + 1));
                break;
            }
        }

        if (empty($apartment) && strlen($street_address) > 0 && is_numeric($street_address[0]))
        {
            $pos = strpos($street_address, ' ');

            if ($pos !== false)
            {
                $apartment = trim(substr($street_address, 0, $pos), ", \t\n\r\0\x0B");
                $address = trim(substr($street_address, $pos + 1));
            }
        }

        return array($address, $apartment);
    }

    /**
     * 
     * @param type $haystack
     * @param type $needle
     * @param type $offset
     * @return boolean
     */
    
    public function rstrpos($haystack, $needle, $offset = null)
    {
        $size = strlen($haystack);

        if (is_null($offset))
        {
            $offset = $size;
        }

        $pos = strpos(strrev($haystack), strrev($needle), $size - $offset);

        if ($pos === false)
        {
            return false;
        }

        return $size - $pos - strlen($needle);
    }

    /**
     * 
     * @return type
     */
    
    function _start_klarna()
    {
        // generate items list

        $items_list = "<ul>\n";
        foreach ($GLOBALS['order']->products as $product)
        {
            $items_list .= "<li>" . $product['name'] . "</li>\n";
        }
        $items_list .= "</ul>\n";

        $this->msp = new \MultiSafepayAPI\Client();

        if (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER == 'Live account')
        {
            $this->msp->setApiUrl($this->liveurl);
        } else
        {
            $this->msp->setApiUrl($this->testurl);
        }

        $this->msp->setApiKey(MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_KEY);

        $trans_type = "redirect";
        $daysactive = "30";

        if (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT == "True")
        {
            $redirect_url = $this->_href_link('ext/modules/payment/multisafepay/success.php', '', 'SSL', false, false);
        } else
        {
            $redirect_url = null;
        }
        
        list($street, $housenumber) =   $this->parseAddress($GLOBALS['order']->customer['street_address']);
        list($del_street, $del_housenumber) =   $this->parseAddress($GLOBALS['order']->delivery['street_address']);
        
        $amount                     =   round($GLOBALS['order']->info['total'], 2) * 100;
        
        try {
            $order = $this->msp->orders->post(array(
                "type" => $trans_type,
                "gateway" => 'KLARNA',
                "order_id" => $this->order_id,
                "currency" => $GLOBALS['order']->info['currency'],
                "amount" => round($amount),
                "description" => 'Order #' . $this->order_id . ' at ' . STORE_NAME,
                "var1" => $GLOBALS['customer_id'],
                "var2" => tep_session_id() . ';' . tep_session_name(),
                "var3" => $GLOBALS['cartID'],
                "items" => $items_list,
                "days_active" => $daysactive,
                "gateway_info" => array(
                       "birthday" => null,
                       "bank_account" => null,
                       "phone" => $GLOBALS['order']->customer['telephone'],
                       "email" => $GLOBALS['order']->customer['email_address'],
                       "gender" => null,
                       "referrer" => $_SERVER['HTTP_REFERER'],
                       "user_agent" => $_SERVER['HTTP_USER_AGENT']
                   ),
                   "payment_options" => array(
                       "notification_url" => $this->_href_link('ext/modules/payment/multisafepay/notify_checkout.php?type=initial', '', 'SSL', false, false),
                       "redirect_url" => $redirect_url,
                       "cancel_url" => $this->_href_link('shopping_cart.php', '', 'SSL', false, false),
                       "close_window" => true
                   ),
                   "customer" => array(
                        "locale" => $this->getLocale($GLOBALS['language']),
                        "ip_address" => $_SERVER['REMOTE_ADDR'],
                        "forwarded_ip" => $_SERVER['HTTP_FORWARDED'],
                        "first_name" => $GLOBALS['order']->customer['firstname'],
                        "last_name" => $GLOBALS['order']->customer['lastname'],
                        "address1" => $street,
                        "address2" => null,
                        "house_number" => $housenumber,
                        "zip_code" => $GLOBALS['order']->customer['postcode'],
                        "city" => $GLOBALS['order']->customer['city'],
                        "state" => $GLOBALS['order']->customer['state'],
                        "country" => $GLOBALS['order']->customer['country']['iso_code_2'],
                        "phone" => $GLOBALS['order']->customer['telephone'],
                        "email" => $GLOBALS['order']->customer['email_address'],
                        "disable_send_email" => null
                   ),
                   "delivery" => array(
                    "first_name" => $GLOBALS['order']->delivery['firstname'],
                    "last_name" => $GLOBALS['order']->delivery['lastname'],
                    "address1" => $del_street,
                    "address2" => null,
                    "house_number" => $del_housenumber,
                    "zip_code" => $GLOBALS['order']->delivery['postcode'],
                    "city" => $GLOBALS['order']->delivery['city'],
                    "state" => $GLOBALS['order']->delivery['state'],
                    "country" => $GLOBALS['order']->delivery['country']['iso_code_2'],
                    "phone" => $GLOBALS['order']->customer['telephone'],
                    "email" => $GLOBALS['order']->customer['email_address']
                ),
                "shopping_cart" => $this->getShoppingCart(),
                "checkout_options" => $this->getCheckoutOptions(),
                "google_analytics" => array(
                    "account" => null
                ),
                "plugin" => array(
                    "shop" => "OsCommerce",
                    "shop_version" => PROJECT_VERSION,
                    "plugin_version" => $this->pluginversion,
                    "partner" => "MultiSafepay",
                ),                
            ));
            
            return $this->msp->orders->getPaymentLink();
        } catch (Exception $e) {
            $this->_error_redirect(htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * 
     * @return $shoppingcart_array array
     */
    
    function getShoppingCart()
    {
        $shoppingcart_array = array();

        foreach ($GLOBALS['order']->products as $product)
        {
            $attributeString = '';
            if (!empty($product['attributes']))
            {
                foreach ($product['attributes'] as $attribute)
                {
                    $attributeString .= $attribute['option'] . ' ' . $attribute['value'] . ', ';
                }
                $attributeString = substr($attributeString, 0, -2);
                $attributeString = ' (' . $attributeString . ')';
            }

            $shoppingcart_array['items'][] = array(
                "name" => $product['name'],
                "description" => $attributeString,
                "unit_price" => $product['price'],
                "quantity" => $product['qty'],
                "merchant_item_id" => $product['model'],
                "tax_table_selector" => 'BTW' . $product['tax'],
                "weight" => array(
                    "unit" => "KG",
                    "value" => $product['weight'],
                )
            );
        }

        $shoppingcart_array['items'][] = array(
            "name" => $GLOBALS['order']->info['shipping_method'],
            "unit_price" => $GLOBALS['order']->info['shipping_cost'],
            "quantity" => 1,
            "merchant_item_id" => 'msp-shipping',
            "tax_table_selector" => 'BTW21', //Default 21% in The Netherlands
            "weight" => array(
                "unit" => "KG",
                "value" => 0,
        ));

        return $shoppingcart_array;
    }

    /**
     * 
     * @return $checkoutoptions_array array
     */
    
    function getCheckoutOptions()
    {
        $checkoutoptions_array = array();
        $checkoutoptions_array['tax_tables'] = array(
            "default" => array(
                "shipping_taxed" => true,
                "rate" => 0.21, //Default 21% in The Netherlands
            ),
            "alternate" => array()
        );

        if (isset($GLOBALS['order']->info['tax_groups']['Unknown tax rate']))
        {
            $checkoutoptions_array['tax_tables']['alternate'][] = array(
                "standalone" => false,
                "name" => 'BTW0',
                "rules" => array(
                    array(
                        "rate" => 0.00,
                        "country" => null
                    )
                )
            );
        }

        foreach ($GLOBALS['order']->products as $product)
        {
            if (!$this->in_array_recursive($product['tax'] / 100, $checkoutoptions_array['tax_tables']['alternate']))
            {
                $checkoutoptions_array['tax_tables']['alternate'][] = array(
                    "standalone" => false,
                    "name" => 'BTW' . $product['tax'],
                    "rules" => array(
                        array(
                            "rate" => $product['tax'] / 100,
                            "country" => 'NL'
                        )
                    )
                );
            }
        }

        return $checkoutoptions_array;
    }

    /**
     * 
     * @param type $needle
     * @param type $haystack
     * @param type $strict
     * @return boolean
     */
    
    function in_array_recursive($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item)
        {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_recursive($needle, $item, $strict)))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @global type $currencies
     * @return type
     */
    
    function checkout_notify()
    {
        global $currencies;
        $this->order_id = $_GET['transactionid'];


        $msp = new MultiSafepayAPI();
        $msp->plugin_name = $this->plugin_name;
        $msp->test = (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER != 'Live account');
        $msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ACCOUNT_ID;
        $msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_ID;
        $msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_SECURE_CODE;
        $msp->transaction['id'] = $this->order_id;

        // get status
        $status = $msp->getStatus();

        if ($msp->error)
        {
            echo $msp->error_code . ": " . $msp->error;
            exit();
        }

        if (!$msp->details['transaction']['var1'])
        { // no customer_id, so create a customer
            //$this->resume_session($msp->details);
            $customer_id = $this->get_customer($msp->details);
        } else
        {
            //$this->resume_session($msp->details);
            $customer_id = $msp->details['transaction']['var1'];
            //tep_session_register('customer_id');
        }

        $this->_customer_id = $customer_id;

        $customer_country = $this->get_country_from_code($msp->details['customer']['country']);
        $delivery_country = $this->get_country_from_code($msp->details['customer-delivery']['country']);

        // update customer data in order
        /* $sql_data_array = array('customers_name' 			=> 	$msp->details['customer']['firstname'] . ' ' . $msp->details['customer']['lastname'],
          'customers_company' 			=> 	$msp->details['customer']['company'],
          'customers_street_address' 	=> 	$msp->details['customer']['address1'] . ' ' . $msp->details['customer']['housenumber'],
          'customers_suburb' 			=> 	'',
          'customers_city' 				=> 	$msp->details['customer']['city'],
          'customers_postcode' 			=> 	$msp->details['customer']['zipcode'],
          'customers_state' 			=> 	$msp->details['customer']['state'],
          'customers_country' 			=> 	$customer_country['countries_name'],
          'customers_telephone' 		=> 	$msp->details['customer']['phone1'],
          'customers_email_address'	 	=> 	$msp->details['customer']['email'],
          'customers_address_format_id' => 	'1',

          'delivery_name' 				=> 	$msp->details['customer-delivery']['firstname'] . ' ' . $msp->details['customer-delivery']['lastname'],
          'delivery_company' 			=> 	$msp->details['customer-delivery']['company'],
          'delivery_street_address' 	=> 	$msp->details['customer-delivery']['address1'] . ' ' . $msp->details['customer-delivery']['housenumber'],
          'delivery_suburb' 			=> 	'',
          'delivery_city' 				=> 	$msp->details['customer-delivery']['city'],
          'delivery_postcode' 			=> 	$msp->details['customer-delivery']['zipcode'],
          'delivery_state' 				=> 	$msp->details['customer-delivery']['state'],
          'delivery_country' 			=> 	$delivery_country['countries_name'],
          'delivery_address_format_id' 	=> 	'1',

          'billing_name' 				=> 	$msp->details['customer']['firstname'] . ' ' . $msp->details['customer']['lastname'],
          'billing_company' 			=> 	$msp->details['customer']['company'],
          'billing_street_address' 		=> 	$msp->details['customer']['address1'] . ' ' . $msp->details['customer']['housenumber'],
          'billing_suburb' 				=> 	'',
          'billing_city' 				=> 	$msp->details['customer']['city'],
          'billing_postcode' 			=> 	$msp->details['customer']['zipcode'],
          'billing_state' 				=> 	$msp->details['customer']['state'],
          'billing_country' 			=> 	$customer_country['countries_name'],
          'billing_address_format_id' 	=> 	'1',

          'payment_method' 				=> 	'MultiSafepay fast checkout',
          //'orders_status' => $order->info['order_status'],
          // 'currency' => $order->info['currency'],
          //'currency_value' => $order->info['currency_value']);
          );

          if ($customer_id)
          {
          $sql_data_array['customers_id'] = $customer_id;
          }

          // create query and update
          $query = "UPDATE " . TABLE_ORDERS . " SET ";
          foreach($sql_data_array as $key => $val)
          {
          $query 	.= 	$key . " = '" . $val . "',";
          }
          $query 		= 	substr($query, 0, -1);
          $query 		.= 	" WHERE orders_id = '" . $this->order_id . "'";
          tep_db_query($query);

         */
        $currency = 'EUR';

        // update order total
        $value = $msp->details['order-total']['total'];
        $text = '<b>' . $currencies->format($value, false, 'EUR', $currencies->currencies[$currency]['value']) . '</b>';
        $query = "UPDATE " . TABLE_ORDERS_TOTAL .
                " SET value = '" . $value . "', text = '" . $text . "'" .
                " WHERE class = 'ot_total' AND orders_id = '" . $this->order_id . "'";
        tep_db_query($query);

        // update tax
        $value = $msp->details['total-tax']['total'];
        $text = $currencies->format($value, false, 'EUR', $currencies->currencies[$currency]['value']);
        $query = "UPDATE " . TABLE_ORDERS_TOTAL .
                " SET value = '" . $value . "', text = '" . $text . "'" .
                " WHERE class = 'ot_tax' AND orders_id = '" . $this->order_id . "'";
        tep_db_query($query);

        // update or add shipping
        $check_shipping = tep_db_query("SELECT count(1) as count FROM " . TABLE_ORDERS_TOTAL . " WHERE class = 'ot_shipping' AND orders_id = '" . $this->order_id . "'");
        $check_shipping = tep_db_fetch_array($check_shipping);
        $check_shipping = $check_shipping['count'];

        $value = $msp->details['shipping']['cost'];
        $title = $msp->details['shipping']['name'];
        $text = $currencies->format($value, false, 'EUR', $currencies->currencies[$currency]['value']);
        if ($check_shipping)
        {
            $query = "UPDATE " . TABLE_ORDERS_TOTAL .
                    " SET title = '" . $title . "', value = '" . $value . "', text = '" . $text . "'" .
                    " WHERE class = 'ot_shipping' AND orders_id = '" . $this->order_id . "'";
            tep_db_query($query);
        } else
        {
            $query = "INSERT INTO " . TABLE_ORDERS_TOTAL .
                    "(orders_id, title, text, value, class, sort_order)" .
                    " VALUES ('" . $this->order_id . "','" . $title . "','" . $text . "','" . $value . "','ot_shipping','2')";
            tep_db_query($query);
        }

        // current order status
        $current_order = tep_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = " . $this->order_id);
        $current_order = tep_db_fetch_array($current_order);
        $old_order_status = $current_order['orders_status'];

        //$status = "completed";
        //determine new osCommerce order status
        $reset_cart = false;
        $notify_customer = false;
        $new_order_status = null;

        switch ($status)
        {
            case "initialized":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED;
                $reset_cart = true;
                break;
            case "completed":
                if ($old_order_status == MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED || $old_order_status == DEFAULT_ORDERS_STATUS_ID || !$old_order_status)
                {
                    $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_COMPLETED;
                    $reset_cart = true;
                    //$notify_customer = ($old_order_status != $new_order_status);
                    $notify_customer = true;
                }
                break;
            case "uncleared":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_UNCLEARED;
                break;
            case "reserved":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_RESERVED;
                break;
            case "void":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_VOID;
                break;
            case "declined":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_DECLINED;
                break;
            case "reversed":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_REVERSED;
                break;
            case "refunded":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_REFUNDED;
                break;
            case "refunded":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_PARTIAL_REFUNDED;
                break;
            case "expired":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_EXPIRED;
                break;
            case "cancelled":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_VOID;
                break;
            default:
                $new_order_status = DEFAULT_ORDERS_STATUS_ID;
        }

        $GLOBALS['order']->info['order_status'] = $new_order_status;

        // update order with new status if needed
        if ($new_order_status)
        {
            $order_status_query = tep_db_query("SELECT orders_status_name FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_id = '" . $new_order_status . "' AND language_id = '" . $GLOBALS['languages_id'] . "'");
            $order_status = tep_db_fetch_array($order_status_query);
            $GLOBALS['order']->info['orders_status'] = $order_status['orders_status_name'];

            // update order
            tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = '" . $new_order_status . "' WHERE orders_id = " . $this->order_id);
        }

        // reload the order and totals
        $GLOBALS['order'] = new order($this->order_id);
        $GLOBALS['order_totals'] = $GLOBALS['order']->totals;

        // notify customer, or just add note to history
        if ($notify_customer)
        {
            $this->_notify_customer($new_order_status);
        } else
        {
            if ($new_order_status && $old_order_status != $new_order_status)
            {
                $sql_data_array = array('orders_id' => $this->order_id,
                    'orders_status_id' => $new_order_status,
                    'date_added' => 'now()',
                    'customer_notified' => 0,
                    'comments' => '');
                tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            }
        }

        // reset cart
        if ($reset_cart)
        {
            //tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = '" . (int)$GLOBALS['order']->customer['id'] . "'");
            //tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id = '" . (int)$GLOBALS['order']->customer['id'] . "'");
        }
        //print_r($msp->details);
        return $status;
    }

    /**
     * 
     * @param type $details
     */
    
    function resume_session($details)
    {
        if (isset($details['transaction']['var2']))
        {
            list ($sess_id, $sess_name) = explode(";", $details['transaction']['var2']);
            //If session management is supported by this PHP version
            if (function_exists('session_id'))
                session_id($sess_id);
            if (function_exists('session_name'))
                session_name($sess_name);
        }
    }

    /**
     * 
     * @param type $details
     * @return \type
     */
    
    function get_customer($details)
    {
        $email = $details['customer']['email'];
        $customer_exists = tep_db_fetch_array(tep_db_query("select customers_id from " .
                        TABLE_CUSTOMERS . " where customers_email_address = '" . $email . "'"));

        $new_user = false;
        if ($customer_exists['customers_id'] != '')
        {
            $customer_id = $customer_exists['customers_id'];
            //tep_session_register('customer_id');
        } else
        {
            $sql_data_array = array(
                'customers_firstname' => $details['customer']['firstname'],
                'customers_lastname' => $details['customer']['lastname'],
                'customers_email_address' => $details['customer']['email'],
                'customers_telephone' => $details['customer']['phone1'],
                'customers_fax' => '',
                'customers_default_address_id' => 0,
                'customers_password' => tep_encrypt_password('test123'),
                'customers_newsletter' => 1
            );
            if (ACCOUNT_DOB == 'true')
            {
                $sql_data_array['customers_dob'] = 'now()';
            }
            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
            $customer_id = tep_db_insert_id();
            //tep_session_register('customer_id');
            tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . "
                                    (customers_info_id, customers_info_number_of_logons,
                                     customers_info_date_account_created)
                               values ('" . (int) $customer_id . "', '0', now())");

            $new_user = true;
        }

        //      The user exists and is logged in
        //      Check database to see if the address exist.
        $address_book = tep_db_query("select address_book_id, entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . "
										where  customers_id = '" . $customer_id . "'
										and entry_street_address = '" . $details['customer']['address1'] . ' ' . $details['customer']['housenumber'] . "'
										and entry_suburb = '" . '' . "'
										and entry_postcode = '" . $details['customer']['zipcode'] . "'
										and entry_city = '" . $details['customer']['city'] . "'
									");

        //      If not, add the addr as default one
        if (!tep_db_num_rows($address_book))
        {
            $country = $this->get_country_from_code($details['customer']['country']);

            $sql_data_array = array(
                'customers_id' => $customer_id,
                'entry_gender' => '',
                'entry_company' => '',
                'entry_firstname' => $details['customer']['firstname'],
                'entry_lastname' => $details['customer']['lastname'],
                'entry_street_address' => $details['customer']['address1'] . ' ' . $details['customer']['housenumber'],
                'entry_suburb' => '',
                'entry_postcode' => $details['customer']['zipcode'],
                'entry_city' => $details['customer']['city'],
                'entry_state' => '',
                'entry_country_id' => $country['countries_id'],
                'entry_zone_id' => ''
            );
            tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

            $address_id = tep_db_insert_id();
            tep_db_query("update " . TABLE_CUSTOMERS . "
                                set customers_default_address_id = '" . (int) $address_id . "'
                                where customers_id = '" . (int) $customer_id . "'");
            $customer_default_address_id = $address_id;
            $customer_country_id = $country['countries_id'];
            //$customer_zone_id = $zone_answer['zone_id'];
        } else
        {
            $customer_default_address_id = $address_book['address_book_id'];
            $customer_country_id = $address_book['entry_country_id'];
            //$customer_zone_id = $address_book['entry_zone_id'];
        }
        $customer_first_name = $details['customer']['firstname'];
        //tep_session_register('customer_default_address_id');
        //tep_session_register('customer_country_id');
        //tep_session_register('customer_zone_id');
        //tep_session_register('customer_first_name');

        return $customer_id;
        //  Customer exists, is logged and address book is up to date
    }

    /**
     * 
     * @param type $code
     * @return type
     */
    function get_country_from_code($code)
    {
        //countries_iso_code_2
        $country = tep_db_fetch_array(tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . $code . "'"));
        return $country;
    }

    /**
     * 
     * @param type $order_id
     * @param type $customer_id
     * @return type
     */
    
    function get_hash($order_id, $customer_id)
    {
        return md5($order_id . $customer_id);
    }

    /**
     * 
     * @param type $error
     */
    
    function _error_redirect($error)
    {
        tep_redirect($this->_href_link(FILENAME_SHOPPING_CART, 'payment_error=' . $this->code . '&error=' . $error, 'NONSSL', true, false, false));
    }

    // ---- Ripped from checkout_process.php ----

    /*
     * Store the order in the database, and set $this->order_id
     */
    
    function _save_order()
    {
        global $customer_id;
        global $languages_id;
        global $order;
        global $order_totals;
        global $order_products_id;

        if (empty($order_totals))
        {
            require(DIR_WS_CLASSES . 'order_total.php');
            $order_total_modules = new order_total();
            $order_totals = $order_total_modules->process();
        }

        if (!empty($this->order_id) && $this->order_id > 0)
        {
            return;
        }

        $sql_data_array = array('customers_id' => $customer_id,
            'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
            'customers_company' => $order->customer['company'],
            'customers_street_address' => $order->customer['street_address'],
            'customers_suburb' => $order->customer['suburb'],
            'customers_city' => $order->customer['city'],
            'customers_postcode' => $order->customer['postcode'],
            'customers_state' => $order->customer['state'],
            'customers_country' => $order->customer['country']['title'],
            'customers_telephone' => $order->customer['telephone'],
            'customers_email_address' => $order->customer['email_address'],
            'customers_address_format_id' => $order->customer['format_id'],
            'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
            'delivery_company' => $order->delivery['company'],
            'delivery_street_address' => $order->delivery['street_address'],
            'delivery_suburb' => $order->delivery['suburb'],
            'delivery_city' => $order->delivery['city'],
            'delivery_postcode' => $order->delivery['postcode'],
            'delivery_state' => $order->delivery['state'],
            'delivery_country' => $order->delivery['country']['title'],
            'delivery_address_format_id' => $order->delivery['format_id'],
            'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
            'billing_company' => $order->billing['company'],
            'billing_street_address' => $order->billing['street_address'],
            'billing_suburb' => $order->billing['suburb'],
            'billing_city' => $order->billing['city'],
            'billing_postcode' => $order->billing['postcode'],
            'billing_state' => $order->billing['state'],
            'billing_country' => $order->billing['country']['title'],
            'billing_address_format_id' => $order->billing['format_id'],
            'payment_method' => $order->info['payment_method'],
            'cc_type' => $order->info['cc_type'],
            'cc_owner' => $order->info['cc_owner'],
            'cc_number' => $order->info['cc_number'],
            'cc_expires' => $order->info['cc_expires'],
            'date_purchased' => 'now()',
            'orders_status' => MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED,
            'currency' => $order->info['currency'],
            'currency_value' => $order->info['currency_value'],
            'payment_method' => 'Klarna'
        );

        tep_db_perform(TABLE_ORDERS, $sql_data_array);
        $insert_id = tep_db_insert_id();
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++)
        {
            $sql_data_array = array('orders_id' => $insert_id,
                'title' => $order_totals[$i]['title'],
                'text' => $order_totals[$i]['text'],
                'value' => $order_totals[$i]['value'],
                'class' => $order_totals[$i]['code'],
                'sort_order' => $order_totals[$i]['sort_order']);
            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        }

        $sql_data_array = array('orders_id' => $insert_id,
            'orders_status_id' => $order->info['order_status'],
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => $order->info['comments']);
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++)
        {
            // Stock Update - Joao Correia
            if (STOCK_LIMITED == 'true')
            {
                if (DOWNLOAD_ENABLED == 'true')
                {
                    $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                        FROM " . TABLE_PRODUCTS . " p
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                         ON p.products_id=pa.products_id
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                         ON pa.products_attributes_id=pad.products_attributes_id
                                        WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
                    // Will work with only one option for downloadable products
                    // otherwise, we have to build the query dynamically with a loop
                    $products_attributes = $order->products[$i]['attributes'];
                    if (is_array($products_attributes))
                    {
                        $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
                    }
                    $stock_query = tep_db_query($stock_query_raw);
                } else
                {
                    $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                }
                if (tep_db_num_rows($stock_query) > 0)
                {
                    $stock_values = tep_db_fetch_array($stock_query);
                    // do not decrement quantities if products_attributes_filename exists
                    if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename']))
                    {
                        $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
                    } else
                    {
                        $stock_left = $stock_values['products_quantity'];
                    }
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false'))
                    {
                        tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    }
                }
            }

            // Update products_ordered (for bestsellers list)
            tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

            $sql_data_array = array('orders_id' => $insert_id,
                'products_id' => tep_get_prid($order->products[$i]['id']),
                'products_model' => $order->products[$i]['model'],
                'products_name' => $order->products[$i]['name'],
                'products_price' => $order->products[$i]['price'],
                'final_price' => $order->products[$i]['final_price'],
                'products_tax' => $order->products[$i]['tax'],
                'products_quantity' => $order->products[$i]['qty']);
            tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
            $order_products_id = tep_db_insert_id();

            //------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = '';
            if (isset($order->products[$i]['attributes']))
            {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++)
                {
                    if (DOWNLOAD_ENABLED == 'true')
                    {
                        $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
										   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
										   left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
											on pa.products_attributes_id=pad.products_attributes_id
										   where pa.products_id = '" . $order->products[$i]['id'] . "'
											and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
											and pa.options_id = popt.products_options_id
											and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
											and pa.options_values_id = poval.products_options_values_id
											and popt.language_id = '" . $languages_id . "'
											and poval.language_id = '" . $languages_id . "'";
                        $attributes = tep_db_query($attributes_query);
                    } else
                    {
                        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                    }
                    $attributes_values = tep_db_fetch_array($attributes);

                    $sql_data_array = array('orders_id' => $insert_id,
                        'orders_products_id' => $order_products_id,
                        'products_options' => $attributes_values['products_options_name'],
                        'products_options_values' => $attributes_values['products_options_values_name'],
                        'options_values_price' => $attributes_values['options_values_price'],
                        'price_prefix' => $attributes_values['price_prefix']);
                    tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename']))
                    {
                        $sql_data_array = array('orders_id' => $insert_id,
                            'orders_products_id' => $order_products_id,
                            'orders_products_filename' => $attributes_values['products_attributes_filename'],
                            'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                            'download_count' => $attributes_values['products_attributes_maxcount']);
                        tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                    }
                    $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
                }
            }
        }
        $this->order_id = $insert_id;
    }

    /**
     * 
     * @global type $customer_id
     * @global type $order
     * @global type $order_totals
     * @global type $order_products_id
     * @global type $total_products_price
     * @global type $products_tax
     * @global type $languages_id
     * @global type $currencies
     * @global type $payment
     * @param type $new_order_status
     */
    
    function _notify_customer($new_order_status = null)
    {
        global $customer_id;
        global $order;
        global $order_totals;
        global $order_products_id;
        global $total_products_price;
        global $products_tax;
        global $languages_id;
        global $currencies;
        global $payment;

        if ($new_order_status != null)
        {
            $order->info['order_status'] = $new_order_status;
        }

        $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
        $sql_data_array = array('orders_id' => $this->order_id,
            'orders_status_id' => $order->info['order_status'],
            'date_added' => 'now()',
            'customer_notified' => $customer_notification,
            'comments' => $order->info['comments']);
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        $products_ordered = '';
        $total_weight = 0;
        $total_tax = 0;
        $total_cost = 0;

        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++)
        {
            //------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = '';
            if (isset($order->products[$i]['attributes']))
            {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++)
                {
                    if (isset($order->products[$i]['attributes'][$j]['option_id']))
                    {
                        if (DOWNLOAD_ENABLED == 'true')
                        {
                            $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                                from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                                on pa.products_attributes_id=pad.products_attributes_id
                                                where pa.products_id = '" . $order->products[$i]['id'] . "'
                                                and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                                and pa.options_id = popt.products_options_id
                                                and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                                and pa.options_values_id = poval.products_options_values_id
                                                and popt.language_id = '" . $languages_id . "'
                                                and poval.language_id = '" . $languages_id . "'";
                        } else
                        {
                            $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                                from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                where pa.products_id = '" . $order->products[$i]['id'] . "'
                                                and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                                and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                                and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "'
                                                and poval.language_id = '" . $languages_id . "'";
                        }

                        $attributes = tep_db_query($attributes_query);
                        $attributes_values = tep_db_fetch_array($attributes);
                    } else
                    {
                        $attributes_values = array();
                        $attributes_values['products_options_name'] = $order->products[$i]['attributes'][$j]['option'];
                        $attributes_values['products_options_values_name'] = $order->products[$i]['attributes'][$j]['value'];
                        $attributes_values['options_values_price'] = $order->products[$i]['attributes'][$j]['price'];
                        $attributes_values['price_prefix'] = $order->products[$i]['attributes'][$j]['prefix'];
                    }

                    $sql_data_array = array('orders_id' => $this->order_id,
                        'orders_products_id' => $order_products_id,
                        'products_options' => $attributes_values['products_options_name'],
                        'products_options_values' => $attributes_values['products_options_values_name'],
                        'options_values_price' => $attributes_values['options_values_price'],
                        'price_prefix' => $attributes_values['price_prefix']);
                    tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename']))
                    {
                        $sql_data_array = array('orders_id' => $this->order_id,
                            'orders_products_id' => $order_products_id,
                            'orders_products_filename' => $attributes_values['products_attributes_filename'],
                            'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                            'download_count' => $attributes_values['products_attributes_maxcount']);
                        tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                    }
                    $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ': ' . $attributes_values['products_options_values_name'];
                }
            }
            //------insert customer choosen option eof ----

            $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
            $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
            $total_cost += $total_products_price;

            $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
        }

        // lets start with the email confirmation
        $email_order = STORE_NAME . "\n" .
                EMAIL_SEPARATOR . "\n" .
                MODULE_PAYMENT_MULTISAFEPAY_KLARNA_EMAIL_TEXT_ORDER_STATUS . ' ' . $order->info['orders_status'] . "\n" .
                EMAIL_TEXT_ORDER_NUMBER . ' ' . $this->order_id . "\n" .
                EMAIL_TEXT_INVOICE_URL . ' ' . $this->_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false) . "\n" .
                EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
        if ($order->info['comments'])
        {
            $email_order .= tep_db_output($order->info['comments']) . "\n\n";
        }
        $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                EMAIL_SEPARATOR . "\n" .
                $products_ordered .
                EMAIL_SEPARATOR . "\n";

        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++)
        {
            $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
        }

        if ($order->content_type != 'virtual')
        {
            $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                    EMAIL_SEPARATOR . "\n" .
                    $this->_address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n") . "\n";
        }

        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                EMAIL_SEPARATOR . "\n" .
                $this->_address_format($order->billing['format_id'], $order->billing, 0, '', "\n") . "\n\n";

        if (is_object($$payment))
        {
            $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                    EMAIL_SEPARATOR . "\n";
            $payment_class = $$payment;
            if (!empty($order->info['payment_method']))
            {
                $email_order .= $order->info['payment_method'] . "\n\n";
            } else
            {
                $email_order .= $payment_class->title . "\n\n";
            }
            if ($payment_class->email_footer)
            {
                $email_order .= $payment_class->email_footer . "\n\n";
            }
        }

        //print_r($order);
        //echo $email_order;

        tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        // send emails to other people
        if (SEND_EXTRA_ORDER_EMAILS_TO != '')
        {
            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
    }

    // ---- Ripped from includes/functions/general.php ----

    /**
     * 
     * @param type $address_format_id
     * @param string $address
     * @param type $html
     * @param type $boln
     * @param type $eoln
     * @return string
     */
    
    function _address_format($address_format_id, $address, $html, $boln, $eoln)
    {
        $address_format_query = tep_db_query("SELECT address_format AS format FROM " . TABLE_ADDRESS_FORMAT . " WHERE address_format_id = '" . (int) $address_format_id . "'");
        $address_format = tep_db_fetch_array($address_format_query);

        $company = $this->_output_string_protected($address['company']);
        if (isset($address['firstname']) && tep_not_null($address['firstname']))
        {
            $firstname = $this->_output_string_protected($address['firstname']);
            $lastname = $this->_output_string_protected($address['lastname']);
        } elseif (isset($address['name']) && tep_not_null($address['name']))
        {
            $firstname = $this->_output_string_protected($address['name']);
            $lastname = '';
        } else
        {
            $firstname = '';
            $lastname = '';
        }

        $street = $this->_output_string_protected($address['street_address']);
        $suburb = $this->_output_string_protected($address['suburb']);
        $city = $this->_output_string_protected($address['city']);
        $state = $this->_output_string_protected($address['state']);

        if (isset($address['country_id']) && tep_not_null($address['country_id']))
        {
            $country = tep_get_country_name($address['country_id']);

            if (isset($address['zone_id']) && tep_not_null($address['zone_id']))
            {
                $state = tep_get_zone_code($address['country_id'], $address['zone_id'], $state);
            }
        } elseif (isset($address['country']) && tep_not_null($address['country']))
        {
            if (is_array($address['country']))
            {
                $country = $this->_output_string_protected($address['country']['title']);
            } else
            {
                $country = $this->_output_string_protected($address['country']);
            }
        } else
        {
            $country = '';
        }

        $postcode = $this->_output_string_protected($address['postcode']);
        $zip = $postcode;

        if ($html)
        {
            // HTML Mode
            $HR = '<hr>';
            $hr = '<hr>';
            if (($boln == '') && ($eoln == "\n"))
            { // Values not specified, use rational defaults
                $CR = '<br>';
                $cr = '<br>';
                $eoln = $cr;
            } else
            { // Use values supplied
                $CR = $eoln . $boln;
                $cr = $CR;
            }
        } else
        {
            // Text Mode
            $CR = $eoln;
            $cr = $CR;
            $HR = '----------------------------------------';
            $hr = '----------------------------------------';
        }

        $statecomma = '';
        $streets = $street;
        if ($suburb != '')
            $streets = $street . $cr . $suburb;
        if ($state != '')
            $statecomma = $state . ', ';

        $fmt = $address_format['format'];
        eval("\$address = \"$fmt\";");

        if ((ACCOUNT_COMPANY == 'true') && (tep_not_null($company)))
        {
            $address = $company . $cr . $address;
        }
        return $address;
    }

    /**
     * 
     * @param type $string
     * @param type $translate
     * @param type $protected
     * @return type
     */
    
    function _output_string($string, $translate = false, $protected = false)
    {
        if ($protected == true)
        {
            return htmlspecialchars($string);
        } else
        {
            if ($translate == false)
            {
                return $this->_parse_input_field_data($string, array('"' => '&quot;'));
            } else
            {
                return $this->_parse_input_field_data($string, $translate);
            }
        }
    }

    /**
     * 
     * @param type $string
     * @return type
     */
    
    function _output_string_protected($string)
    {
        return $this->_output_string($string, false, true);
    }

    /**
     * 
     * @param type $data
     * @param type $parse
     * @return type
     */
    
    function _parse_input_field_data($data, $parse)
    {
        return strtr(trim($data), $parse);
    }

    /**
     * 
     * @global type $request_type
     * @global type $session_started
     * @global type $SID
     * @param type $page
     * @param type $parameters
     * @param type $connection
     * @param type $add_session_id
     * @param type $unused
     * @param type $escape_html
     * @return string
     */
    
    function _href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $unused = true, $escape_html = true)
    {
        global $request_type, $session_started, $SID;

        unset($unused);

        if (!tep_not_null($page))
        {
            die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
        }

        if ($connection == 'NONSSL')
        {
            $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
        } elseif ($connection == 'SSL')
        {
            if (ENABLE_SSL == true)
            {
                $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
            } else
            {
                $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
            }
        } else
        {
            die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
        }

        if (tep_not_null($parameters))
        {
            if ($escape_html)
            {
                $link .= $page . '?' . $this->_output_string($parameters);
            } else
            {
                $link .= $page . '?' . $parameters;
            }
            $separator = '&';
        } else
        {
            $link .= $page;
            $separator = '?';
        }

        while ((substr($link, -1) == '&') || (substr($link, -1) == '?'))
            $link = substr($link, 0, -1);

        // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
        if (($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False'))
        {
            if (tep_not_null($SID))
            {
                $_sid = $SID;
            } elseif (( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ))
            {
                if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN)
                {
                    $_sid = tep_session_name() . '=' . tep_session_id();
                }
            }
        }

        if (isset($_sid))
        {
            if ($escape_html)
            {
                $link .= $separator . $this->_output_string($_sid);
            } else
            {
                $link .= $separator . $_sid;
            }
        }
        return $link;
    }

    /*
     * Checks whether the payment has been “installed” through the admin panel
     */

    function check()
    {
        if (!isset($this->_check))
        {
            $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /*
     * Installs the configuration keys into the database
     */

    function install()
    {
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('MultiSafepay enabled', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS', 'True', 'Enable MultiSafepay payments for this website', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Account type', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER', 'Live account', '<a href=\'https://testmerchant.multisafepay.com/signup\' target=\'_blank\' style=\'text-decoration: underline; font-weight: bold; color:#696916; \'>Sign up for a free test account!</a>', '6', '21', 'tep_cfg_select_option(array(\'Live account\', \'Test account\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('API Key', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_KEY', '', 'Your MultiSafepay API Key', '6', '22', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Auto Redirect', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT', 'True', 'Enable auto redirect after payment', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '25', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Daysactive', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_DAYS_ACTIVE', '', 'The number of days a paymentlink remains active.', '6', '22', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Google Analytics', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_GA', '', 'Google Analytics Account ID', '6', '22', now())");        
        
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Initialized Order Status', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED', 0, 'In progress', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Completed Order Status',   'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_COMPLETED',   0, 'Completed successfully', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Uncleared Order Status',   'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_UNCLEARED',   0, 'Not yet cleared', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Reserved Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_RESERVED',    0, 'Reserved', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Voided Order Status',      'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_VOID',        0, 'Cancelled', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Declined Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_DECLINED',    0, 'Declined (e.g. fraud, not enough balance)', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Reversed Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_REVERSED',    0, 'Undone', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refunded Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_REFUNDED',    0, 'Refunded', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Expired Order Status',     'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_EXPIRED',     0, 'Expired', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Partial Refunded Order Status',     'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_PARTIAL_REFUNDED',     0, 'Partial Refunded', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable payment method icons', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ICON_DISABLED', 'False', 'Enable payment method icons in front of the title', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");        
    }

    /*
     * Removes the configuration keys from the database
     */

    function remove()
    {
        tep_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    /*
     * Defines an array containing the configuration key keys that are used by
     * the payment module
     */

    function keys()
    {
        return array(
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_KEY',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_DAYS_ACTIVE',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_GA',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_COMPLETED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_UNCLEARED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_RESERVED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_VOID',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_DECLINED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_REVERSED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_REFUNDED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_EXPIRED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_PARTIAL_REFUNDED',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ICON_DISABLED',
        );
    }

    /**
     * 
     * @param type $lang
     * @return string
     */
    
    function getLocale($lang)
    {
        switch ($lang)
        {
            case "dutch":
                $lang = 'nl_NL';
                break;
            case "spanish":
                $lang = 'es_ES';
                break;
            case "french":
                $lang = 'fr_FR';
                break;
            case "german":
                $lang = 'de_DE';
                break;
            case "english":
                $lang = 'en_GB';
                break;
            case "italian":
            case "italiano":                    
                $lang = 'it_IT';
                break;        
            default:
                $lang = 'en_GB';
                break;
        }

        return $lang;
    }

    /**
     * 
     * @param type $country
     * @return type
     */
    
    function getcountry($country)
    {
        if (empty($country))
        {
            $langcode = explode(";", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $langcode = explode(",", $langcode['0']);
            return strtoupper($langcode['1']);
        } else
        {
            return strtoupper($country);
        }
    }

}

?>