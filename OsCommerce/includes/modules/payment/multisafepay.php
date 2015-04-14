<?php

/*

  MultiSafepay Payment Module (1.20) for osCommerce
  http://www.multisafepay.com

  Copyright (C) 2008 MultiSafepay.com

 */

$dir = dirname(__FILE__);
require_once($dir . "/mspcheckout/include/MultiSafepay.combined.php");

if (!class_exists('multisafepay')) {

    class multisafepay {

        var $code;
        var $title;
        var $description;
        var $enabled;
        var $sort_order;
        var $plugin_name;
        var $icon = "msp.gif";
        var $api_url;
        var $order_id;
        var $public_title;
        var $status;
        var $shipping_methods = array();
        var $taxes = array();
        var $msp;

        /*
         * Constructor
         */

        function multisafepay($order_id = -1) {
            global $order;
            $this->code = 'multisafepay';
            $this->title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE);
            $this->description = MODULE_PAYMENT_MULTISAFEPAY_TEXT_DESCRIPTION;
            $this->enabled = MODULE_PAYMENT_MULTISAFEPAY_STATUS == 'True';
            $this->sort_order = MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER;
            $this->plugin_name = 'Plugin 2.0.1 (' . PROJECT_VERSION . ')';

            if (is_object($order))
                $this->update_status();

            // new configuration value
            if (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live' || MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live account') {
                $this->api_url = 'https://api.multisafepay.com/ewx/';
            } else {
                $this->api_url = 'https://testapi.multisafepay.com/ewx/';
            }

            $this->order_id = $order_id;
            $this->public_title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE);
            $this->status = null;
        }

        /*
         * Check whether this payment module is available
         */

        function update_status() {
            global $order;

            if (($this->enabled == true) && ((int) MODULE_PAYMENT_MULTISAFEPAY_ZONE > 0)) {
                $check_flag = false;
                $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MULTISAFEPAY_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
                while ($check = tep_db_fetch_array($check_query)) {
                    if ($check['zone_id'] < 1) {
                        $check_flag = true;
                        break;
                    } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                        $check_flag = true;
                        break;
                    }
                }

                if ($check_flag == false) {
                    $this->enabled = false;
                }
            }
        }

        // ---- select payment module ----

        /*
         * Client side javascript that will verify any input fields you use in the
         * payment method selection page
         */
        function javascript_validation() {
            return false;
        }

        /*
         * Outputs the payment method title/text and if required, the input fields
         */

        function selection() {
            global $customer_id;
            global $languages_id;
            global $order;
            global $order_totals;
            global $order_products_id;

            $selection = array('id' => $this->code,
                'module' => $this->public_title,
                'fields' => array());
            return $selection;
        }

        /*
         * Any checks of any conditions after payment method has been selected
         */

        function pre_confirmation_check() {
            if (MODULE_PAYMENT_MULTISAFEPAY_GATEWAY_SELECTION == 'True') {
                $gatewaytest = $_POST['multisafepay_gateway_selection'];
                if (!$gatewaytest) {
                    //$error = 'Selecteer een Gateway';
                    //$payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error);
                    //tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
                }
                $this->gateway_selection = $_POST['multisafepay_gateway_selection'];
            } else {
                return false;
            }
        }

        // ---- confirm order ----

        /*
         * Any checks or processing on the order information before proceeding to
         * payment confirmation
         */
        function confirmation() {
            global $HTTP_POST_VARS, $order;

            return false;
        }

        /*
         * Outputs the html form hidden elements sent as POST data to the payment
         * gateway
         */

        function process_button() {
            if (MODULE_PAYMENT_MULTISAFEPAY_GATEWAY_SELECTION == 'True') {
                $fields = tep_draw_hidden_field('multisafepay_gateway_selection', $_POST['multisafepay_gateway_selection']);
                return $fields;
            } else {
                return false;
            }
        }

        // ---- process payment ----

        /*
         * Payment verification
         */
        function before_process() {
            $this->_save_order();
            tep_redirect($this->_start_transaction());
        }

        /*
         * Post-processing of the payment/order after the order has been finalised
         */

        function after_process() {
            return false;
        }

        // ---- error handling ----

        /*
         * Advanced error handling
         */
        function output_error() {
            return false;
        }

        function get_error() {
            $error = array(
                'title' => MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR,
                'error' => $this->_get_error_message($_GET['error'])
            );

            return $error;
        }

        // ---- MultiSafepay ----

        /*
         * Starts a new transaction and returns the redirect URL
         */
        function _start_transaction() {
            //$amount								= 	$this->convertEuro($GLOBALS['order']->info['total']);
            //$amount 							= 	$GLOBALS['order']->info['total'] * 100;


            $amount = round($GLOBALS['order']->info['total'], 2) * 100;
            //echo $amount;exit;
            // generate items list
            $items = "<ul>\n";
            foreach ($GLOBALS['order']->products as $product) {
                $items .= "<li>" . $product['name'] . "</li>\n";
            }
            $items .= "</ul>\n";

            // start transaction
            $this->msp = new MultiSafepayAPI();
            $this->msp->plugin_name = 'Plugin 2.0.1 (' . PROJECT_VERSION . ')';
            $this->msp->test = (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live account');
            $this->msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID;
            $this->msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_ID;
            $this->msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE;
            $this->msp->merchant['notification_url'] = $this->_href_link('ext/modules/payment/multisafepay/notify_checkout.php?type=initial', '', 'SSL', false, false);
            //$this->msp->merchant['cancel_url']       	= 	$this->_href_link('checkout_shipping.php', '', 'SSL', false, false);
            $this->msp->merchant['cancel_url'] = $this->_href_link('ext/modules/payment/multisafepay/cancel.php', '', 'SSL', false, false);


            if ($_POST['msp_paymentmethod']) {
                $this->msp->transaction['gateway'] = $_POST['msp_paymentmethod'];
            }

            if ($_POST["msp_issuer"]) {
                $this->msp->extravars = $_POST["msp_issuer"];
            }

            if (MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT == "True") {
                $this->msp->merchant['redirect_url'] = $this->_href_link('ext/modules/payment/multisafepay/success.php', '', 'SSL', false, false);
            }

            $this->msp->customer['locale'] = strtolower($GLOBALS['order']->delivery['country']['iso_code_2']) . '_' . $GLOBALS['order']->delivery['country']['iso_code_2'];
            $this->msp->customer['firstname'] = $GLOBALS['order']->customer['firstname'];
            $this->msp->customer['lastname'] = $GLOBALS['order']->customer['lastname'];
            $this->msp->customer['zipcode'] = $GLOBALS['order']->customer['postcode'];
            $this->msp->customer['city'] = $GLOBALS['order']->customer['city'];
            $this->msp->customer['country'] = $GLOBALS['order']->customer['country']['iso_code_2'];
            $this->msp->customer['phone'] = $GLOBALS['order']->customer['telephone'];
            $this->msp->customer['email'] = $GLOBALS['order']->customer['email_address'];
            $this->msp->parseCustomerAddress($GLOBALS['order']->customer['street_address']);

            $this->msp->transaction['id'] = $this->order_id;
            $this->msp->transaction['currency'] = $GLOBALS['order']->info['currency'];
            $this->msp->transaction['amount'] = round($amount);
            $this->msp->transaction['description'] = 'Order #' . $this->order_id . ' at ' . STORE_NAME;
            $this->msp->transaction['items'] = $items;


            if ($_POST["msp_issuer"]) {
                $this->msp->extravars = $_POST["msp_issuer"];
                $url = $this->msp->startDirectXMLTransaction();
            } else {
                $url = $this->msp->startTransaction();
            }


            if ($this->msp->error) {
                $this->_error_redirect($this->msp->error_code . ": " . $this->msp->error);
                exit();
            }

            return $url;
        }

        /* function convertEuro($value, $round = true)
          {
          $currency  							= 	'EUR';
          $rate      							= 	$GLOBALS['currencies']->currencies[$currency]['value'];
          $new_total 							= 	$value * $rate;

          if ($round)
          {
          $new_total = tep_round($new_total, 2);
          }
          return $new_total;
          } */

        function check_transaction() {
            $this->msp = new MultiSafepayAPI();
            $this->msp->plugin_name = $this->plugin_name;
            $this->msp->test = (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live account');
            $this->msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID;
            $this->msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_ID;
            $this->msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE;
            $this->msp->transaction['id'] = $this->order_id;
            $status = $this->msp->getStatus();

            if ($this->msp->error) {
                return $this->msp->error_code;
            }


            return $status;
        }

        function cancel() {
            
        }

        /*
         * Checks current order status and updates the database
         */

        function checkout_notify() {
            $this->msp = new MultiSafepayAPI();
            $this->msp->plugin_name = 'Plugin 2.0.1 (' . PROJECT_VERSION . ')';
            $this->msp->test = (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live account');
            $this->msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID;
            $this->msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_ID;
            $this->msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE;
            $this->msp->transaction['id'] = $this->order_id;
            $status = $this->msp->getStatus();

            if ($this->msp->error) {
                return $this->msp->error_code;
            }

            // determine status
            $reset_cart = false;
            $notify_customer = false;

            $current_order = tep_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = " . $this->order_id);
            $current_order = tep_db_fetch_array($current_order);
            $old_order_status = $current_order['orders_status'];
            $new_stat = $old_order_status;

            switch ($status) {
                case "initialized":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED;
                    $reset_cart = true;
                    break;
                case "completed":
                    if (in_array($old_order_status, array(MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED, DEFAULT_ORDERS_STATUS_ID, MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED))) {
                        $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED;
                        $reset_cart = true;
                        if ($old_order_status != $GLOBALS['order']->info['order_status']) {
                            $notify_customer = true;
                        }
                        $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED;
                    }
                    break;
                case "uncleared":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED;
                    break;
                case "reserved":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED;
                    break;
                case "void":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID) {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

                        while ($order = tep_db_fetch_array($order_query)) {
                            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                        }
                    }
                    break;
                case "cancelled":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID) {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

                        while ($order = tep_db_fetch_array($order_query)) {
                            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                        }
                    }
                    break;
                case "declined":

                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED) {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

                        while ($order = tep_db_fetch_array($order_query)) {
                            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
                        }
                    }
                    break;
                case "reversed":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED;
                    break;
                case "refunded":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED;
                    break;
                case "partial_refunded":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED;
                    break;
                case "expired":
                    $GLOBALS['order']->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED) {
                        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

                        while ($order = tep_db_fetch_array($order_query)) {
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



            if ($old_order_status != $new_stat) {
                // update order
                tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = " . $new_stat . " WHERE orders_id = " . $this->order_id);
            }



            if ($notify_customer) {

                $this->_notify_customer($new_stat);
            } else {
                // if we don't inform the customer about the update, check if there's a new status. If so, update the order_status_history table accordingly
                $last_osh_status_r = tep_db_fetch_array(tep_db_query("SELECT orders_status_id FROM orders_status_history WHERE orders_id = '" . tep_db_input($this->order_id) . "' ORDER BY date_added DESC limit 1"));
                if ($last_osh_status_r['orders_status_id'] != $GLOBALS['order']->info['order_status']) {
                    $sql_data_array = array('orders_id' => $this->order_id,
                        'orders_status_id' => $GLOBALS['order']->info['order_status'],
                        'date_added' => 'now()',
                        'customer_notified' => 0,
                    );

                    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                }
            }

            // reset cart
            if ($reset_cart) {
                tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = '" . (int) $GLOBALS['order']->customer['id'] . "'");
                tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id = '" . (int) $GLOBALS['order']->customer['id'] . "'");
            }

            return $status;
        }

        function _get_error_message($code) {
            if (is_numeric($code)) {
                $message = constant(sprintf("MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_%04d", $code));

                if (!$message) {
                    $message = MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_UNKNOWN;
                }
            } else {
                $const = sprintf("MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_%s", strtoupper($code));
                if (defined($const)) {
                    $message = constant($const);
                } else {
                    $message = $code;
                }
            }
            return $message;
        }

        function _error_redirect($error) {
            tep_redirect($this->_href_link(
                            FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . $error, 'NONSSL', true, false, false
            ));
        }

        // ---- Ripped from checkout_process.php ----

        /*
         * Store the order in the database, and set $this->order_id
         */
        function _save_order() {
            global $customer_id;
            global $languages_id;
            global $order;
            global $shipping;
            global $order_totals;
            global $order_products_id;

            if (empty($order_totals)) {
                require(DIR_WS_CLASSES . 'order_total.php');
                $order_total_modules = new order_total();
                $order_totals = $order_total_modules->process();
            }

            if (!empty($this->order_id) && $this->order_id > 0) {
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
                //'shipping_module'                    =>    $shipping['id'],
                //'orders_status' 					=> 	MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED,
                'currency' => $GLOBALS['order']->info['currency'],
                'currency_value' => $order->info['currency_value']);

            tep_db_perform(TABLE_ORDERS, $sql_data_array);
            $insert_id = tep_db_insert_id();

            for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
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

            for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                // Stock Update - Joao Correia
                if (STOCK_LIMITED == 'true') {
                    if (DOWNLOAD_ENABLED == 'true') {
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
                        if (is_array($products_attributes)) {
                            $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
                        }
                        $stock_query = tep_db_query($stock_query_raw);
                    } else {
                        $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    }
                    if (tep_db_num_rows($stock_query) > 0) {
                        $stock_values = tep_db_fetch_array($stock_query);
                        // do not decrement quantities if products_attributes_filename exists
                        if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
                            $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
                        } else {
                            $stock_left = $stock_values['products_quantity'];
                        }
                        tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                        if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false')) {
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
                if (isset($order->products[$i]['attributes'])) {
                    $attributes_exist = '1';
                    for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                        if (DOWNLOAD_ENABLED == 'true') {
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
                        } else {
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

                        if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
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

        function _notify_customer($new_order_status = null) {
            global $customer_id;
            global $order;
            global $order_totals;
            global $order_products_id;
            global $total_products_price;
            global $products_tax;
            global $languages_id;
            global $currencies;
            global $payment;

            if ($new_order_status != null) {

                $order->info['order_status'] = $new_order_status;
            }


            $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
            $sql_data_array = array('orders_id' => $this->order_id,
                'orders_status_id' => $order->info['order_status'],
                'date_added' => 'now()',
                'customer_notified' => $customer_notification,
                'comments' => $order->info['comments']);
            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

            // initialized for the email confirmation
            $products_ordered = '';
            $total_weight = 0;
            $total_tax = 0;
            $total_cost = 0;

            for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                //------insert customer choosen option to order--------
                $attributes_exist = '0';
                $products_ordered_attributes = '';
                if (isset($order->products[$i]['attributes'])) {
                    $attributes_exist = '1';
                    for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                        if (isset($order->products[$i]['attributes'][$j]['option_id'])) {
                            if (DOWNLOAD_ENABLED == 'true') {
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
                            } else {
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
                        } else {
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

                        if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
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
                    MODULE_PAYMENT_MULTISAFEPAY_EMAIL_TEXT_ORDER_STATUS . ' ' . $order->info['orders_status'] . "\n" .
                    EMAIL_TEXT_ORDER_NUMBER . ' ' . $this->order_id . "\n" .
                    EMAIL_TEXT_INVOICE_URL . ' ' . $this->_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false) . "\n" .
                    EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
            if ($order->info['comments']) {
                $email_order .= tep_db_output($order->info['comments']) . "\n\n";
            }
            $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                    EMAIL_SEPARATOR . "\n" .
                    $products_ordered .
                    EMAIL_SEPARATOR . "\n";

            for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
                $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
            }

            if ($order->content_type != 'virtual') {
                $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        $this->_address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n") . "\n";
            }

            $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                    EMAIL_SEPARATOR . "\n" .
                    $this->_address_format($order->billing['format_id'], $order->billing, 0, '', "\n") . "\n\n";
            if (is_object($$payment)) {
                $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                        EMAIL_SEPARATOR . "\n";
                $payment_class = $$payment;
                if (!empty($order->info['payment_method'])) {
                    $email_order .= $order->info['payment_method'] . "\n\n";
                } else {
                    $email_order .= $payment_class->title . "\n\n";
                }
                if ($payment_class->email_footer) {
                    $email_order .= $payment_class->email_footer . "\n\n";
                }
            }
            tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            // send emails to other people
            if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
                tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
        }

        // ---- Ripped from includes/functions/general.php ----

        function _address_format($address_format_id, $address, $html, $boln, $eoln) {
            $address_format_query = tep_db_query("SELECT address_format AS format FROM " . TABLE_ADDRESS_FORMAT . " WHERE address_format_id = '" . (int) $address_format_id . "'");
            $address_format = tep_db_fetch_array($address_format_query);

            $company = $this->_output_string_protected($address['company']);
            if (isset($address['firstname']) && tep_not_null($address['firstname'])) {
                $firstname = $this->_output_string_protected($address['firstname']);
                $lastname = $this->_output_string_protected($address['lastname']);
            } elseif (isset($address['name']) && tep_not_null($address['name'])) {
                $firstname = $this->_output_string_protected($address['name']);
                $lastname = '';
            } else {
                $firstname = '';
                $lastname = '';
            }
            $street = $this->_output_string_protected($address['street_address']);
            $suburb = $this->_output_string_protected($address['suburb']);
            $city = $this->_output_string_protected($address['city']);
            $state = $this->_output_string_protected($address['state']);
            if (isset($address['country_id']) && tep_not_null($address['country_id'])) {
                $country = tep_get_country_name($address['country_id']);
                if (isset($address['zone_id']) && tep_not_null($address['zone_id'])) {
                    $state = tep_get_zone_code($address['country_id'], $address['zone_id'], $state);
                }
            } elseif (isset($address['country']) && tep_not_null($address['country'])) {
                if (is_array($address['country'])) {
                    $country = $this->_output_string_protected($address['country']['title']);
                } else {
                    $country = $this->_output_string_protected($address['country']);
                }
            } else {
                $country = '';
            }
            $postcode = $this->_output_string_protected($address['postcode']);
            $zip = $postcode;

            if ($html) {
                // HTML Mode
                $HR = '<hr>';
                $hr = '<hr>';
                if (($boln == '') && ($eoln == "\n")) { // Values not specified, use rational defaults
                    $CR = '<br>';
                    $cr = '<br>';
                    $eoln = $cr;
                } else { // Use values supplied
                    $CR = $eoln . $boln;
                    $cr = $CR;
                }
            } else {
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

            if ((ACCOUNT_COMPANY == 'true') && (tep_not_null($company))) {
                $address = $company . $cr . $address;
            }
            return $address;
        }

        function _output_string($string, $translate = false, $protected = false) {
            if ($protected == true) {
                return htmlspecialchars($string);
            } else {
                if ($translate == false) {
                    return $this->_parse_input_field_data($string, array('"' => '&quot;'));
                } else {
                    return $this->_parse_input_field_data($string, $translate);
                }
            }
        }

        function _output_string_protected($string) {
            return $this->_output_string($string, false, true);
        }

        function _parse_input_field_data($data, $parse) {
            return strtr(trim($data), $parse);
        }

        function _href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $unused = true, $escape_html = true) {
            global $request_type, $session_started, $SID;

            unset($unused);

            if (!tep_not_null($page)) {
                die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
            }

            if ($connection == 'NONSSL') {
                $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
            } elseif ($connection == 'SSL') {
                if (ENABLE_SSL == true) {
                    $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
                } else {
                    $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
                }
            } else {
                die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
            }

            if (tep_not_null($parameters)) {
                if ($escape_html) {
                    $link .= $page . '?' . $this->_output_string($parameters);
                } else {
                    $link .= $page . '?' . $parameters;
                }
                $separator = '&';
            } else {
                $link .= $page;
                $separator = '?';
            }

            while ((substr($link, -1) == '&') || (substr($link, -1) == '?'))
                $link = substr($link, 0, -1);

            // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
            if (($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False')) {
                if (tep_not_null($SID)) {
                    $_sid = $SID;
                } elseif (( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') )) {
                    if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
                        $_sid = tep_session_name() . '=' . tep_session_id();
                    }
                }
            }

            if (isset($_sid)) {
                if ($escape_html) {
                    $link .= $separator . $this->_output_string($_sid);
                } else {
                    $link .= $separator . $_sid;
                }
            }
            return $link;
        }

        // ---- installation & configuration ----

        /*
         * Checks whether the payment has been “installed” through the admin panel
         */
        function check() {
            if (!isset($this->_check)) {
                $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MULTISAFEPAY_STATUS'");
                $this->_check = tep_db_num_rows($check_query);
            }
            return $this->_check;
        }

        /*
         * Installs the configuration keys into the database
         */

        function install() {
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('MultiSafepay enabled', 'MODULE_PAYMENT_MULTISAFEPAY_STATUS', 'True', 'Enable MultiSafepay payments for this website', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Type account', 'MODULE_PAYMENT_MULTISAFEPAY_API_SERVER', 'Live account', '<a href=\'http://www.multisafepay.com/nl/klantenservice-zakelijk/open-een-testaccount.html\' target=\'_blank\' style=\'text-decoration: underline; font-weight: bold; color:#696916; \'>Sign up for a free test account!</a>', '6', '21', 'tep_cfg_select_option(array(\'Live account\', \'Test account\'), ', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Account ID', 'MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID', '', 'Your merchant account ID', '6', '22', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Site ID', 'MODULE_PAYMENT_MULTISAFEPAY_SITE_ID', '', 'ID of this site', '6', '23', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Site Code', 'MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE', '', 'Site code for this site', '6', '24', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Auto Redirect', 'MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT', 'True', 'Enable auto redirect after payment', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_MULTISAFEPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '25', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

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
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable gateway titles in checkout', 'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ENABLER', 'True', 'Enable the gateway title in checkout', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
            tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable icons in gateway titles. If disabled it will overrule option above.', 'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED', 'True', 'Enable the icon in the checkout title for the gateway', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        }

        /*
         * Removes the configuration keys from the database
         */

        function remove() {
            tep_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
        }

        /*
         * Defines an array containing the configuration key keys that are used by
         * the payment module
         */

        function keys() {
            return array(
                'MODULE_PAYMENT_MULTISAFEPAY_STATUS',
                'MODULE_PAYMENT_MULTISAFEPAY_API_SERVER',
                'MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID',
                'MODULE_PAYMENT_MULTISAFEPAY_SITE_ID',
                'MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE',
                'MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT',
                'MODULE_PAYMENT_MULTISAFEPAY_ZONE',
                'MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER',
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
                'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ENABLER',
                'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED',
            );
        }

        function getScriptName() {

            global $PHP_SELF;

            return basename($PHP_SELF);
            /*
              if (isset($_SERVER["SCRIPT_NAME"])){
              $file 	= $_SERVER["SCRIPT_NAME"];
              $break 	= Explode('/', $file);
              $file 	= $break[count($break) - 1];
              };

              return $file;
             */
        }

        function getTitle($admin = 'title') {

            if (MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED != 'False') {

                $title = ($this->checkView() == "checkout") ? $this->generateIcon($this->getIcon()) . " " : "";
            } else {
                $title = "";
            }


            $title .= ($this->checkView() == "admin") ? "MultiSafepay - " : "";
            if ($admin && $this->checkView() == "admin") {
                $title .= $admin;
            } else {
                $title .= $this->getLangStr($admin);
            };
            return $title;
        }

        function getLangStr($str) {
            if (MODULE_PAYMENT_MULTISAFEPAY_TITLES_ENABLER == "True" || MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED == 'False') {
                switch ($str) {
                    case "title":
                        return MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE;
                    case "iDEAL":
                        return MODULE_PAYMENT_MSP_IDEAL_TEXT_TITLE;
                    case "Bank transfer":
                        return MODULE_PAYMENT_MSP_BANKTRANS_TEXT_TITLE;
                    case "GiroPay":
                        return MODULE_PAYMENT_MSP_GIROPAY_TEXT_TITLE;
                    case "VISA":
                        return MODULE_PAYMENT_MSP_VISA_TEXT_TITLE;
                    case "AMEX":
                        return MODULE_PAYMENT_MSP_AMEX_TEXT_TITLE;
                    case "DirectDebit":
                        return MODULE_PAYMENT_MSP_DIRDEB_TEXT_TITLE;
                    case "Bancontact/Mistercash":
                        return MODULE_PAYMENT_MSP_MISTERCASH_TEXT_TITLE;
                    case "MasterCard":
                        return MODULE_PAYMENT_MSP_MASTERCARD_TEXT_TITLE;
                    case "PAYPAL":
                        return MODULE_PAYMENT_MSP_PAYPAL_TEXT_TITLE;
                    case "Maestro":
                        return MODULE_PAYMENT_MSP_MAESTRO_TEXT_TITLE;
                    case "SOFORT Banking":
                        return MODULE_PAYMENT_MSP_DIRECTBANK_TEXT_TITLE;
                    case "BABYGIFTCARD":
                        return MODULE_PAYMENT_MSP_BABYGIFTCARD_TEXT_TITLE;
                    case "BOEKENBON":
                        return MODULE_PAYMENT_MSP_BOEKENBON_TEXT_TITLE;
                    case "DEGROTESPEELGOEDWINKEL":
                        return MODULE_PAYMENT_MSP_DEGROTESPEELGOEDWINKEL_TEXT_TITLE;
                    case "EBON":
                        return MODULE_PAYMENT_MSP_EBON_TEXT_TITLE;
                    case "EROTIEKBON":
                        return MODULE_PAYMENT_MSP_EROTIEKBON_TEXT_TITLE;
                    case "LIEF":
                        return MODULE_PAYMENT_MSP_LIEF_TEXT_TITLE;
                    case "WEBSHOPGIFTCARD":
                        return MODULE_PAYMENT_MSP_WEBSHOPGIFTCARD_TEXT_TITLE;
                    case "PARFUMNL":
                        return MODULE_PAYMENT_MSP_PARFUMNL_TEXT_TITLE;
                    case "PARFUMCADEAUKAART":
                        return MODULE_PAYMENT_MSP_PARFUMCADEAUKAART_TEXT_TITLE;
                    case "GEZONDHEIDSBON":
                        return MODULE_PAYMENT_MSP_GEZONDHEIDSBON_TEXT_TITLE;
                    case "FASHIONCHEQUE":
                        return MODULE_PAYMENT_MSP_FASHIONCHEQUE_TEXT_TITLE;
                    case MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE:
                        return MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE;
                        break;
                }
            }
        }

        function checkView() {
            $view = "admin";

            if (!tep_session_is_registered('admin')) {
                if ($this->getScriptName() == FILENAME_CHECKOUT_PAYMENT) {
                    $view = "checkout";
                } else {
                    $view = "frontend";
                }
            }
            return $view;
        }

        function generateIcon($icon) {
            return tep_image($icon);
        }

        function getIcon() {
            $icon = DIR_WS_IMAGES . "multisafepay/en/" . $this->icon;

            if (file_exists(DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon)) {
                $icon = DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon;
            }
            return $icon;
        }

        function getUserLanguage($savedSetting) {
            if ($savedSetting != "DETECT") {
                return $savedSetting;
            }

            global $languages_id;

            $query = tep_db_query("select languages_id, name, code, image, directory from " . TABLE_LANGUAGES . " where languages_id = " . (int) $languages_id . " limit 1");
            if ($languages = tep_db_fetch_array($query)) {
                return strtoupper($languages['code']);
            }

            return "EN";
        }

        function getlocale($lang) {
            switch ($lang) {
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
                    $lang = 'en_EN';
                    break;
                default:
                    $lang = 'en_EN';
                    break;
            }
            return $lang;
        }

        function getcountry($country) {
            if (empty($country)) {
                $langcode = explode(";", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                $langcode = explode(",", $langcode['0']);
                return strtoupper($langcode['1']);
            } else {
                return strtoupper($country);
            }
        }

    }

}
?>