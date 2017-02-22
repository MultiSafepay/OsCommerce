<?php

require( "multisafepay.php" );

class multisafepay_ferbuy extends multisafepay {

    var $icon = "ferbuy.png";

    /*
     * Constructor
     */

    function multisafepay_ferbuy()
    {
        global $order;
        
        $this->code = 'multisafepay_ferbuy';
        $this->title = $this->getTitle('Ferbuy');
        $this->public_title = $this->getTitle('Ferbuy');
        $this->description = $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>MultiSafepay Ferbuy</b><BR>The main MultiSafepay module must be installed (does not have to be active) to use this payment method.<BR>";
        $this->enabled = MODULE_PAYMENT_MSP_FERBUY_STATUS == 'True';
        $this->sort_order = MODULE_PAYMENT_MSP_FERBUY_SORT_ORDER;


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

        if (($this->enabled == true) && ((int) MODULE_PAYMENT_MSP_FERBUY_ZONE > 0))
        {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MSP_FERBUY_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
            
            while ($check = tep_db_fetch_array($check_query))
            {
                if ($check['zone_id'] < 1)
                {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->billing['zone_id']) {
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

        return tep_draw_hidden_field('msp_paymentmethod', 'FERBUY');
    }

    /*
     * Checks whether the payment has been “installed” through the admin panel
     */

    function check()
    {
        if (!isset($this->_check))
        {
            $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MSP_FERBUY_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        
        return $this->_check;
    }

    /**
     * Configuration keys
     */

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable MultiSafepay Ferbuy Module', 'MODULE_PAYMENT_MSP_FERBUY_STATUS', 'True', 'Do you want to accept Ferbuy payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MSP_FERBUY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MSP_FERBUY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
    }

    /**
     * 
     * @return type
     */
    
    function keys()
    {
        return array
        (
            'MODULE_PAYMENT_MSP_FERBUY_STATUS',
            'MODULE_PAYMENT_MSP_FERBUY_SORT_ORDER',
            'MODULE_PAYMENT_MSP_FERBUY_ZONE',
        );
    }

}

?>