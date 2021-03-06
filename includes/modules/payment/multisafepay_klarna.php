<?php

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before you update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      TechSupport <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2017 MultiSafepay, Inc. (http://www.multisafepay.com)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
$dir = dirname(dirname(dirname(dirname(__FILE__))));
require_once($dir . "/mspcheckout/include/API/Autoloader.php");

class multisafepay_klarna
{

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
        $this->description = $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>MultiSafepay Klarna</b><BR>The main MultiSafepay module must be installed (does not have to be active) to use this payment method.<BR>";
        $this->sort_order = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER;
        $this->plugin_name = $this->pluginversion . '(' . PROJECT_VERSION . ')';

        if (is_object($order) || is_object($GLOBALS['order'])) {
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

        if (file_exists(DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon)) {
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

    /**
     * 
     * @return string
     */
    function checkView()
    {
        $view = "admin";

        if (!tep_session_is_registered('admin')) {
            if ($this->getScriptName() == 'checkout_payment.php') { //FILENAME_CHECKOUT_PAYMENT)
                $view = "checkout";
            } else {
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
        if ($this->enabled && ((int) MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE . "' AND zone_country_id = '" . $GLOBALS['order']->billing['country']['id'] . "' ORDER BY zone_id");

            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } else if ($check['zone_id'] == $GLOBALS['order']->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if (!$check_flag) {
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
        if (empty($this->liveurl) || empty($this->testurl)) {
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

        while (($offset = $this->rstrpos($street_address, ' ', $offset)) !== false) {
            if ($offset < strlen($street_address) - 1 && is_numeric($street_address[$offset + 1])) {
                $address = trim(substr($street_address, 0, $offset));
                $apartment = trim(substr($street_address, $offset + 1));
                break;
            }
        }

        if (empty($apartment) && strlen($street_address) > 0 && is_numeric($street_address[0])) {
            $pos = strpos($street_address, ' ');

            if ($pos !== false) {
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

        if (is_null($offset)) {
            $offset = $size;
        }

        $pos = strpos(strrev($haystack), strrev($needle), $size - $offset);

        if ($pos === false) {
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
        foreach ($GLOBALS['order']->products as $product) {
            $items_list .= "<li>" . $product['name'] . "</li>\n";
        }
        $items_list .= "</ul>\n";

        $this->msp = new \MultiSafepayAPI\Client();

        if (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live account') {
            $this->msp->setApiUrl($this->liveurl);
        } else {
            $this->msp->setApiUrl($this->testurl);
        }

        $this->msp->setApiKey(MODULE_PAYMENT_MULTISAFEPAY_API_KEY);

        $trans_type = "redirect";
        $daysactive = "30";

        if (MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT == "True") {
            $redirect_url = $this->_href_link('ext/modules/payment/multisafepay/success.php', '', 'SSL', false, false);
        } else {
            $redirect_url = null;
        }

        list($street, $housenumber) = $this->parseAddress($GLOBALS['order']->customer['street_address']);
        list($del_street, $del_housenumber) = $this->parseAddress($GLOBALS['order']->delivery['street_address']);

        $amount = round($GLOBALS['order']->info['total'], 2) * 100;

        try {
            $this->msp->orders->post(array(
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

        foreach ($GLOBALS['order']->products as $product) {
            $attributeString = '';
            if (!empty($product['attributes'])) {
                foreach ($product['attributes'] as $attribute) {
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

        if (isset($GLOBALS['order']->info['tax_groups']['Unknown tax rate'])) {
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

        foreach ($GLOBALS['order']->products as $product) {
            if (!$this->in_array_recursive($product['tax'] / 100, $checkoutoptions_array['tax_tables']['alternate'])) {
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
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_recursive($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
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
            'currency' => $order->info['currency'],
            'currency_value' => $order->info['currency_value'],
            'payment_method' => 'Klarna'
        );

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
                MODULE_PAYMENT_MULTISAFEPAY_KLARNA_EMAIL_TEXT_ORDER_STATUS . ' ' . $order->info['orders_status'] . "\n" .
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

        //print_r($order);
        //echo $email_order;

        tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        // send emails to other people
        if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
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

    /**
     * 
     * @param type $string
     * @param type $translate
     * @param type $protected
     * @return type
     */
    function _output_string($string, $translate = false, $protected = false)
    {
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

    /*
     * Checks whether the payment has been “installed” through the admin panel
     */

    function check()
    {
        if (!isset($this->_check)) {
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
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable MultiSafepay Klarna Module', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS', 'True', 'Do you want to accept Klarna payments?', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '25', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
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
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE'
        );
    }

    /**
     * 
     * @param type $lang
     * @return string
     */
    function getLocale($lang)
    {
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
        if (empty($country)) {
            $langcode = explode(";", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $langcode = explode(",", $langcode['0']);
            return strtoupper($langcode['1']);
        } else {
            return strtoupper($country);
        }
    }

}

?>