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

    require(DIR_WS_CLASSES . "order_total.php");
    $order_total_modules = new order_total();

    //Set some globals (expected by osCommerce)
    $customer_id = $order->customer['id'];
    $order_totals = $order->totals;

    //Update order status
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
