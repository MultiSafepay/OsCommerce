<?php
/*

MultiSafepay Payment Module for osCommerce 1.10
http://www.multisafepay.com

Copyright (C) 2008 MultiSafepay.com
Copyright (C) 2008 Privateer Software Development

*/

define('MODULE_PAYMENT_MULTISAFEPAY_LOCALE',                'nl');

define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE',			'MultiSafepay');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_PUBLIC_TITLE',		'MultiSafepay (+ iDEAL, Credit Card en Mister Cash)');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_DESCRIPTION',		'<img src="images/icon_popup.gif" border="0">&nbsp;<a href="http://www.multisafepay.com/" style="text-decoration: underline; font-weight: bold;">Bezoek de MultiSafepay website</a>');

define('MODULE_PAYMENT_MULTISAFEPAY_EMAIL_TEXT_ORDER_STATUS', 'Orderstatus:');

define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_RETURN_TO_SHOP',	'Terug naar %s');

define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR',			'Er is een fout opgetreden');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_REDIRECT',	'Niet mogelijk om door te sturen');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_STATUS',		'Niet mogelijk om orderstatus te bepalen');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_UNKNOWN',	'Geen details beschikbaar');

define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1000',		'Ongeldig verzoek ontvangen');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1001',		'Ongeldig bedrag');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1002',		'Ongeldige valuta');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1003',		'Ongeldig merchant ID');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1004',		'Ongeldig merchant account ID');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1005',		'Ongeldig merchant security code');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1006',		'Ongeldig transactie-ID');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1007',		'Ongeldig IP-address');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1008',		'Ongeldige omschrijving');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1009',		'Ongeldig transactietype');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1010',		'Ongeldige eigen variabele (var1/var2/var3)');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1011',		'Ongeldig customer account ID');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1012',		'Ongeldig customer security code');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1013',		'MD5 komt niet overeen');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1014',		'Back-end: ongespecificeerde fout');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1015',		'Back-end: account niet gevonden');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1016',		'Back-end: ontbrekende gegevens');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1017',		'Back-end: onvoldoende saldo');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_2000',		'HTTP-verzoek mislukt');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_2001',		'Ongeldig HTTP-antwoord');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_2002',		'Ongeldig HTTP content-type');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_6666',		'Merchantfout');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_9999',		MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_UNKNOWN);
define('MODULE_PAYMENT_MSP_MISTERCASH_TEXT_TITLE',			' Bancontact/Mistercash by Multisafepay');

define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_UNCLEARED',	'Transactie is niet vrijgegeven');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_RESERVED',	'Transactie is gereserveerd');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_VOID',		'Transactie is geannuleerd');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_DECLINED',	'Transactie is afgewezen');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_REVERSED',	'Transactie is teruggedraaid');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_REFUNDED',	'Transactie is teruggeboekt');
define('MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_EXPIRED',	'Transactie is verlopen');

?>
