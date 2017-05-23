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
require( "multisafepay.php" );

class multisafepay_ideal extends multisafepay
{

    var $icon = "ideal.png";
    var $issuer = '';
    var $liveurl = 'https://api.multisafepay.com/v1/json/';
    var $testurl = 'https://testapi.multisafepay.com/v1/json/';

    /*
     * Constructor
     */

    function __construct()
    {
        global $order;

        $this->code = 'multisafepay_ideal';
        $this->title = $this->getTitle('iDEAL');
        $this->public_title = $this->getTitle('iDEAL');
        $this->description = $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>MultiSafepay iDEAL</b><BR>The main MultiSafepay module must be installed (does not have to be active) to use this payment method.<BR>";
        $this->enabled = MODULE_PAYMENT_MSP_IDEAL_STATUS == 'True';
        $this->sort_order = MODULE_PAYMENT_MSP_IDEAL_SORT_ORDER;


        if (is_object($order)) {
            $this->update_status();
        }
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

        $issuers = $this->create_iDeal_box();


        if (MODULE_PAYMENT_MSP_IDEAL_DIRECT == 'True') {
            $selection = array(
                'id' => $this->code,
                'module' => $this->public_title,
                'fields' => array(array('title' => '',
                        'field' => tep_draw_pull_down_menu('msp_issuer', $issuers) . ' '))
            );
        } else {
            $selection = array(
                'id' => $this->code,
                'module' => $this->public_title,
                'fields' => array(array('title' => ''))
            );
        }

        return $selection;
    }

    /**
     * 
     * @return type
     */
    function create_iDeal_box()
    {
        try {
            $msp = new \MultiSafepayAPI\Client();

            if (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live account') {
                $msp->setApiUrl($this->liveurl);
            } else {
                $msp->setApiUrl($this->testurl);
            }

            $msp->setApiKey(MODULE_PAYMENT_MULTISAFEPAY_API_KEY);

            $iDealIssuers = $msp->issuers->get();

            $issuers = array();
            $i = 0;
            $issuers[$i]['id'] = null;
            $issuers[$i]['text'] = "Select a bank";
            $i++;

            foreach ($iDealIssuers as $issuer) {
                $issuers[$i]['id'] = $issuer->code;
                $issuers[$i]['text'] = $issuer->description;
                $i++;
            }

            return $issuers;
        } catch (Exception $e) {
            echo htmlspecialchars($e->getMessage());
            die();
        }
    }

    /*
     * Check whether this payment module is available
     */

    function update_status()
    {
        global $order;

        if (($this->enabled == true) && ((int) MODULE_PAYMENT_MSP_IDEAL_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MSP_IDEAL_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

    function process_button()
    {
        if (MODULE_PAYMENT_MSP_IDEAL_DIRECT == 'True') {
            //return tep_draw_hidden_field('msp_paymentmethod',		'IDEAL').$this->create_iDeal_box();
            return tep_draw_hidden_field('msp_paymentmethod', 'IDEAL') . tep_draw_hidden_field('msp_issuer', $_POST['msp_issuer']);
        } else {
            return tep_draw_hidden_field('msp_paymentmethod', 'IDEAL');
        }
    }

    /*
     * Checks whether the payment has been “installed” through the admin panel
     */

    function check()
    {
        if (!isset($this->_check)) {
            $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MSP_IDEAL_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /*
     * Installs the configuration keys into the database
     */

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable MultiSafepay iDEAL Module', 'MODULE_PAYMENT_MSP_IDEAL_STATUS', 'True', 'Do you want to accept iDEAL payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MSP_IDEAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MSP_IDEAL_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Direct iDeal', 'MODULE_PAYMENT_MSP_IDEAL_DIRECT', 'True', 'Select the bank within the website?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    /**
     * 
     * @return type
     */
    function keys()
    {
        return array
            (
            'MODULE_PAYMENT_MSP_IDEAL_STATUS',
            'MODULE_PAYMENT_MSP_IDEAL_SORT_ORDER',
            'MODULE_PAYMENT_MSP_IDEAL_ZONE',
            'MODULE_PAYMENT_MSP_IDEAL_DIRECT'
        );
    }

    /**
     * 
     * @return string
     */
    function javascript_validation()
    {
        $js = 'var issuer = document.checkout_payment.msp_issuer.value;';
        $js .= 'if(issuer != 0){';
        $js .= 'var payment = document.getElementsByName("payment");';
        $js .= 'document.checkout_payment.payment.value = \'multisafepay_ideal\';';
        $js .= 'return true;';
        $js .= '};';

        return $js;
    }

}

?>