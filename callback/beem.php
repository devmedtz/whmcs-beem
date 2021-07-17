<?php
/**
 * WHMCS Sample Payment Callback File
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
global $CONFIG;

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Beem Module Not Activated");
}

// Retrieve data returned in payment gateway callback
$paymentAmount = $_GET["amount"];
$paymentFee = 0.0;
$refrenceNumber = $_GET["refrenceNumber"];
$status = $_GET["status"];
$timestamp = $_GET["timestamp"];
$transactionId = $_GET["transactionId"];
$msisdn = $_GET["msisdn"];

$keyword = $gatewayParams['keyword'];
$invoiceId = trim($refrenceNumber, "'$keyword.'-");

$systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'] . '/' : $CONFIG['SystemURL'] . '/';


//  Validate Callback Invoice ID.

$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);


//  Check Callback Transaction ID.

checkCbTransID($transactionId);

if ($status == "true") {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
    logTransaction($gatewayParams['name'], $_POST, "completed");
    #redirect to invoice page
    $invoice_url = $systemurl . 'viewinvoice.php?id=' . $invoiceId;
    //header("Location: $invoice_url");
    //exit;
} elseif ($status == "false")
    $values["status"] = "Failed";
else
    $values["status"] = "Unpaid";

