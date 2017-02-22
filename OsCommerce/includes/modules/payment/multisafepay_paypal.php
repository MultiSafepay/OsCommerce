<?php

require( "multisafepay.php" );

class multisafepay_paypal extends multisafepay {

    var $icon = "paypal.png";

    /*
     * Constructor
     */

    function multisafepay_paypal()
    {
        global $order;
        
        $this->code = 'multisafepay_paypal';
        $this->title = $this->getTitle(MODULE_PAYMENT_MSP_PAYPAL_TEXT_TITLE);
        $this->public_title = $this->getTitle(MODULE_PAYMENT_MSP_PAYPAL_TEXT_TITLE);
        $this->description = $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>MultiSafepay PayPal</b><BR>The main MultiSafepay module must be installed (does not have to be active) to use this payment method.<BR>";
        $this->enabled = MODULE_PAYMENT_MSP_PAYPAL_STATUS == 'True';
        $this->sort_order = MODULE_PAYMENT_MSP_PAYPAL_SORT_ORDER;

        if (is_object($order))
        {
            $this->update_status();
        }
    }

    /**
     * 
     * @global type $order
     */
    
    function update_status()
    {
        global $order;

        if (($this->enabled == true) && ((int) MODULE_PAYMENT_MSP_PAYPAL_ZONE > 0))
        {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MSP_PAYPAL_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
            
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

    /**
     * 
     * @return type
     */
    
    function process_button()
    {

        return tep_draw_hidden_field('msp_paymentmethod', 'PAYPAL');
    }

    /**
     * 
     * @return type
     */
    
    function check()
    {
        if (!isset($this->_check))
        {
            $check_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_MSP_PAYPAL_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        
        return $this->_check;
    }

    /*
     * Installs the configuration keys into the database
     */

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable MultiSafepay PayPal Module', 'MODULE_PAYMENT_MSP_PAYPAL_STATUS', 'True', 'Do you want to accept PayPal payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MSP_PAYPAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MSP_PAYPAL_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
    }

    /**
     * 
     * @return type
     */
    
    function keys()
    {
        return array
        (
            'MODULE_PAYMENT_MSP_PAYPAL_STATUS',
            'MODULE_PAYMENT_MSP_PAYPAL_SORT_ORDER',
            'MODULE_PAYMENT_MSP_PAYPAL_ZONE',
        );
    }

}

?>