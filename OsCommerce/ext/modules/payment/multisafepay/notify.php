<?php

/*
  MultiSafepay Payment Module for osCommerce 
  http://www.multisafepay.com

  Copyright (C) 2008 MultiSafepay.com
 */

chdir("../../../../");
require("includes/application_top.php");
include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);

$initial_request = ($_GET['type'] == 'initial');

if (empty($_GET['transactionid'])) {
    $message = "No transaction ID supplied";
    $url = tep_href_link(
            FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $payment_module->code . '&error=' . urlencode($message), 'NONSSL', true, false
    );
} else {
    // load selected payment module
    require(DIR_WS_CLASSES . "payment.php");
    $payment_modules = new payment("multisafepay");
    $payment_module = $GLOBALS[$payment_modules->selected_module];

    require(DIR_WS_CLASSES . "order.php");
    $order = new order($_GET['transactionid']);

    $order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . $order->info['orders_status'] . "' AND language_id = '" . $languages_id . "'");
    $order_status = tep_db_fetch_array($order_status_query);
    $order->info['order_status'] = $order_status['orders_status_id'];

    //require(DIR_WS_CLASSES . "order_total.php");
    //$order_total_modules = new order_total();
    // set some globals (expected by osCommerce)
    $customer_id = $order->customer['id'];
    $order_totals = $order->totals;

    // update order status
    $payment_module->order_id = $_GET['transactionid'];
    $status = $payment_module->update_order();

    switch ($status) {
        default:
            $message = "OK";
            $url = tep_href_link("ext/modules/payment/multisafepay/success.php", '', 'NONSSL');
    }
}

if ($initial_request) {
    echo "<p><a href=\"" . $url . "\">" . sprintf(MODULE_PAYMENT_MULTISAFEPAY_TEXT_RETURN_TO_SHOP, htmlspecialchars(STORE_NAME)) . "</a></p>";
} else {
    header("Content-type: text/plain");
    echo $message;
}
?>
