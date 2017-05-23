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
if (MODULE_PAYMENT_MULTISAFEPAY_FCO_STATUS == 'True') {
    ?>
    <div style="float:left;">
      <pre>
      </pre>
    </div>
    <div class="clear:both"></div>
    <!-- BEGIN MSP CHECKOUT -->
    <div align="right">
      <div style="width: 220px; margin-top:15px; margin-bottom:5px;">
        <div align="center">
    <?php
    if ($cart->count_contents() > 0) {
        echo '<a href="mspcheckout/process.php"><img src="mspcheckout/images/button.png" alt="Checkout" name="Checkout"></a>';
        //echo ' <p align="right" style="clear: both; padding: 15px 50px 0 0;"> OR </p>';
    }
    ?>
        </div>
      </div>
    </div>

    <?php
    // display any MSP error

    if (isset($HTTP_GET_VARS['payment_error']) && is_object(${$HTTP_GET_VARS['payment_error']}) && ($error = ${$HTTP_GET_VARS['payment_error']}->get_error())) {
        ?>
        <table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo tep_output_string_protected($error['title']); ?></b></td>
          </tr>
        </table>

        <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBoxNotice">
          <tr class="infoBoxNoticeContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                  <td class="main" width="100%" valign="top"><?php echo tep_output_string_protected($error['error']); ?></td>
                  <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                </tr>
              </table></td>
          </tr>
        </table>

        <?php
    }
    ?>

    <!-- END MSP CHECKOUT -->
    <?php
}
?>