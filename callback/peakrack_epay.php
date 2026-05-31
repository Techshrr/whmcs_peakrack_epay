<?php
// SPDX-License-Identifier: Apache-2.0

/**
 * PeakRack WHMCS EPay Gateway
 *
 * Official repository:
 * https://github.com/Techshrr/whmcs_peakrack_epay
 *
 * Copyright 2026 PeakRack.
 * Licensed under the Apache License, Version 2.0.
 * See the LICENSE and NOTICE files for details.
 */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../peakrack_epay/lib.php';

$gatewayModuleName = 'peakrack_epay';
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (empty($gatewayParams['type'])) {
    die('Module Not Activated');
}

function whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, $message)
{
    if ($isReturn) {
        if ($invoiceId > 0) {
            header('Location: ' . rtrim($gatewayParams['systemurl'], '/') . '/viewinvoice.php?id=' . (int) $invoiceId);
            exit;
        }

        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        exit;
    }

    echo $message;
    exit;
}

function whmcs_peakrack_epay_callback_transaction_exists($transactionId)
{
    if ($transactionId === '' || !class_exists('\WHMCS\Database\Capsule')) {
        return false;
    }

    return \WHMCS\Database\Capsule::table('tblaccounts')
        ->where('transid', $transactionId)
        ->exists();
}

$isReturn = isset($_GET['return']);
$returnInvoiceId = (int) ($_GET['invoiceid'] ?? 0);
$requestParams = $_POST ?: $_GET;
unset($requestParams['return']);
unset($requestParams['invoiceid']);

$safeLogData = $requestParams;
$safeLogData['callback_mode'] = $isReturn ? 'return' : 'notify';
if ($returnInvoiceId > 0) {
    $safeLogData['return_invoiceid'] = $returnInvoiceId;
}
$invoiceId = 0;

if (empty($requestParams)) {
    logTransaction($gatewayModuleName, [], 'Empty Callback');
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $returnInvoiceId, 'failure');
}

if (empty($requestParams['pid']) || (string) $requestParams['pid'] !== (string) $gatewayParams['merchantId']) {
    logTransaction($gatewayModuleName, $safeLogData, 'Invalid Merchant ID');
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $returnInvoiceId, 'failure');
}

if (!whmcs_peakrack_epay_verify_callback($requestParams, $gatewayParams)) {
    logTransaction($gatewayModuleName, $safeLogData, 'Signature Verification Failed');
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $returnInvoiceId, 'failure');
}

$outTradeNo = (string) ($requestParams['out_trade_no'] ?? '');
$param = whmcs_peakrack_epay_decode_param($requestParams['param'] ?? '');
$invoiceId = (int) ($param['invoiceid'] ?? 0);
if ($invoiceId <= 0) {
    $invoiceId = whmcs_peakrack_epay_invoice_id_from_out_trade_no($outTradeNo, $gatewayParams['orderPrefix'] ?? 'PRK_');
}
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['paymentmethod']);

$transactionId = '';
foreach (['trade_no', 'api_trade_no', 'transaction_id', 'transactionId', 'out_trade_no'] as $transactionField) {
    if (!empty($requestParams[$transactionField])) {
        $transactionId = (string) $requestParams[$transactionField];
        break;
    }
}
$paymentAmount = whmcs_peakrack_epay_format_amount($requestParams['money'] ?? $requestParams['amount'] ?? $requestParams['total_amount'] ?? $requestParams['total_fee'] ?? 0);

if (!whmcs_peakrack_epay_is_success_status($requestParams)) {
    logTransaction($gatewayModuleName, $safeLogData, 'Ignored Status: ' . ($requestParams['trade_status'] ?? $requestParams['status'] ?? 'unknown'));
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'success');
}

if ($transactionId === '') {
    logTransaction($gatewayModuleName, $safeLogData, 'Missing Transaction ID');
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'failure');
}

if ((float) $paymentAmount <= 0) {
    logTransaction($gatewayModuleName, $safeLogData, 'Invalid Payment Amount');
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'failure');
}

if (($gatewayParams['verifyAmount'] ?? '') === 'on') {
    $expectedAmount = isset($param['expected_amount'])
        ? whmcs_peakrack_epay_format_amount($param['expected_amount'])
        : null;

    if ($expectedAmount === null || (float) $expectedAmount <= 0) {
        logTransaction($gatewayModuleName, $safeLogData, 'Missing Expected Amount');
        whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'failure');
    }

    if (!whmcs_peakrack_epay_amounts_match($expectedAmount, $paymentAmount)) {
        $safeLogData['expected_gateway_amount'] = $expectedAmount;
        $safeLogData['actual_gateway_amount'] = $paymentAmount;
        logTransaction($gatewayModuleName, $safeLogData, 'Amount Mismatch');
        whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'failure');
    }
}

if (whmcs_peakrack_epay_callback_transaction_exists($transactionId)) {
    logTransaction($gatewayModuleName, $safeLogData, 'Duplicate Transaction');
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'success');
}

checkCbTransID($transactionId);

$invoiceBalance = whmcs_peakrack_epay_invoice_balance($invoiceId);
if ($invoiceBalance !== null && (float) $invoiceBalance <= 0.0) {
    logTransaction($gatewayModuleName, $safeLogData, 'Invoice Already Paid');
    whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'success');
}

logTransaction($gatewayModuleName, $safeLogData, 'Successful');
addInvoicePayment(
    $invoiceId,
    $transactionId,
    $paymentAmount,
    0.00,
    $gatewayParams['paymentmethod']
);

whmcs_peakrack_epay_callback_finish($isReturn, $gatewayParams, $invoiceId, 'success');
