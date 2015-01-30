<?php
/*
  $Id: ot_fixed_payment_chg.php,v 2.1 2006 xaglo Exp $
  * Order total module that displays the fee related to the payment type.
  * A method exists to allow the display of the fee from the payment type page

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
  * Modified by rigadin@osc-help.net to get it working with tax class
  * Modified by Giovanni Putignano (gputignano@tiscali.it), now display taxes correctly.
  * Modified by O.F.Y. (info@oscommerceforyou.hu), for osCommerce 2.3 correctly.
  * Modified Ruud Jonk (techsupport@multisafepay.com), added support for 'Pay after delivery' gateway fee
*/

  class ot_fixed_payment_chg {
    var $title, $output;

    function ot_fixed_payment_chg() {
      $this->code = 'ot_fixed_payment_chg';
      $this->title = MODULE_FIXED_PAYMENT_CHG_TITLE;
      $this->description = MODULE_FIXED_PAYMENT_CHG_DESCRIPTION;
      $this->enabled = MODULE_FIXED_PAYMENT_CHG_STATUS;
      $this->sort_order = MODULE_FIXED_PAYMENT_CHG_SORT_ORDER;
      $this->type = MODULE_FIXED_PAYMENT_CHG_TYPE;
      $this->tax_class = MODULE_FIXED_PAYMENT_CHG_TAX_CLASS;
      $this->output = array();
    }

    
   function process() {
      global $order, $ot_subtotal, $currencies;
      $od_amount = $this->calculate_credit();
      if ($od_amount != 0) {
        $this->deduction = $od_amount;
        $this->output[] = array('title' => $this->title . ':',
                              'text' => $currencies->format($od_amount),
                              'value' => $od_amount);
        $order->info['total'] = $order->info['total'] + $od_amount;  
		$order->info['msp_fee_inc_tax'] = $od_amount;  
      }
    }
    

  function calculate_credit() {
    global $order, $customer_id, $payment;
    $od_amount=0;
	$table = preg_split("/[:,]/" , MODULE_FIXED_PAYMENT_CHG_TYPE); 
    
    for ($i = 0; $i < count($table); $i+=3) {
      if ($payment == $table[$i]) {

$od_min_fee=$table[$i+1];
$od_fee = $table[$i+2] * $order->info['subtotal'];

if ($od_min_fee < $od_fee) {
$od_am = $od_fee;
} 
else {
$od_am = $od_min_fee;
}

       


		  // If tax class is defined, get the tax rate according to delivery country and zone
          // $tod_rate = tep_get_tax_rate(MODULE_FIXED_PAYMENT_CHG_TAX_CLASS); // Amended for tax calculation fix
          $tod_rate = tep_get_tax_rate(MODULE_FIXED_PAYMENT_CHG_TAX_CLASS,$order->delivery['country']['id'], $order->delivery['zone_id']); // Added for tax fix
          $tax_description = tep_get_tax_description(MODULE_FIXED_PAYMENT_CHG_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
		
		if ($od_min_fee < $od_fee) {
	       
			
			
			if (DISPLAY_PRICE_WITH_TAX=="true") { 
    	    	$tod_amount =  tep_calculate_tax($od_am / (1 + ($tod_rate / 100)), $tod_rate);
        	  	$order->info['tax_groups']["$tax_description"] += tep_calculate_tax($od_am / (1 + ($tod_rate / 100)), $tod_rate);
        	} else {
          		$tod_amount =  tep_calculate_tax($od_am, $tod_rate);
          		$order->info['tax_groups']["$tax_description"] += tep_calculate_tax($od_am, $tod_rate);
        	  	$order->info['total'] += $tod_amount;
        	}


       	  	$od_amount = $od_am;
		} else {
   	    	$tod_amount =  tep_calculate_tax($od_am, $tod_rate);
       	  	$order->info['tax_groups']["$tax_description"] += tep_calculate_tax($od_am, $tod_rate);
	        if (DISPLAY_PRICE_WITH_TAX=="true") { 
        	  	$od_amount = $od_am + $tod_amount;
        	} else {
        	  	$od_amount = $od_am;
        	  	$order->info['total'] += $tod_amount;
        	}


		

          $order->info['tax'] += $tod_amount;
        } 

      }
    }
    return $od_amount;
  }

  function get_payment_cost($pay_type) {
    global $order;
	
      $od_amount=0;
       $table = preg_split("/[:,]/" , MODULE_FIXED_PAYMENT_CHG_TYPE);

      
      for ($i = 0; $i < count($table); $i+=3) {
        if ($pay_type == $table[$i]) {
			$od_min_fee=$table[$i+1];
			$od_fee = $table[$i+2] * $order->info['subtotal'];

			if ($od_min_fee < $od_fee) {
				$od_am = $od_fee;
			} else {
				$od_am = $od_min_fee;
			}
          	if (MODULE_FIXED_PAYMENT_CHG_TAX_CLASS > 0) {
            $tod_rate = tep_get_tax_rate(MODULE_FIXED_PAYMENT_CHG_TAX_CLASS,$order->delivery['country']['id'], $order->delivery['zone_id']);
			if ($od_min_fee < $od_fee) {
            	if (DISPLAY_PRICE_WITH_TAX=="true") {
					$tod_amount =  tep_calculate_tax($od_am / (1 + ($tod_rate / 100)), $tod_rate);
            	} else {
            		$tod_amount =  tep_calculate_tax($od_am, $tod_rate);
            	}
		            $od_amount = $od_am;
			} else {
					$tod_amount =  tep_calculate_tax($od_am, $tod_rate);
            	if (DISPLAY_PRICE_WITH_TAX=="true") {
		            $od_amount = $od_am + $tod_amount;
            	} else {
            		$od_amount = $od_am;
            	}
			}
          }
        }
      }
      return $od_amount;
    }






    
    function check() {
      if (!isset($this->check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_FIXED_PAYMENT_CHG_STATUS'");
        $this->check = tep_db_num_rows($check_query);
      }

      return $this->check;
    }

    function keys() {
      return array('MODULE_FIXED_PAYMENT_CHG_STATUS', 'MODULE_FIXED_PAYMENT_CHG_SORT_ORDER', 'MODULE_FIXED_PAYMENT_CHG_TYPE', 'MODULE_FIXED_PAYMENT_CHG_TAX_CLASS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display fee', 'MODULE_FIXED_PAYMENT_CHG_STATUS', 'true', 'Display fee related to the payment type', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FIXED_PAYMENT_CHG_SORT_ORDER', '3', 'Display sort order.', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Fee for payment type', 'MODULE_FIXED_PAYMENT_CHG_TYPE', 'cod:2.70:0.035,paypal_ipn:0:0.03', 'Payment methods with minimal fee (any) and normal fee (0 to 1, 1 is 100%) all splitted by colons, enter like this: [cod:xx:0.yy,paypal_ipn:xx:0.yy] ', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax', 'MODULE_FIXED_PAYMENT_CHG_TAX_CLASS', '0', 'Use the following tax class:', '6', '6', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
      $keys = '';
      $keys_array = $this->keys();
      for ($i=0; $i<sizeof($keys_array); $i++) {
        $keys .= "'" . $keys_array[$i] . "',";
      }
      $keys = substr($keys, 0, -1);

      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in (" . $keys . ")");
    }
  }
?>