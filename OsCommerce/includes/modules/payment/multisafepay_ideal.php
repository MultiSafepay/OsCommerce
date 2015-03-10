<?php

require( "multisafepay.php" );

class multisafepay_ideal extends multisafepay {

    var $icon = "ideal.png";
    var $issuer = '';

    /*
     * Constructor
     */

    function multisafepay_ideal() {
        global $order;
        $this->code = 'multisafepay_ideal';
        $this->title = $this->getTitle('iDEAL');
        $this->public_title = $this->getTitle('iDEAL');
        $this->description = $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>MultiSafepay iDeal</b><BR>The main MultiSafepay module must be installed (does not have to be active) to use this payment method.<BR>";
        $this->enabled = MODULE_PAYMENT_MSP_IDEAL_STATUS == 'True';
        $this->sort_order = MODULE_PAYMENT_MSP_IDEAL_SORT_ORDER;


        if (is_object($order))
            $this->update_status();
    }

    function create_iDeal_box() {
        $msp = new MultiSafepayAPI();
        $msp->plugin_name = $this->plugin_name;
        $msp->test = (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live account');
        $msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID;
        $msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_ID;
        $msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE;

        $iDealIssuers = $msp->getIdealIssuers();

        $output = '<div class="idealbox" style="padding:20px;border:1px solid #d50172; margin-top:20px;text-align:center">';
        $output .= '<img src="images/multisafepay/en/ideal-big.jpg" border="0" width="113" height="88"/><br /><br />';
        $output .= "<select name='msp_issuer' style='width:164px; padding: 2px; margin-left: 7px;'>";
        $output .='<option>Kies uw bank</option>';

        if ($msp->test) {
            foreach ($iDealIssuers['issuers'] as $issuer) {
                $output .= '<option value="' . $issuer['code']['VALUE'] . '">' . $issuer['description']['VALUE'] . '</option>';
            }
        } else {
            foreach ($iDealIssuers['issuers']['issuer'] as $issuer) {
                $output .= '<option value="' . $issuer['code']['VALUE'] . '">' . $issuer['description']['VALUE'] . '</option>';
            }
        }
        $output .= '</select><div style="clear:both;"></div></div><br />';
        return ($output);
    }

    /*
     * Check whether this payment module is available
     */

    function update_status() {
        if ($this->enabled && ((int) MODULE_PAYMENT_MSP_IDEAL_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_MSP_IDEAL_ZONE . "' AND zone_country_id = '" . $GLOBALS['order']->billing['country']['id'] . "' ORDER BY zone_id");

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

    function process_button() {
        if (MODULE_PAYMENT_MSP_IDEAL_DIRECT == 'True') {
            return tep_draw_hidden_field('msp_paymentmethod', 'IDEAL') . $this->create_iDeal_box();
        } else {
            return tep_draw_hidden_field('msp_paymentmethod', 'IDEAL');
        }
    }

    /*
     * Checks whether the payment has been “installed” through the admin panel
     */

    function check() {
        if (!isset($this->_check)) {
            $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MSP_IDEAL_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /*
     * Installs the configuration keys into the database
     */

    function install() {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable MultiSafepay iDEAL Module', 'MODULE_PAYMENT_MSP_IDEAL_STATUS', 'True', 'Do you want to accept iDEAL payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MSP_IDEAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MSP_IDEAL_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Direct iDeal', 'MODULE_PAYMENT_MSP_IDEAL_DIRECT', 'True', 'Select the bank within the website?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function keys() {
        return array
            (
            'MODULE_PAYMENT_MSP_IDEAL_STATUS',
            'MODULE_PAYMENT_MSP_IDEAL_SORT_ORDER',
            'MODULE_PAYMENT_MSP_IDEAL_ZONE',
            'MODULE_PAYMENT_MSP_IDEAL_DIRECT'
        );
    }

}

?>