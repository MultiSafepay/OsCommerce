<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/mspcheckout/include/API/Autoloader.php");

if (!class_exists('multisafepay'))
{

    class multisafepay {

        var $code;
        var $title;
        var $description;
        var $enabled;
        var $sort_order;
        var $plugin_name;
        var $icon = "connect.png";
        var $api_url;
        var $order_id;
        var $public_title;
        var $status;
        var $shipping_methods = array();
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
        function multisafepay($order_id = -1)
        {
            global $order;

            $this->code = 'multisafepay';
            $this->title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE);
            $this->enabled = MODULE_PAYMENT_MULTISAFEPAY_STATUS == 'True';
            $this->sort_order = MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER;
            $this->plugin_name = $this->pluginversion . '(' . PROJECT_VERSION . ')';

            if (is_object($order) || is_object($GLOBALS['order']))
            {
                $this->update_status();
            }

            $this->order_id = $order_id;
            $this->public_title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE);
            $this->status = null;
        }

        /**
         * 
         * @global type $order
         */
        function update_status()
        {
            global $order;

            if (($this->enabled == true) && ((int) MODULE_PAYMENT_MULTISAFEPAY_ZONE > 0))
            {
                $check_flag = false;
                $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MULTISAFEPAY_ZONE
                        . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
                while ($check = tep_db_fetch_array($check_query))
                {
                    if ($check['zone_id'] < 1)
                    {
                        $check_flag = true;
                        break;
                    } elseif ($check['zone_id'] == $order->billing['zone_id'])
                    {
                        $check_flag = true;
                        break;
                    }
                }
                if ($check_flag == false)
                {
                    $this->enabled = false;
                }
            }
        }

        /**
         * 
         * @return boolean
         */
        function javascript_validation()
        {
            return false;
        }

        /**
         * 
         * @global type $customer_id
         * @global type $languages_id
         * @global type $order
         * @global type $order_totals
         * @global type $order_products_id
         * @return type
         */
        function selection()
        {
            global $customer_id;
            global $languages_id;
            global $order;
            global $order_totals;
            global $order_products_id;

            $selection = array
                (
                'id' => $this->code,
                'module' => $this->public_title,
                'fields' => array()
            );
            return $selection;
        }

        /**
         * 
         * @return boolean
         */
        function pre_confirmation_check()
        {
            $gatewaytest = $_POST['payment'];

            if (!$gatewaytest)
            {
                $error = 'Select a payment method.';
                $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error);
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
            }
            return false;
        }

        /**
         * 
         * @global type $HTTP_POST_VARS
         * @global type $order
         * @return boolean
         */
        function confirmation()
        {
            global $HTTP_POST_VARS, $order;

            return false;
        }

        /**
         * 
         * @return boolean
         */
        function process_button()
        {
            return false;
        }

        function before_process()
        {
            $this->_save_order();
            tep_redirect($this->_start_transaction());
        }

        /**
         * 
         * @return boolean
         */
        function after_process()
        {
            return false;
        }

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
                'title' => 'Error:',
                'error' => $this->_get_error_message($_GET['error'])
            );

            return $error;
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
        function _start_transaction()
        {
            $amount = round($GLOBALS['order']->info['total'], 2) * 100;

            $items_list = "<ul>\n";
            foreach ($GLOBALS['order']->products as $product)
            {
                $items_list .= "<li>" . $product['name'] . "</li>\n";
            }
            $items_list .= "</ul>\n";

            $this->msp = new \MultiSafepayAPI\Client();

            if (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live account')
            {
                $this->msp->setApiUrl($this->liveurl);
            } else
            {
                $this->msp->setApiUrl($this->testurl);
            }

            $this->msp->setApiKey(MODULE_PAYMENT_MULTISAFEPAY_API_KEY);

            //temp

            $trans_type = "redirect";

            if (isset($_POST['msp_paymentmethod']))
            {
                $gateway = $_POST['msp_paymentmethod'];
            } else
            {
                $gateway = null;
            }

            if ($gateway == 'IDEAL' && $_POST["msp_issuer"])
            {
                $issuer_id = $_POST["msp_issuer"];
            } else
            {
                $issuer_id = null;
            }

            if (MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT == "True")
            {
                $redirect_url = $this->_href_link('ext/modules/payment/multisafepay/success.php', '', 'SSL', false, false);
            } else
            {
                $redirect_url = null;
            }

            list($street, $housenumber) = $this->parseAddress($GLOBALS['order']->customer['street_address']);

            try {
                $order = $this->msp->orders->post(array(
                    "type" => $trans_type,
                    "order_id" => $this->order_id,
                    "currency" => $GLOBALS['order']->info['currency'],
                    "amount" => round($amount),
                    "description" => 'Order #' . $this->order_id . ' at ' . STORE_NAME,
                    "var1" => $GLOBALS['customer_id'],
                    "var2" => tep_session_id() . ';' . tep_session_name(),
                    "var3" => $GLOBALS['cartID'],
                    "items" => $items_list,
                    "manual" => "false",
                    "gateway" => $gateway,
                    "days_active" => MODULE_PAYMENT_MULTISAFEPAY_DAYS_ACTIVE,
                    "payment_options" => array(
                        "notification_url" => $this->_href_link('ext/modules/payment/multisafepay/notify_checkout.php?type=initial', '', 'SSL', false, false),
                        "redirect_url" => $redirect_url,
                        "cancel_url" => $this->_href_link('ext/modules/payment/multisafepay/cancel.php', '', 'SSL', false, false),
                        "close_window" => "true"
                    ),
                    "customer" => array(
                        "locale" => $this->getLocale($GLOBALS['language']),
                        "ip_address" => $_SERVER['REMOTE_ADDR'],
                        "forwarded_ip" => $_SERVER['HTTP_FORWARDED'],
                        "first_name" => $GLOBALS['order']->customer['firstname'],
                        "last_name" => $GLOBALS['order']->customer['lastname'],
                        "address1" => $street,
                        "address2" => "",
                        "house_number" => $housenumber,
                        "zip_code" => $GLOBALS['order']->customer['postcode'],
                        "city" => $GLOBALS['order']->customer['city'],
                        "state" => "",
                        "country" => $GLOBALS['order']->customer['country']['iso_code_2'],
                        "phone" => $GLOBALS['order']->customer['telephone'],
                        "email" => $GLOBALS['order']->customer['email_address'],
                    ),
                    "google_analytics" => array(
                        "account" => MODULE_PAYMENT_MULTISAFEPAY_GA,
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
                $this->_error_redirect($e->getMessage());
            }
        }

        /**
         * 
         * @return type
         */
        function check_transaction()
        {
            try {
                $this->msp = new \MultiSafepayAPI\Client();

                if (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live account')
                {
                    $this->msp->setApiUrl($this->liveurl);
                } else
                {
                    $this->msp->setApiUrl($this->testurl);
                }

                $this->msp->setApiKey(MODULE_PAYMENT_MULTISAFEPAY_API_KEY);

                $response_obj = $this->msp->issuers->get('orders', $this->order_id);

                return $response_obj;
            } catch (Exception $e) {
                echo htmlspecialchars($e->getMessage());
            }
        }

    /**
     * 
     * @param type $details
     * @return type
     */
    
        function get_customer($details)
        {
            $email = $details->customer->email;

            //Check if the email exists

            $customer_exists = tep_db_fetch_array(tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $email . "'"));

            $new_user = false;

            if ($customer_exists['customers_id'] != '')
            {          
                $customer_id = $customer_exists['customers_id'];
            } else {
                $sql_data_array = array(
                    'customers_firstname' => tep_db_input($details->customer->first_name),
                    'customers_lastname' => tep_db_input($details->customer->last_name),
                    'customers_email_address' => $details->customer->email,
                    'customers_telephone' => $details->customer->phone1,
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

                tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . "(customers_info_id, customers_info_number_of_logons, customers_info_date_account_created)
                              values ('" . (int) $customer_id . "', '0', now())");

                $new_user = true;
            }

            //The user exists and is logged in
            //Check database to see whether or not the address exists.

            $address_book = tep_db_query("select address_book_id, entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . "
                                                                                    where  customers_id = '" . $customer_id . "'
                                                                                    and entry_street_address = '" . $details->customer->address1 . ' ' . $details->customer->house_number . "'
                                                                                    and entry_suburb = '" . '' . "'
                                                                                    and entry_postcode = '" . $details->customer->zip_code . "'
                                                                                    and entry_city = '" . $details->customer->city . "'");

            //If not, add the address as default one

            if (@!tep_db_num_rows($address_book->lengths))
            {
                $country = $this->get_country_from_code($details->customer->country);

                $sql_data_array = array(
                    'customers_id' => $customer_id,
                    'entry_gender' => '',
                    'entry_company' => '',
                    'entry_firstname' => tep_db_input($details->customer->first_name),
                    'entry_lastname' => tep_db_input($details->customer->last_name),
                    'entry_street_address' => $details->customer->address1 . ' ' . $details->customer->house_number,
                    'entry_suburb' => '',
                    'entry_postcode' => $details->customer->zip_code,
                    'entry_city' => $details->customer->city,
                    'entry_state' => '',
                    'entry_country_id' => $country['countries_id'],
                    'entry_zone_id' => ''
                );

                tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

                $address_id = tep_db_insert_id();

                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int) $address_id . "' where customers_id = '" . (int) $customer_id . "'");

                $customer_default_address_id = $address_id;
                $customer_country_id = $country['countries_id'];
            } else {
                $customer_default_address_id = $address_book['address_book_id'];
                $customer_country_id = $address_book['entry_country_id'];
            }

            return $customer_id;
        }       

        /**
         * 
         * @param type $code
         * @return type
         */
        
        function get_country_from_code($code)
        {
            $country = tep_db_fetch_array(tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . $code . "'"));
            return $country;
        }        
        
        
        /**
         * 
         * @return type
         */
        function checkout_notify()
        {
            try {
                $this->msp = new \MultiSafepayAPI\Client();

                if (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live account')
                {
                    $this->msp->setApiUrl($this->liveurl);
                } else
                {
                    $this->msp->setApiUrl($this->testurl);
                }

                $this->msp->setApiKey(MODULE_PAYMENT_MULTISAFEPAY_API_KEY);
                $response_obj = $this->msp->issuers->get('orders', $this->order_id);
            } catch (Exception $e) {
                echo htmlspecialchars($e->getMessage());
            }

            if (!$response_obj->var1)
            {
                // no customer_id, so create a customer
                $customer_id = $this->get_customer($response_obj);
            } else
            {
                $customer_id = $response_obj->var1;
                //tep_session_register('customer_id');
            }

            $this->_customer_id = $customer_id;

            $reset_cart = false;
            $notify_customer = false;
            $status = $response_obj->status;

            $current_order = tep_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = " . $this->order_id);
            $current_order = tep_db_fetch_array($current_order);
            $old_order_status = $current_order['orders_status'];
            $new_status = $old_order_status;

            switch ($status)
            {
                case "initialized":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED;
                    $reset_cart = true;
                    break;
                case "completed":
                    if (in_array($old_order_status, array(MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED, DEFAULT_ORDERS_STATUS_ID, MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED)))
                    {
                        $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED;
                        $reset_cart = true;
                        if ($old_order_status != $GLOBALS['order']->info['order_status'])
                        {
                            $notify_customer = true;
                        }
                        $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED;
                    }
                    break;
                case "uncleared":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED;
                    break;
                case "reserved":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED;
                    break;
                case "void":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID)
                    {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

                        while ($order = tep_db_fetch_array($order_query))
                        {
                            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                        }
                    }
                    break;
                case "cancelled":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID)
                    {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

                        while ($order = tep_db_fetch_array($order_query))
                        {
                            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                        }
                    }
                    break;
                case "declined":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED)
                    {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

                        while ($order = tep_db_fetch_array($order_query))
                        {
                            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                        }
                    }
                    break;
                case "reversed":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED;
                    break;
                case "refunded":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED;
                    break;
                case "partial_refunded":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED;
                    break;
                case "expired":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED;
                    $new_status = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED)
                    {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");
                        while ($order = tep_db_fetch_array($order_query))
                        {
                            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                        }
                    }
                    break;
                default:
                    $GLOBALS['order']->info['order_status'] = DEFAULT_ORDERS_STATUS_ID;
            }

            $order_status_query = tep_db_query("SELECT orders_status_name FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_id = '" . $GLOBALS['order']->info['order_status'] . "' AND language_id = '" . $GLOBALS['languages_id'] . "'");
            $order_status = tep_db_fetch_array($order_status_query);

            $GLOBALS['order']->info['orders_status'] = $order_status['orders_status_name'];

            if ($old_order_status != $new_status)
            {
                tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = " . $new_status . " WHERE orders_id = " . $this->order_id);
            }

            if ($notify_customer)
            {
                $this->_notify_customer($new_status);
            } else
            {
                $last_osh_status_r = tep_db_fetch_array(tep_db_query("SELECT orders_status_id FROM orders_status_history WHERE orders_id = '" . tep_db_input($this->order_id) . "' ORDER BY date_added DESC limit 1"));
                if ($last_osh_status_r['orders_status_id'] != $GLOBALS['order']->info['order_status'])
                {
                    $sql_data_array = array('orders_id' => $this->order_id,
                        'orders_status_id' => $GLOBALS['order']->info['order_status'],
                        'date_added' => 'now()',
                        'customer_notified' => 0,
                    );

                    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                }
            }

            // reset cart
            if ($reset_cart)
            {
                tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = '" . (int) $GLOBALS['order']->customer['id'] . "'");
                tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id = '" . (int) $GLOBALS['order']->customer['id'] . "'");
            }

            return $status;
        }

        /**
         * 
         * @param type $code
         * @return type
         */
        function _get_error_message($message)
        {
            return $message;
        }

        /**
         * 
         * @param type $error
         */
        function _error_redirect($error)
        {
            tep_redirect($this->_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . $error, 'NONSSL', true, false, false));
        }

        /**
         * 
         * @global type $customer_id
         * @global type $languages_id
         * @global type $order
         * @global type $shipping
         * @global type $order_totals
         * @global type $order_products_id
         * @return type
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
                'orders_status' => $order->info['order_status'],
                'currency' => $GLOBALS['order']->info['currency'],
                'currency_value' => $order->info['currency_value']);

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

                $attributes_exist = '0';
                $products_ordered_attributes = '';
                if (isset($order->products[$i]['attributes']))
                {
                    $attributes_exist = '1';
                    for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++)
                    {
                        if (DOWNLOAD_ENABLED == 'true')
                        {
                            $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, 
                                                pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
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
                            $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, "
                                    . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '"
                                    . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id']
                                    . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
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

            if (SEND_EMAILS == 'true')
            {
                $customer_notification = '1';
            } else
            {
                $customer_notification = '0';
            }

            $sql_data_array = array('orders_id' => $this->order_id,
                'orders_status_id' => $order->info['order_status'], 'date_added' => 'now()',
                'customer_notified' => $customer_notification,
                'comments' => $order->info['comments']);
            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

            // initialized for the email confirmation
            $products_ordered = '';
            $total_weight = 0;
            $total_tax = 0;
            $total_cost = 0;

            for ($i = 0, $n = sizeof($order->products); $i < $n; $i++)
            {
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
                                $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, 
                                    pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
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

            $email_order = STORE_NAME . "\n" .
                    EMAIL_SEPARATOR . "\n" .
                    MODULE_PAYMENT_MULTISAFEPAY_EMAIL_TEXT_ORDER_STATUS . ' ' . $order->info['orders_status'] . "\n" .
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
            tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            // send emails to other people
            if (SEND_EXTRA_ORDER_EMAILS_TO != '')
            {
                tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
        }

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

        /**
         * 
         * @return type
         */
        function check()
        {
            if (!isset($this->_check))
            {
                $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MULTISAFEPAY_STATUS'");
                $this->_check = tep_db_num_rows($check_query);
            }
            return $this->_check;
        }

        /*
         * Installs the configuration keys into the database
         */

        function install()
        {
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('MultiSafepay enabled', 'MODULE_PAYMENT_MULTISAFEPAY_STATUS', 'True', 'Enable MultiSafepay payments for this website', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Type account', 'MODULE_PAYMENT_MULTISAFEPAY_API_SERVER', 'Live account', '<a href=\'http://www.multisafepay.com/nl/klantenservice-zakelijk/open-een-testaccount.html\' target=\'_blank\' style=\'text-decoration: underline; font-weight: bold; color:#696916; \'>Sign up for a free test account!</a>', '6', '21', 'tep_cfg_select_option(array(\'Live account\', \'Test account\'), ', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('API Key', 'MODULE_PAYMENT_MULTISAFEPAY_API_KEY', '', 'Your MultiSafepay API Key', '6', '22', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Auto Redirect', 'MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT', 'True', 'Enable auto redirect after payment', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_MULTISAFEPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '25', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Daysactive', 'MODULE_PAYMENT_MULTISAFEPAY_DAYS_ACTIVE', '', 'The number of days a paymentlink remains active.', '6', '22', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Google Analytics', 'MODULE_PAYMENT_MULTISAFEPAY_GA', '', 'Google Analytics Account ID', '6', '22', now())");

            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Initialized Order Status', 'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED', 0, 'In progress', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Completed Order Status',   'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED',   0, 'Completed successfully', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Uncleared Order Status',   'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED',   0, 'Not yet cleared', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Reserved Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED',    0, 'Reserved', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Voided Order Status',      'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID',        0, 'Cancelled', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Declined Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED',    0, 'Declined (e.g. fraud, not enough balance)', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Reversed Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED',    0, 'Undone', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refunded Order Status',    'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED',    0, 'refunded', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Expired Order Status',     'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED',     0, 'Expired', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Partial refunded Order Status',     'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED',     0, 'Partial Refunded', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable payment method icons', 'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED', 'False', 'Enable payment method icons in front of the title', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
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

        /**
         * 
         * @return type
         */
        function keys()
        {
            return array(
                'MODULE_PAYMENT_MULTISAFEPAY_STATUS',
                'MODULE_PAYMENT_MULTISAFEPAY_API_SERVER',
                'MODULE_PAYMENT_MULTISAFEPAY_API_KEY',
                'MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT',
                'MODULE_PAYMENT_MULTISAFEPAY_ZONE',
                'MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER',
                'MODULE_PAYMENT_MULTISAFEPAY_DAYS_ACTIVE',
                'MODULE_PAYMENT_MULTISAFEPAY_GA',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED',
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED',
                'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED',
            );
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
         * @param type $admin
         * @return type
         */
        function getTitle($admin = 'title')
        {

            if (MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED != 'False')
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
            }

            return $title;
        }

        /**
         * 
         * @param type $str
         * @return type
         */
        function getLangStr($str)
        {
            switch ($str)
            {
                //Payment methods
                case "title":
                    return MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE;
                case "iDEAL":
                    return MODULE_PAYMENT_MSP_IDEAL_TEXT_TITLE;
                case "Banktransfer":
                    return MODULE_PAYMENT_MSP_BANKTRANS_TEXT_TITLE;
                case "Giropay":
                    return MODULE_PAYMENT_MSP_GIROPAY_TEXT_TITLE;
                case "VISA":
                    return MODULE_PAYMENT_MSP_VISA_TEXT_TITLE;
                case "American Express":
                    return MODULE_PAYMENT_MSP_AMEX_TEXT_TITLE;
                case "Direct Debit":
                    return MODULE_PAYMENT_MSP_DIRDEB_TEXT_TITLE;
                case "Bancontact":
                    return MODULE_PAYMENT_MSP_BANCONTACT_TEXT_TITLE;
                case "MasterCard":
                    return MODULE_PAYMENT_MSP_MASTERCARD_TEXT_TITLE;
                case "PayPal":
                    return MODULE_PAYMENT_MSP_PAYPAL_TEXT_TITLE;
                case "Maestro":
                    return MODULE_PAYMENT_MSP_MAESTRO_TEXT_TITLE;
                case "SOFORT Banking":
                    return MODULE_PAYMENT_MSP_SOFORT_TEXT_TITLE;
                case "Ferbuy":
                    return MODULE_PAYMENT_MSP_FERBUY_TEXT_TITLE;
                case "EPS":
                    return MODULE_PAYMENT_MSP_EPS_TEXT_TITLE;
                case "Dotpay":
                    return MODULE_PAYMENT_MSP_DOTPAY_TEXT_TITLE;
                case "Paysafecard":
                    return MODULE_PAYMENT_MSP_PAYSAFECARD_TEXT_TITLE;
                case "E-Invoice":
                    return MODULE_PAYMENT_MSP_EINVOICE_TEXT_TITLE;
                case "Klarna":
                    return MODULE_PAYMENT_MSP_KLARNA_TEXT_TITLE;
                //Giftcards
                case "Boekenbon":
                    return MODULE_PAYMENT_MSP_BOEKENBON_TEXT_TITLE;
                case "De Grote Speelgoedwinkel":
                    return MODULE_PAYMENT_MSP_DEGROTESPEELGOEDWINKEL_TEXT_TITLE;
                case "Erotiekbon":
                    return MODULE_PAYMENT_MSP_EROTIEKBON_TEXT_TITLE;
                case "Webshopgiftcard":
                    return MODULE_PAYMENT_MSP_WEBSHOPGIFTCARD_TEXT_TITLE;
                case "ParfumNL":
                    return MODULE_PAYMENT_MSP_PARFUMNL_TEXT_TITLE;
                case "Parfumcadeaukaart":
                    return MODULE_PAYMENT_MSP_PARFUMCADEAUKAART_TEXT_TITLE;
                case "Gezondheidsbon":
                    return MODULE_PAYMENT_MSP_GEZONDHEIDSBON_TEXT_TITLE;
                case "FashionCheque":
                    return MODULE_PAYMENT_MSP_FASHIONCHEQUE_TEXT_TITLE;
                case "Fashion Giftcard":
                    return MODULE_PAYMENT_MSP_FASHIONGIFTCARD_TEXT_TITLE;
                case "Lief! cadeaukaart":
                    return MODULE_PAYMENT_MSP_LIEF_TEXT_TITLE;
                case "Bloemencadeau":
                    return MODULE_PAYMENT_MSP_BLOEMENCADEAU_TEXT_TITLE;
                case "Brouwmarkt":
                    return MODULE_PAYMENT_MSP_BROUWMARKT_TEXT_TITLE;
                case "Fietsenbon":
                    return MODULE_PAYMENT_MSP_FIETSENBON_TEXT_TITLE;
                case "Givacard":
                    return MODULE_PAYMENT_MSP_GIVACARD_TEXT_TITLE;
                case "Goodcard":
                    return MODULE_PAYMENT_MSP_GOODCARD_TEXT_TITLE;
                case "Jewelstore":
                    return MODULE_PAYMENT_MSP_JEWELSTORE_TEXT_TITLE;
                case "Kelly Giftcard":
                    return MODULE_PAYMENT_MSP_KELLYGIFTCARD_TEXT_TITLE;
                case "Nationale Tuinbon":
                    return MODULE_PAYMENT_MSP_NATIONALETUINBON_TEXT_TITLE;
                case "Podium":
                    return MODULE_PAYMENT_MSP_PODIUM_TEXT_TITLE;
                case "Sport & Fit":
                    return MODULE_PAYMENT_MSP_SPORTNFIT_TEXT_TITLE;
                case "VVV Giftcard":
                    return MODULE_PAYMENT_MSP_VVVGIFTCARD_TEXT_TITLE;
                case "Wellness Giftcard":
                    return MODULE_PAYMENT_MSP_WELLNESSGIFTCARD_TEXT_TITLE;
                case "Wijncadeaukaart":
                    return MODULE_PAYMENT_MSP_WIJNCADEAUKAART_TEXT_TITLE;
                case "Winkelcheque":
                    return MODULE_PAYMENT_MSP_WINKELCHEQUE_TEXT_TITLE;
                case "Yourgift":
                    return MODULE_PAYMENT_MSP_YOURGIFT_TEXT_TITLE;
                case "Beauty and wellness":
                    return MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_TEXT_TITLE;
                case MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE:
                    return MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE;
            }
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
                if ($this->getScriptName() == 'checkout_payment.php')
                {
                    $view = "checkout";
                } else
                {
                    $view = "frontend";
                }
            }
            return $view;
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
         * @return string
         */
        function getIcon()
        {
            $icon = DIR_WS_IMAGES . "multisafepay/en/" . $this->icon;

            if (file_exists(DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon))
            {
                $icon = DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon;
            }
            return $icon;
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

    }

}
?>