<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/lib.php';

$gatewayModuleName = 'peakrack_epay';
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (empty($gatewayParams['type'])) {
    http_response_code(403);
    die('Module Not Activated');
}

function whmcs_peakrack_epay_redirect_fail($message)
{
    http_response_code(400);
    echo '<!doctype html><html><head><meta charset="utf-8"><title>PeakRack EPay</title></head><body>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
    exit;
}

$invoiceId = (int) ($_POST['invoiceid'] ?? 0);
$amount = whmcs_peakrack_epay_format_amount($_POST['amount'] ?? 0);
$currency = strtoupper((string) ($_POST['currency'] ?? ''));
$paymentType = whmcs_peakrack_epay_normalize_payment_type($_POST['type'] ?? '');
$issuedAt = (int) ($_POST['issued'] ?? 0);
$token = (string) ($_POST['token'] ?? '');

if ($invoiceId <= 0 || (float) $amount < 0.01 || $currency !== 'CNY' || $paymentType === '' || $issuedAt <= 0 || $token === '') {
    whmcs_peakrack_epay_redirect_fail('Invalid payment selection.');
}

if (!whmcs_peakrack_epay_selection_token_is_fresh($issuedAt)) {
    whmcs_peakrack_epay_redirect_fail('Payment selection expired. Please reload the invoice and try again.');
}

if (!whmcs_peakrack_epay_payment_type_is_enabled($paymentType, $gatewayParams)) {
    whmcs_peakrack_epay_redirect_fail('Payment type is not enabled.');
}

if (!whmcs_peakrack_epay_verify_selection_token($gatewayParams, $invoiceId, $amount, $currency, $paymentType, $token, $issuedAt)) {
    whmcs_peakrack_epay_redirect_fail('Invalid payment token.');
}

$invoiceBalance = whmcs_peakrack_epay_invoice_balance($invoiceId);
if ($invoiceBalance !== null && (float) $invoiceBalance <= 0.0) {
    header('Location: ' . rtrim($gatewayParams['systemurl'], '/') . '/viewinvoice.php?id=' . $invoiceId);
    exit;
}

$submitUrl = whmcs_peakrack_epay_normalize_submit_url($gatewayParams['submitUrl'] ?? '');
if ($submitUrl === '' || !filter_var($submitUrl, FILTER_VALIDATE_URL)) {
    whmcs_peakrack_epay_redirect_fail('Invalid EPay Submit URL.');
}

try {
    $requestParams = whmcs_peakrack_epay_build_payment_request($gatewayParams, $invoiceId, $amount, $paymentType);
} catch (Throwable $e) {
    whmcs_peakrack_epay_redirect_fail('EPay request signing failed.');
}

echo '<!doctype html><html><head><meta charset="utf-8"><title>PeakRack EPay</title></head>';
echo '<body onload="document.forms[0].submit()">';
echo '<form method="post" accept-charset="UTF-8" action="' . htmlspecialchars($submitUrl, ENT_QUOTES, 'UTF-8') . '">';
echo whmcs_peakrack_epay_render_hidden_inputs($requestParams);
echo '<noscript><button type="submit">Continue to payment</button></noscript>';
echo '</form>';
echo '</body></html>';
