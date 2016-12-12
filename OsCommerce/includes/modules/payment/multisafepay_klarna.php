<?php

$dir = dirname(dirname(dirname(dirname(__FILE__))));
<<<<<<< HEAD
require_once($dir . "/mspcheckout/include/MultiSafepay.combined.php");
=======
require_once($dir . "/mspcheckout/include/API/Autoloader.php");
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8

class multisafepay_klarna {

    var $code;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $plugin_name;
<<<<<<< HEAD
    var $icon = "KLARNA.png";
    var $api_url;
    var $order_id;
    var $public_title;
    var $status;
    var $shipping_methods = array();
    var $taxes = array();
    var $_customer_id = 0;

    /*
     * Constructor
     */

    function multisafepay_klarna($order_id = -1) {
=======
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

    function multisafepay_klarna($order_id = -1)
    {
        global $order;

>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        $this->code = 'multisafepay_klarna';
        $this->title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_TITLE);
        $this->description = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_TITLE);
        $this->enabled = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS == 'True';
        $this->sort_order = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER;
<<<<<<< HEAD
        $this->plugin_name = 'oscommerce-1.21 (' . PROJECT_VERSION . ')';

        if (is_object($order) || is_object($GLOBALS['order']))
            $this->update_status();

        // new configuration value
        if (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER == 'Live' || MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER == 'Live account') {
            $this->api_url = 'https://api.multisafepay.com/ewx/';
        } else {
            $this->api_url = 'https://testapi.multisafepay.com/ewx/';
=======
        $this->plugin_name = $this->pluginversion . '(' . PROJECT_VERSION . ')';

        if (is_object($order) || is_object($GLOBALS['order']))
        {
            $this->update_status();
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        }

        $this->order_id = $order_id;
        $this->public_title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_TITLE);
        $this->status = null;
    }

<<<<<<< HEAD
    function getTitle($admin = 'title') {
        $title = ($this->checkView() == "checkout") ? $this->generateIcon($this->getIcon()) . " " : "";

        $title .= ($this->checkView() == "admin") ? "MultiSafepay - " : "";
        if ($admin && $this->checkView() == "admin") {
            $title .= $admin;
        } else {
            $title .= $this->getLangStr($admin);
        };

        return $title;
    }

    function getLangStr($str) {

        return $str;
    }

    function generateIcon($icon) {
        return tep_image($icon);
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

    function getIcon() {
        $icon = DIR_WS_IMAGES . "multisafepay/en/" . $this->icon;

        if (file_exists(DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon)) {
=======
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
        if (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ENABLER == "True" || MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ICON_DISABLED == 'False')
        {
            return $str;
        }
    }

    /**
     * 
     * @param type $icon
     * @return type
     */
    function generateIcon($icon)
    {
        return tep_image($icon);
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $icon = DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon;
            return $icon;
        }
    }

<<<<<<< HEAD
    function getUserLanguage($savedSetting) {
        if ($savedSetting != "DETECT") {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            return $savedSetting;
        }

        global $languages_id;

        $query = tep_db_query("select languages_id, name, code, image, directory from " . TABLE_LANGUAGES . " where languages_id = " . (int) $languages_id . " limit 1");
<<<<<<< HEAD
        if ($languages = tep_db_fetch_array($query)) {
=======
        if ($languages = tep_db_fetch_array($query))
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            return strtoupper($languages['code']);
        }

        return "EN";
    }

<<<<<<< HEAD
    function checkView() {
        $view = "admin";

        if (!tep_session_is_registered('admin')) {
            if ($this->getScriptName() == FILENAME_CHECKOUT_PAYMENT) {
                $view = "checkout";
            } else {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                $view = "frontend";
            }
        }
        return $view;
    }

<<<<<<< HEAD
    // This is a copy from process.php	
    function get_shipping_methods($weight) {
        //require_once("includes/application_top.php");
        if (!empty($GLOBALS['_SESSION']['language'])) {
            require_once('includes/languages/' . $GLOBALS['_SESSION']['language'] . '/modules/payment/multisafepay_klarna.php');
        }
        //require_once(DIR_WS_CLASSES . 'order.php');
        //require_once(DIR_WS_CLASSES . 'shipping.php');

        $total_weight = $weight;
        $total_count = 1;

        // from shipping.php:
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        if (SHIPPING_BOX_WEIGHT >= $shipping_weight * SHIPPING_BOX_PADDING / 100) {
            $shipping_weight = $shipping_weight + SHIPPING_BOX_WEIGHT;
        } else {
            $shipping_weight = $shipping_weight + ($shipping_weight * SHIPPING_BOX_PADDING / 100);
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
            $shipping_num_boxes = ceil($shipping_weight / SHIPPING_MAX_WEIGHT);
            $shipping_weight = $shipping_weight / $shipping_num_boxes;
        }

        $tax_class = array();
        $shipping_arr = array();
        $tax_class_unique = array();

        /*
         * Load shipping modules
         */
        $module_directory = dirname(dirname(__FILE__)) . '/' . 'shipping/';
        if (!file_exists($module_directory)) {
            echo 'Error: ' . $module_directory;
        }

        // find module files
        $file_extension = substr(__FILE__, strrpos(__FILE__, '.'));
        $directory_array = array();
        if ($dir = @ dir($module_directory)) {
            while ($file = $dir->read()) {
                if (!is_dir($module_directory . $file)) {
                    if (substr($file, strrpos($file, '.')) == $file_extension) {
                        $directory_array[] = $file;
                    }
                }
            }
            sort($directory_array);
            $dir->close();
        }

        $check_query = tep_db_fetch_array(tep_db_query("select countries_iso_code_2
                             from " . TABLE_COUNTRIES . "
                             where countries_id =
                             '" . SHIPPING_ORIGIN_COUNTRY . "'"));
        $shipping_origin_iso_code_2 = $check_query['countries_iso_code_2'];

        // load modules
        $module_info = array();
        $module_info_enabled = array();
        $shipping_modules = array();
        for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
            $file = $directory_array[$i];
            global $language;
            include_once (DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/shipping/' . $file);
            include_once ($module_directory . $file);

            $class = substr($file, 0, strrpos($file, '.'));
            $module = new $class;
            $curr_ship = strtoupper($module->code);

            switch ($curr_ship) {
                case 'FEDEXGROUND':
                    $curr_ship = 'FEDEX_GROUND';
                    break;
                case 'FEDEXEXPRESS':
                    $curr_ship = 'FEDEX_EXPRESS';
                    break;
                case 'UPSXML':
                    $curr_ship = 'UPSXML_RATES';
                    break;
                case 'DHLAIRBORNE':
                    $curr_ship = 'AIRBORNE';
                    break;
                default:
                    break;
            }

            if (@constant('MODULE_SHIPPING_' . $curr_ship . '_STATUS') == 'True') {
                $module_info_enabled[$module->code] = array('enabled' => true);
            }
            if ($module->check() == true) {
                $module_info[$module->code] = array(
                    'code' => $module->code,
                    'title' => $module->title,
                    'description' => $module->description,
                    'status' => $module->check());
            }

            if (!empty($module_info_enabled[$module->code]['enabled'])) {
                $shipping_modules[$module->code] = $module;
            }
        }

        /*
         * Get shipping prices
         */
        $shipping_methods = array();
        foreach ($module_info as $key => $value) {
            // check if active
            $module_name = $module_info[$key]['code'];
            if (!$module_info_enabled[$module_name]) {
                continue;
            }

            $curr_ship = strtoupper($module_name);

            // calculate price
            $module = $shipping_modules[$module_name];
            $quote = $module->quote($method);
            $price = $quote['methods'][0]['cost'];
            global $currencies;
            $shipping_price = $currencies->get_value(DEFAULT_CURRENCY) * ($price >= 0 ? $price : 0);

            // need this?
            $common_string = "MODULE_SHIPPING_" . $curr_ship . "_";
            @$zone = constant($common_string . "ZONE");
            @$enable = constant($common_string . "STATUS");
            @$curr_tax_class = constant($common_string . "TAX_CLASS");
            @$price = constant($common_string . "COST");
            @$handling = constant($common_string . "HANDLING");
            @$table_mode = constant($common_string . "MODE");

            // allowed countries - zones	
            if ($zone != '') {
                $zone_result = tep_db_query("SELECT countries_name, coalesce(zone_code, 'All Areas') zone_code, countries_iso_code_2
                                  FROM " . TABLE_GEO_ZONES . " AS gz
                                  inner join " . TABLE_ZONES_TO_GEO_ZONES . " AS ztgz on gz.geo_zone_id = ztgz.geo_zone_id
                                  inner join " . TABLE_COUNTRIES . " AS c on ztgz.zone_country_id = c.countries_id
                                  left join " . TABLE_ZONES . " AS z on ztgz.zone_id = z.zone_id
                                  WHERE gz.geo_zone_id = '" . $zone . "'");

                $allowed_restriction_state = $allowed_restriction_country = array();
                // Get all the allowed shipping zones.
                while ($zone_answer = tep_db_fetch_array($zone_result)) {
                    $allowed_restriction_state[] = $zone_answer['zone_code'];
                    $allowed_restriction_country[] = $zone_answer['countries_iso_code_2'];
                }
            }

            if ($curr_tax_class != 0 && $curr_tax_class != '') {
                $tax_class[] = $curr_tax_class;

                if (!in_array($curr_tax_class, $tax_class_unique))
                    $tax_class_unique[] = $curr_tax_class;
            }

            if (empty($quote['error']) && $quote['id'] != 'zones') {
                foreach ($quote['methods'] as $method) {
                    $shipping_methods[] = array(
                        'id' => $quote['id'],
                        'module' => $quote['module'],
                        'title' => $quote['methods'][0]['title'],
                        'price' => $shipping_price,
                        'allowed' => $allowed_restriction_country,
                        'tax_class' => $curr_tax_class,
                        'zone' => $zone,
                    );
                }
            } elseif ($quote['id'] == 'zones') {
                for ($cur_zone = 1; $cur_zone <= $module->num_zones; $cur_zone++) {
                    $countries_table = constant('MODULE_SHIPPING_ZONES_COUNTRIES_' . $cur_zone);
                    $country_zones = split("[,]", $countries_table);

                    if (count($country_zones) > 1 || !empty($country_zones[0])) {
                        $shipping = -1;
                        $zones_cost = constant('MODULE_SHIPPING_ZONES_COST_' . $cur_zone);

                        $zones_table = split("[:,]", $zones_cost);
                        $size = sizeof($zones_table);
                        for ($i = 0; $i < $size; $i+=2) {
                            if ($shipping_weight <= $zones_table[$i]) {
                                $shipping = $zones_table[$i + 1];
                                $shipping_method = $shipping_weight . ' ' . MODULE_SHIPPING_ZONES_TEXT_UNITS;
                                break;
                            }
                        }

                        if ($shipping == -1) {
                            $shipping_cost = 0;
                            $shipping_method = MODULE_SHIPPING_ZONES_UNDEFINED_RATE;
                        } else {
                            $shipping_cost = ($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $cur_zone);

                            $shipping_methods[] = array(
                                'id' => $quote['id'],
                                'module' => $quote['module'],
                                'title' => $shipping_method,
                                'price' => $shipping_cost,
                                'allowed' => $country_zones,
                            );
                        }
                    }
                }
            }
        }
        return $shipping_methods;
    }

=======
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
    /*
     * Check whether this payment module is available
     */

<<<<<<< HEAD
    function update_status() {
        // always disable
        //$this->enabled = false;
        if ($this->enabled && ((int) MODULE_PAYMENT_MSP_BANKTRANS_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_MSP_BANKTRANS_ZONE . "' AND zone_country_id = '" . $GLOBALS['order']->billing['country']['id'] . "' ORDER BY zone_id");

            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } else if ($check['zone_id'] == $GLOBALS['order']->billing['zone_id']) {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                    $check_flag = true;
                    break;
                }
            }

<<<<<<< HEAD
            if (!$check_flag) {
                $this->enabled = false;
            }
        }
        /* if(MODULE_PAYMENT_MULTISAFEPAY_KLARNA_NORMAL_CHECKOUT != 'True'){
          $this->enabled = false;
          } */
=======
            if (!$check_flag)
            {
                $this->enabled = false;
            }
        }
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
    }

    // ---- select payment module ----

    /*
     * Client side javascript that will verify any input fields you use in the
     * payment method selection page
     */
<<<<<<< HEAD
    function javascript_validation() {
=======
    function javascript_validation()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        return false;
    }

    /*
     * Outputs the payment method title/text and if required, the input fields
     */

<<<<<<< HEAD
    function selection() {
=======
    function selection()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        global $customer_id;
        global $languages_id;
        global $order;
        global $order_totals;
        global $order_products_id;

        // check if transaction is possible
<<<<<<< HEAD
        if (empty($this->api_url)) {
=======
        //Not needed obviously?
        //Previously: if(empty($this->api_url)... / $this->api_url has already been removed.
        if (empty($this->liveurl || $this->testurl))
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
    function pre_confirmation_check() {
=======
    function pre_confirmation_check()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        return false;
    }

    // ---- confirm order ----

    /*
     * Any checks or processing on the order information before proceeding to
     * payment confirmation
     */
<<<<<<< HEAD
    function confirmation() {
=======
    function confirmation()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        return false;
    }

    /*
     * Outputs the html form hidden elements sent as POST data to the payment
     * gateway
     */

<<<<<<< HEAD
    function process_button() {
=======
    function process_button()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        return false;
    }

    // ---- process payment ----

    /*
     * Payment verification
     */
<<<<<<< HEAD
    function before_process() {
        $this->_save_order();

=======
    function before_process()
    {
        $this->_save_order();
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        tep_redirect($this->_start_klarna());
    }

    /*
     * Post-processing of the payment/order after the order has been finalised
     */

<<<<<<< HEAD
    function after_process() {
=======
    function after_process()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        return false;
    }

    // ---- error handling ----

    /*
     * Advanced error handling
     */
<<<<<<< HEAD
    function output_error() {
        return false;
    }

    function get_error() {
=======
    
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        $error = array(
            'title' => MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_ERROR,
            'error' => $this->_get_error_message($_GET['error'])
        );

        return $error;
    }
<<<<<<< HEAD

    // ---- MultiSafepay ----
    function isNewAddressQuery() {
        // Check for mandatory parameters
        $country = $_GET['country'];
        $countryCode = $_GET['countrycode'];
        $transactionId = $_GET['transactionid'];

        if (empty($country) || empty($countryCode) || empty($transactionId))
            return false;
        else
            return true;
    }

    // Handles new shipping costs request
    function handleShippingMethodsNotification() {
        $country = $_GET['country'];
        $countryCode = $_GET['countrycode'];
        $transactionId = $_GET['transactionid'];
        $weight = $_GET['weight'];
        $size = $_GET['size'];

        header("Content-Type:text/xml");
        print($this->getShippingMethodsFilteredXML($country, $countryCode, $weight, $size, $transactionId));
    }

    // Returns XML with new shipping costs
    function getShippingMethodsFilteredXML($country, $countryCode, $weight, $size, $transactionId) {
        $outxml = '<shipping-info>';
        $methods = $this->getShippingMethodsFiltered($country, $countryCode, $weight, $size, $transactionId);

        foreach ($methods as $method) {
            $outxml .= '<shipping>';
            $outxml .= '<shipping-name>';
            $outxml .= $method['name'];
            $outxml .= '</shipping-name>';
            $outxml .= '<shipping-cost currency="' . $method['currency'] . '">';
            $outxml .= $method['cost'];
            $outxml .= '</shipping-cost>';
            $outxml .= '</shipping>';
        }

        $outxml .= '</shipping-info>';
        return $outxml;
    }

    // Get shipping methods for given parameters
    // Result as an array:
    // 'name' => 'test-name'
    // 'cost' => '123'
    // 'currency' => 'EUR' (currently only this supported)
    function getShippingMethodsFiltered($country, $countryCode, $weight, $size, $transactionId) {
        $out = array();
        $shipping_methods = $this->get_shipping_methods($weight);

        foreach ($shipping_methods as $shipping_method) {
            // ISO codes match - add to output list
            $shipping = array();
            $shipping['name'] = $shipping_method['module'] . ' - ' . $shipping_method['title'];
            $shipping['cost'] = $shipping_method['price'];
            $shipping['currency'] = 'EUR'; // Currently Euro is supported
            $out[] = $shipping;
        }

        return $out;
    }

    function _start_klarna() {
        $amount = $this->convertEuro($GLOBALS['order']->info['total']);
        $amount = $amount * 100;

        // generate items list
        $items = "<ul>\n";
        foreach ($GLOBALS['order']->products as $product) {
            $items .= "<li>" . $product['name'] . "</li>\n";
        }
        $items .= "</ul>\n";

        // start transaction
        $msp = new MultiSafepayAPI();
        $msp->plugin_name = $this->plugin_name;

        $msp->test = (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER != 'Live account');
        $msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ACCOUNT_ID;
        $msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_ID;
        $msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_SECURE_CODE;
        $msp->merchant['notification_url'] = $this->_href_link('ext/modules/payment/multisafepay/notify_checkout.php?type=initial', '', 'SSL', false, false);
        $msp->merchant['cancel_url'] = $this->_href_link('shopping_cart.php', '', 'SSL', false, false);
        $msp->use_shipping_notification = false;

        if (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT == "True") {
            $msp->merchant['redirect_url'] = $this->_href_link('ext/modules/payment/multisafepay/success.php', '', 'SSL', false, false);
        }

        $msp->customer['locale'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_LOCALE;
        $msp->customer['firstname'] = $GLOBALS['order']->customer['firstname'];
        $msp->customer['lastname'] = $GLOBALS['order']->customer['lastname'];
        $msp->customer['zipcode'] = $GLOBALS['order']->customer['postcode'];
        $msp->customer['city'] = $GLOBALS['order']->customer['city'];
        $msp->customer['country'] = $GLOBALS['order']->customer['country']['iso_code_2'];
        $msp->customer['phone'] = $GLOBALS['order']->customer['telephone'];
        $msp->customer['email'] = $GLOBALS['order']->customer['email_address'];
        $msp->parseCustomerAddress($GLOBALS['order']->customer['street_address']);

        $msp->delivery['firstname'] = $GLOBALS['order']->delivery['firstname'];
        $msp->delivery['lastname'] = $GLOBALS['order']->delivery['lastname'];
        $msp->delivery['zipcode'] = $GLOBALS['order']->delivery['postcode'];
        $msp->delivery['city'] = $GLOBALS['order']->delivery['city'];
        $msp->customer['country'] = $GLOBALS['order']->delivery['country']['iso_code_2'];
        $msp->delivery['phone'] = $GLOBALS['order']->delivery['telephone'];
        $msp->delivery['email'] = $GLOBALS['order']->delivery['email_address'];
        $msp->parseDeliveryAddress($GLOBALS['order']->delivery['street_address']);

        $msp->transaction['id'] = $this->order_id;
        $msp->transaction['currency'] = 'EUR';
        $msp->transaction['amount'] = $amount; // cents
        $msp->transaction['description'] = 'Order #' . $this->order_id . ' at ' . STORE_NAME;
        $msp->transaction['items'] = $items;
        $msp->transaction['gateway'] = 'KLARNA';

        $msp->transaction['var1'] = $GLOBALS['customer_id'];
        $msp->transaction['var2'] = tep_session_id() . ';' . tep_session_name();

        $msp->setDefaultTaxZones();
        $msp->cart->AddRoundingPolicy('', 'PER_ITEM');

        $this->getItems($msp);

        $GLOBALS['multisafepay_order_id'] = $this->order_id;
        tep_session_register('multisafepay_order_id');


        $price = 0;
        $taxes = array();


        if (isset($GLOBALS['order']->info['msp_fee_inc_tax'])) {
            if ($GLOBALS['order']->info['msp_fee_inc_tax'] > 0) {
                $fee_rate = tep_get_tax_rate(MODULE_FIXED_PAYMENT_CHG_TAX_CLASS) / 100;

                if ($fee_rate == 0) {
                    $fee_rate = '0.00';
                }
                $table = new MspAlternateTaxTable();
                $table->name = 'payment_fee';
                $rule = new MspAlternateTaxRule($fee_rate);
                $table->AddAlternateTaxRules($rule);
                $msp->cart->AddAlternateTaxTables($table);

                if ($GLOBALS['order']->info['msp_fee_inc_tax'] > 2.95) {
                    $this->_error_redirect('Multisafepay fee to high!');
                    exit();
                }

                $fee_inc_tax = $GLOBALS['order']->info['msp_fee_inc_tax'];
                /* $tax = ($fee_inc_tax / 121) * 21;

                  $fee = $fee_inc_tax - $tax;
                  $fee = number_format($fee, 4, '.', ''); */

                $c_item = new MspItem('Fee', 'Fee', 1, $fee_inc_tax, 'KG', 0); // Todo adjust the amount to cents, and round it up.
                $c_item->SetMerchantItemId('Fee');
                $c_item->SetTaxTableSelector('payment_fee');
                $msp->cart->AddItem($c_item);

                if ($fee_rate != 0) {
                    $price += $GLOBALS['order']->info['msp_fee_inc_tax'];
                }
            }
        }



        foreach ($GLOBALS['order']->products as $product) {
            if (isset($product['final_price'])) {
                $price += $product['final_price'] * $product['qty'];
            } elseif (isset($product['price'])) {
                $price += $product['price'] * $product['qty'];
            }



            $taxes[$product['tax']] = $product['tax'];
        }

        $unique_taxes = array_unique($taxes);

        if (isset($unique_taxes['20'])) {
            $tax = 1 + ($unique_taxes['20'] / 100);
        } elseif (isset($unique_taxes['6'])) {
            $tax = 1 + ($unique_taxes['6'] / 100);
        } else {
            $tax = 1 + ($unique_taxes['5'] / 100);
        }


        $total_tax = ($price * $tax) - $price;

        if ((string) $total_tax == (string) $GLOBALS['order']->info['tax']) {
            $c_item = new MspItem('Shipping' . " " . 'EUR', 'Shipping', '1', $GLOBALS['order']->info['shipping_cost'], '0', '0');
            $msp->cart->AddItem($c_item);
            $c_item->SetMerchantItemId('Shipping');
            $c_item->SetTaxTableSelector('BTW0');
        } else {
            $shipping_tax = (float) $GLOBALS['order']->info['tax'] - (float) $total_tax;
            $shipping_tax_percentage = $shipping_tax / (float) $GLOBALS['order']->info['shipping_cost'];


            $table = new MspAlternateTaxTable();
            $table->name = 'shipping_tax';
            $rule = new MspAlternateTaxRule($shipping_tax_percentage);
            $table->AddAlternateTaxRules($rule);
            $msp->cart->AddAlternateTaxTables($table);


            $c_item = new MspItem('Shipping' . " " . 'EUR', 'Shipping', '1', $GLOBALS['order']->info['shipping_cost'], '0', '0');
            $msp->cart->AddItem($c_item);
            $c_item->SetMerchantItemId('Shipping');
            $c_item->SetTaxTableSelector('shipping_tax');
        }






        $url = $msp->startCheckout();


        if ($msp->error) {
            $this->_error_redirect($msp->error_code . ": " . $msp->error);
            exit();
        }
        return $url;
    }

    function getItems($msp) {
        foreach ($GLOBALS['order']->products as $product) {
            $price = $product['price'];
            if (isset($product['final_price'])) {
                $price = $product['final_price'];
            }

            $attributeString = '';
            if (!empty($product['attributes'])) {
                foreach ($product['attributes'] as $attribute) {
=======
    
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
                        "locale" => strtolower($GLOBALS['order']->delivery['country']['iso_code_2']) . '_' . $GLOBALS['order']->delivery['country']['iso_code_2'],
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
            echo htmlspecialchars($e->getMessage());
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                    $attributeString .= $attribute['option'] . ' ' . $attribute['value'] . ', ';
                }
                $attributeString = substr($attributeString, 0, -2);
                $attributeString = ' (' . $attributeString . ')';
            }

<<<<<<< HEAD
            $c_item = new MspItem($product['name'] . $attributeString, $product['model'], $product['qty'], $this->convertEuro($price), 'KG', $product['weight']);
            $c_item->SetMerchantItemId($product['id']);

            if ($product['tax_description'] != 'Unknown tax rate') {

                $tax_name = 'BTW' . $product['tax'];

                $c_item->SetTaxTableSelector($tax_name);
            } else {
                $c_item->SetTaxTableSelector('BTW0');
            }

            $msp->cart->AddItem($c_item);
        }
    }

    function convertEuro($value, $round = true) {
        $currency = 'EUR';
        $rate = $GLOBALS['currencies']->currencies[$currency]['value'];
        $new_total = $value * $rate;

        if ($round)
            $new_total = tep_round($new_total, 2);

        return $new_total;
    }

    function checkout_notify() {
        global $currencies;
        $this->order_id = $_GET['transactionid'];

        // Check if new address query
        if ($this->isNewAddressQuery()) {
            $this->handleShippingMethodsNotification();
            exit(0); // Nothing else to do
        }

=======
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

>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8

        $msp = new MultiSafepayAPI();
        $msp->plugin_name = $this->plugin_name;
        $msp->test = (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER != 'Live account');
        $msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ACCOUNT_ID;
        $msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_ID;
        $msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_SECURE_CODE;
        $msp->transaction['id'] = $this->order_id;

        // get status
        $status = $msp->getStatus();

<<<<<<< HEAD
        if ($msp->error) {
=======
        if ($msp->error)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            echo $msp->error_code . ": " . $msp->error;
            exit();
        }

<<<<<<< HEAD
        if (!$msp->details['transaction']['var1']) { // no customer_id, so create a customer
            //$this->resume_session($msp->details);
            $customer_id = $this->get_customer($msp->details);
        } else {
=======
        if (!$msp->details['transaction']['var1'])
        { // no customer_id, so create a customer
            //$this->resume_session($msp->details);
            $customer_id = $this->get_customer($msp->details);
        } else
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
        if ($check_shipping) {
=======
        if ($check_shipping)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $query = "UPDATE " . TABLE_ORDERS_TOTAL .
                    " SET title = '" . $title . "', value = '" . $value . "', text = '" . $text . "'" .
                    " WHERE class = 'ot_shipping' AND orders_id = '" . $this->order_id . "'";
            tep_db_query($query);
<<<<<<< HEAD
        } else {
=======
        } else
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
        // determine new osCommerce order status
=======
        //determine new osCommerce order status
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        $reset_cart = false;
        $notify_customer = false;
        $new_order_status = null;

<<<<<<< HEAD
        switch ($status) {
=======
        switch ($status)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            case "initialized":
                $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED;
                $reset_cart = true;
                break;
            case "completed":
<<<<<<< HEAD
                if ($old_order_status == MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED || $old_order_status == DEFAULT_ORDERS_STATUS_ID || !$old_order_status) {
                    $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_COMPLETED;
                    $reset_cart = true;
                    // $notify_customer = ($old_order_status != $new_order_status);
=======
                if ($old_order_status == MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED || $old_order_status == DEFAULT_ORDERS_STATUS_ID || !$old_order_status)
                {
                    $new_order_status = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_COMPLETED;
                    $reset_cart = true;
                    //$notify_customer = ($old_order_status != $new_order_status);
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD




        // update order with new status if needed
        if ($new_order_status) {
=======
        // update order with new status if needed
        if ($new_order_status)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
        if ($notify_customer) {
            $this->_notify_customer($new_order_status);
        } else {
            if ($new_order_status && $old_order_status != $new_order_status) {
=======
        if ($notify_customer)
        {
            $this->_notify_customer($new_order_status);
        } else
        {
            if ($new_order_status && $old_order_status != $new_order_status)
            {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                $sql_data_array = array('orders_id' => $this->order_id,
                    'orders_status_id' => $new_order_status,
                    'date_added' => 'now()',
                    'customer_notified' => 0,
                    'comments' => '');
                tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            }
        }

        // reset cart
<<<<<<< HEAD
        if ($reset_cart) {
=======
        if ($reset_cart)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            //tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = '" . (int)$GLOBALS['order']->customer['id'] . "'");
            //tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id = '" . (int)$GLOBALS['order']->customer['id'] . "'");
        }
        //print_r($msp->details);
        return $status;
    }

<<<<<<< HEAD
    function resume_session($details) {
        if (isset($details['transaction']['var2'])) {
=======
    /**
     * 
     * @param type $details
     */
    
    function resume_session($details)
    {
        if (isset($details['transaction']['var2']))
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            list ($sess_id, $sess_name) = explode(";", $details['transaction']['var2']);
            //If session management is supported by this PHP version
            if (function_exists('session_id'))
                session_id($sess_id);
            if (function_exists('session_name'))
                session_name($sess_name);
        }
    }

<<<<<<< HEAD
    function get_customer($details) {
        $email = $details['customer']['email'];
        //    Check if the email exists
=======
    /**
     * 
     * @param type $details
     * @return \type
     */
    
    function get_customer($details)
    {
        $email = $details['customer']['email'];
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        $customer_exists = tep_db_fetch_array(tep_db_query("select customers_id from " .
                        TABLE_CUSTOMERS . " where customers_email_address = '" . $email . "'"));

        $new_user = false;
<<<<<<< HEAD
        if ($customer_exists['customers_id'] != '') {
            $customer_id = $customer_exists['customers_id'];
            //tep_session_register('customer_id');
        } else {
=======
        if ($customer_exists['customers_id'] != '')
        {
            $customer_id = $customer_exists['customers_id'];
            //tep_session_register('customer_id');
        } else
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
            if (ACCOUNT_DOB == 'true') {
=======
            if (ACCOUNT_DOB == 'true')
            {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
        if (!tep_db_num_rows($address_book)) {
=======
        if (!tep_db_num_rows($address_book))
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
        } else {
=======
        } else
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
    function get_country_from_code($code) {
=======
    /**
     * 
     * @param type $code
     * @return type
     */
    function get_country_from_code($code)
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        //countries_iso_code_2
        $country = tep_db_fetch_array(tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . $code . "'"));
        return $country;
    }

<<<<<<< HEAD
    function get_hash($order_id, $customer_id) {
        return md5($order_id . $customer_id);
    }

    function _get_error_message($code) {
        if (is_numeric($code)) {
            $message = constant(sprintf("MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_ERROR_%04d", $code));

            if (!$message) {
                $message = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_ERROR_UNKNOWN;
            }
        } else {
            $const = sprintf("MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_ERROR_%s", strtoupper($code));

            if (defined($const)) {
                $message = constant($const);
            } else {
=======
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
     * @param type $code
     * @return type
     */
    
    function _get_error_message($code)
    {
        if (is_numeric($code))
        {
            $message = constant(sprintf("MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_ERROR_%04d", $code));

            if (!$message)
            {
                $message = MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_ERROR_UNKNOWN;
            }
        } else
        {
            $const = sprintf("MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TEXT_ERROR_%s", strtoupper($code));

            if (defined($const))
            {
                $message = constant($const);
            } else
            {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                $message = $code;
            }
        }
        return $message;
    }

<<<<<<< HEAD
    function _error_redirect($error) {
=======
    /**
     * 
     * @param type $error
     */
    
    function _error_redirect($error)
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        tep_redirect($this->_href_link(
                        FILENAME_SHOPPING_CART, 'payment_error=' . $this->code . '&error=' . $error, 'NONSSL', true, false, false
        ));
    }

    // ---- Ripped from checkout_process.php ----

    /*
     * Store the order in the database, and set $this->order_id
     */
<<<<<<< HEAD
    function _save_order() {
=======
    
    function _save_order()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        global $customer_id;
        global $languages_id;
        global $order;
        global $order_totals;
        global $order_products_id;

<<<<<<< HEAD
        if (empty($order_totals)) {
=======
        if (empty($order_totals))
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            require(DIR_WS_CLASSES . 'order_total.php');
            $order_total_modules = new order_total();
            $order_totals = $order_total_modules->process();
        }

<<<<<<< HEAD
        if (!empty($this->order_id) && $this->order_id > 0) {
            return;
        }

        if (MODULE_PAYMENT_MULTISAFEPAY_KLARNA_DISPLAY_CHECKOUT_ORDERS == 'False') {
            $order->info['order_status'] = 0;
        }

=======
        if (!empty($this->order_id) && $this->order_id > 0)
        {
            return;
        }

>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
            //'orders_status' 				=> 	$order->info['order_status'],
            'orders_status' => MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED,
            //  'orders_status' 				=> 	0,// set to 0 to hide the order before a transaction is made
            'currency' => $order->info['currency'],
            'currency_value' => $order->info['currency_value'],
            'payment_method' => 'MultiSafepay fast checkout'
=======
            'orders_status' => MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ORDER_STATUS_ID_INITIALIZED,
            'currency' => $order->info['currency'],
            'currency_value' => $order->info['currency_value'],
            'payment_method' => 'Klarna'
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        );

        tep_db_perform(TABLE_ORDERS, $sql_data_array);
        $insert_id = tep_db_insert_id();
<<<<<<< HEAD
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
=======
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
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
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
            if (isset($order->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    if (DOWNLOAD_ENABLED == 'true') {
=======
            if (isset($order->products[$i]['attributes']))
            {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++)
                {
                    if (DOWNLOAD_ENABLED == 'true')
                    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
                    } else {
=======
                    } else
                    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
=======
                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename']))
                    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
    function _notify_customer($new_order_status = null) {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        global $customer_id;
        global $order;
        global $order_totals;
        global $order_products_id;
        global $total_products_price;
        global $products_tax;
        global $languages_id;
        global $currencies;
        global $payment;

<<<<<<< HEAD
        if ($new_order_status != null) {

=======
        if ($new_order_status != null)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $order->info['order_status'] = $new_order_status;
        }

        $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
        $sql_data_array = array('orders_id' => $this->order_id,
            'orders_status_id' => $order->info['order_status'],
            'date_added' => 'now()',
            'customer_notified' => $customer_notification,
            'comments' => $order->info['comments']);
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

<<<<<<< HEAD
        // initialized for the email confirmation
=======
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        $products_ordered = '';
        $total_weight = 0;
        $total_tax = 0;
        $total_cost = 0;

<<<<<<< HEAD
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
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                        }

                        $attributes = tep_db_query($attributes_query);
                        $attributes_values = tep_db_fetch_array($attributes);
<<<<<<< HEAD
                    } else {
=======
                    } else
                    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
=======
                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename']))
                    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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
<<<<<<< HEAD
        if ($order->info['comments']) {
=======
        if ($order->info['comments'])
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $email_order .= tep_db_output($order->info['comments']) . "\n\n";
        }
        $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                EMAIL_SEPARATOR . "\n" .
                $products_ordered .
                EMAIL_SEPARATOR . "\n";

<<<<<<< HEAD
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
        }

        if ($order->content_type != 'virtual') {
=======
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++)
        {
            $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
        }

        if ($order->content_type != 'virtual')
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                    EMAIL_SEPARATOR . "\n" .
                    $this->_address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n") . "\n";
        }

        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                EMAIL_SEPARATOR . "\n" .
                $this->_address_format($order->billing['format_id'], $order->billing, 0, '', "\n") . "\n\n";

<<<<<<< HEAD
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
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                $email_order .= $payment_class->email_footer . "\n\n";
            }
        }

        //print_r($order);
        //echo $email_order;

        tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        // send emails to other people
<<<<<<< HEAD
        if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
=======
        if (SEND_EXTRA_ORDER_EMAILS_TO != '')
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
    }

    // ---- Ripped from includes/functions/general.php ----

<<<<<<< HEAD
    function _address_format($address_format_id, $address, $html, $boln, $eoln) {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        $address_format_query = tep_db_query("SELECT address_format AS format FROM " . TABLE_ADDRESS_FORMAT . " WHERE address_format_id = '" . (int) $address_format_id . "'");
        $address_format = tep_db_fetch_array($address_format_query);

        $company = $this->_output_string_protected($address['company']);
<<<<<<< HEAD
        if (isset($address['firstname']) && tep_not_null($address['firstname'])) {
            $firstname = $this->_output_string_protected($address['firstname']);
            $lastname = $this->_output_string_protected($address['lastname']);
        } elseif (isset($address['name']) && tep_not_null($address['name'])) {
            $firstname = $this->_output_string_protected($address['name']);
            $lastname = '';
        } else {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $firstname = '';
            $lastname = '';
        }

        $street = $this->_output_string_protected($address['street_address']);
        $suburb = $this->_output_string_protected($address['suburb']);
        $city = $this->_output_string_protected($address['city']);
        $state = $this->_output_string_protected($address['state']);

<<<<<<< HEAD
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
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $country = '';
        }

        $postcode = $this->_output_string_protected($address['postcode']);
        $zip = $postcode;

<<<<<<< HEAD
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
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
        if ((ACCOUNT_COMPANY == 'true') && (tep_not_null($company))) {
=======
        if ((ACCOUNT_COMPANY == 'true') && (tep_not_null($company)))
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $address = $company . $cr . $address;
        }
        return $address;
    }

<<<<<<< HEAD
    function _output_string($string, $translate = false, $protected = false) {
        if ($protected == true) {
            return htmlspecialchars($string);
        } else {
            if ($translate == false) {
                return $this->_parse_input_field_data($string, array('"' => '&quot;'));
            } else {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                return $this->_parse_input_field_data($string, $translate);
            }
        }
    }

<<<<<<< HEAD
    function _output_string_protected($string) {
        return $this->_output_string($string, false, true);
    }

    function _parse_input_field_data($data, $parse) {
        return strtr(trim($data), $parse);
    }

    function _href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $unused = true, $escape_html = true) {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        global $request_type, $session_started, $SID;

        unset($unused);

<<<<<<< HEAD
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
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $link .= $page;
            $separator = '?';
        }

        while ((substr($link, -1) == '&') || (substr($link, -1) == '?'))
            $link = substr($link, 0, -1);

        // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
<<<<<<< HEAD
        if (($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False')) {
            if (tep_not_null($SID)) {
                $_sid = $SID;
            } elseif (( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') )) {
                if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
=======
        if (($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False'))
        {
            if (tep_not_null($SID))
            {
                $_sid = $SID;
            } elseif (( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ))
            {
                if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN)
                {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                    $_sid = tep_session_name() . '=' . tep_session_id();
                }
            }
        }

<<<<<<< HEAD
        if (isset($_sid)) {
            if ($escape_html) {
                $link .= $separator . $this->_output_string($_sid);
            } else {
=======
        if (isset($_sid))
        {
            if ($escape_html)
            {
                $link .= $separator . $this->_output_string($_sid);
            } else
            {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
                $link .= $separator . $_sid;
            }
        }
        return $link;
    }

<<<<<<< HEAD
    // ---- installation & configuration ----

    /*
     * Checks whether the payment has been “installed” through the admin panel
     */
    function check() {
        if (!isset($this->_check)) {
=======
    /*
     * Checks whether the payment has been “installed” through the admin panel
     */

    function check()
    {
        if (!isset($this->_check))
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /*
     * Installs the configuration keys into the database
     */

<<<<<<< HEAD
    function install() {
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('MultiSafepay enabled', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS', 'True', 'Enable MultiSafepay payments for this website', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Type account', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER', 'Live account', '<a href=\'http://www.multisafepay.com/nl/klantenservice-zakelijk/open-een-testaccount.html\' target=\'_blank\' style=\'text-decoration: underline; font-weight: bold; color:#696916; \'>Sign up for a free test account!</a>', '6', '21', 'tep_cfg_select_option(array(\'Live account\', \'Test account\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Account ID', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ACCOUNT_ID', '', 'Your merchant account ID', '6', '22', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Site ID', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_ID', '', 'ID of this site', '6', '23', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Site Code', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_SECURE_CODE', '', 'Site code for this site', '6', '24', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Auto Redirect', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT', 'True', 'Enable auto redirect after payment', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display checkout orders', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_DISPLAY_CHECKOUT_ORDERS', 'True', 'Displays new fast checkout orders before the transaction is completed', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
=======
    function install()
    {
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('MultiSafepay enabled', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS', 'True', 'Enable MultiSafepay payments for this website', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Type account', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER', 'Live account', '<a href=\'http://www.multisafepay.com/nl/klantenservice-zakelijk/open-een-testaccount.html\' target=\'_blank\' style=\'text-decoration: underline; font-weight: bold; color:#696916; \'>Sign up for a free test account!</a>', '6', '21', 'tep_cfg_select_option(array(\'Live account\', \'Test account\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('API Key', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_KEY', '', 'Your MultiSafepay API Key', '6', '22', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Auto Redirect', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT', 'True', 'Enable auto redirect after payment', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '25', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
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
<<<<<<< HEAD
=======
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable gateway titles in checkout', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ENABLER', 'True', 'Enable the gateway title in checkout', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable icons in gateway titles. If disabled it will overrule option above.', 'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ICON_DISABLED', 'True', 'Enable the icon in the checkout title for the gateway', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");        
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
    }

    /*
     * Removes the configuration keys from the database
     */

<<<<<<< HEAD
    function remove() {
=======
    function remove()
    {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
        tep_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    /*
     * Defines an array containing the configuration key keys that are used by
     * the payment module
     */

<<<<<<< HEAD
    function keys() {
        return array(
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ACCOUNT_ID',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_ID',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SITE_SECURE_CODE',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_DISPLAY_CHECKOUT_ORDERS',
=======
    function keys()
    {
        return array(
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_STATUS',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_SERVER',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_API_KEY',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_AUTO_REDIRECT',
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_ZONE',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_SORT_ORDER',
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
<<<<<<< HEAD
        );
    }

    function getlocale($lang) {
        switch ($lang) {
=======
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ENABLER',
            'MODULE_PAYMENT_MULTISAFEPAY_KLARNA_TITLES_ICON_DISABLED',
        );
    }

    /**
     * 
     * @param type $lang
     * @return string
     */
    
    function getlocale($lang)
    {
        switch ($lang)
        {
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
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

<<<<<<< HEAD
    function getcountry($country) {
        if (empty($country)) {
            $langcode = explode(";", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $langcode = explode(",", $langcode['0']);
            return strtoupper($langcode['1']);
        } else {
=======
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
>>>>>>> 9336b56b885b9fa93d525aa6f3b425c1cf7e7da8
            return strtoupper($country);
        }
    }

}

?>