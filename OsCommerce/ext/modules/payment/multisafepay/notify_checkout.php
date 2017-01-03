<?php

chdir("../../../../");
require("includes/application_top.php");
define('DIR_WS_LANGUAGES', 'includes/languages/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('FILENAME_CHECKOUT_PROCESS', 'checkout_process.php');

include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);

if (isset($_GET['type']) && $_GET['type'] == 'initial')
{
    $initial_request = true;
}else{
    $initial_request = false;
}

if (empty($_GET['transactionid']))
{
    $message = "No transaction ID supplied";
    $url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $payment_module->code . '&error=' . urlencode($message), 'NONSSL', true, false);
} else {    
    require(DIR_WS_CLASSES . "payment.php");
    $payment_modules = new payment("multisafepay");
    $payment_module = $GLOBALS[$payment_modules->selected_module];

    require(DIR_WS_CLASSES . "order.php");
    $order = new order($_GET['transactionid']);

    $order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . $order->info['orders_status'] . "' AND language_id = '" . $languages_id . "'");
    $order_status = tep_db_fetch_array($order_status_query);
    $order->info['order_status'] = $order_status['orders_status_id'];

    require(DIR_WS_CLASSES . "order_total.php");
    $order_total_modules = new order_total();
    
    $customer_id = $order->customer['id'];
    $order_totals = $order->totals;
    
    $payment_module->order_id = $_GET['transactionid'];
    $transdata = $payment_module->check_transaction();
    
    if ($transdata->fastcheckout == 'NO')
    {
        $status = $payment_module->checkout_notify();
    } else {
        $payment_modules = new payment("multisafepay_fastcheckout");
        $payment_module = $GLOBALS[$payment_modules->selected_module];
        $status = $payment_module->checkout_notify();
    }

    if ($payment_module->_customer_id)
    {
        $hash = $payment_module->get_hash($payment_module->order_id, $payment_module->_customer_id);
        $parameters = 'customer_id=' . $payment_module->_customer_id . '&hash=' . $hash;
    }

    switch ($status)
    {
        case "initialized":
        case "completed":
            $message = "OK";
            $url = tep_href_link("ext/modules/payment/multisafepay/success.php", $parameters, 'NONSSL');
            break;
        default:
            $message = "OK";
            $url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $payment_module->code . '&error=' . urlencode($status), 'NONSSL', true, false);
    }
}

if ($initial_request)
{
    echo "<p><a href=\"" . $url . "\">" . sprintf(MODULE_PAYMENT_MULTISAFEPAY_TEXT_RETURN_TO_SHOP, htmlspecialchars(STORE_NAME)) . "</a></p>";
} else {
    header("Content-type: text/plain");
    echo $message;
}
?>
