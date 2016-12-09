<?php

require( "multisafepay.php" );

class multisafepay_beautyandwellness extends multisafepay {

    var $icon = "beautyandwellness.png";

    /*
     * Constructor
     */

    function multisafepay_beautyandwellness()
    {
        global $order;
        $this->code = 'multisafepay_beautyandwellness';
        $this->title = $this->getTitle('Beauty and wellness');
        $this->public_title = $this->getTitle('Beauty and wellness');
        $this->description = $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>MultiSafepay Beauty and wellness</b><BR>The main MultiSafepay module must be installed (does not have to be active) to use this payment method.<BR>";
        $this->enabled = MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_STATUS == 'True';
        $this->sort_order = MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_SORT_ORDER;


        if (is_object($order))
        {
            $this->update_status();
        }
    }

    /*
     * Check whether this payment module is available
     */

    function update_status()
    {
        global $order;

        if (($this->enabled == true) && ((int) MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_ZONE > 0))
        {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

    function process_button()
    {

        return tep_draw_hidden_field('msp_paymentmethod', 'BEAUTYANDWELLNESS');
    }

    /*
     * Checks whether the payment has been “installed” through the admin panel
     */

    function check()
    {
        if (!isset($this->_check))
        {
            $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /*
     * Installs the configuration keys into the database
     */

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable MultiSafepay Beauty and wellness module', 'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_STATUS', 'True', 'Do you want to accept Bloemencadeau payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        //tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Direct iDeal', 'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_DIRECT', 'True', 'Select the bank within the website?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function keys()
    {
        return array
            (
            'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_STATUS',
            'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_SORT_ORDER',
            'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_ZONE',
                //'MODULE_PAYMENT_MSP_BEAUTYANDWELLNESS_DIRECT'
        );
    }

}

?>