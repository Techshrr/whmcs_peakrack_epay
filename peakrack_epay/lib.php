<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function whmcs_peakrack_epay_is_chinese_language(array $params = [])
{
    $candidates = [];

    $candidates[] = $_GET['language'] ?? '';
    $candidates[] = $_POST['language'] ?? '';
    $candidates[] = $_GET['lang'] ?? '';
    $candidates[] = $_POST['lang'] ?? '';

    if (class_exists('\WHMCS\Session') && method_exists('\WHMCS\Session', 'get')) {
        $candidates[] = \WHMCS\Session::get('Language');
        $candidates[] = \WHMCS\Session::get('language');
        $candidates[] = \WHMCS\Session::get('locale');
    }

    $candidates[] = $_SESSION['Language'] ?? '';
    $candidates[] = $_SESSION['language'] ?? '';
    $candidates[] = $_SESSION['locale'] ?? '';
    $candidates[] = $_COOKIE['WHMCSCurrentLanguage'] ?? '';
    $candidates[] = $_COOKIE['WHMCSLanguage'] ?? '';
    $candidates[] = $_COOKIE['language'] ?? '';
    $candidates[] = $_COOKIE['Language'] ?? '';
    $candidates[] = $params['language'] ?? '';
    $candidates[] = $params['clientdetails']['language'] ?? '';

    foreach ($candidates as $candidate) {
        $language = strtolower(trim((string) $candidate));
        if ($language === '') {
            continue;
        }

        return strpos($language, 'chinese') !== false
            || strpos($language, 'zh') === 0
            || strpos($language, 'cn') !== false;
    }

    return false;
}

function whmcs_peakrack_epay_lang($key, array $params = [], array $replace = [])
{
    $messages = [
        'en' => [
            'missing_config' => 'PeakRack EPay is not fully configured. Missing: :field.',
            'invalid_submit_url' => 'PeakRack EPay Submit URL is invalid.',
            'min_amount' => 'EPay requires a minimum payment amount of 0.01 CNY.',
            'currency_error' => 'EPay payments expect CNY. Set this gateway\'s "Convert To For Processing" option to CNY before using it for :currency invoices.',
            'signing_failed' => 'EPay request signing failed: :message',
            'openssl_missing' => 'EPay RSA signing requires the PHP OpenSSL extension.',
            'pay_button' => 'Pay with EPay',
        ],
        'zh' => [
            'missing_config' => 'PeakRack 易支付尚未完整配置，缺少：:field。',
            'invalid_submit_url' => 'PeakRack 易支付 Submit URL 无效。',
            'min_amount' => '易支付最低支付金额为 0.01 元人民币。',
            'currency_error' => '易支付接口应使用 CNY。请先在此支付网关中把 “Convert To For Processing” 设置为 CNY，再用于 :currency 发票。',
            'signing_failed' => '易支付请求签名失败：:message',
            'openssl_missing' => '易支付 RSA 签名需要 PHP OpenSSL 扩展。',
            'pay_button' => '使用易支付支付',
        ],
    ];

    $locale = whmcs_peakrack_epay_is_chinese_language($params) ? 'zh' : 'en';
    $message = $messages[$locale][$key] ?? $messages['en'][$key] ?? $key;

    foreach ($replace as $name => $value) {
        $message = str_replace(':' . $name, (string) $value, $message);
    }

    return $message;
}

function whmcs_peakrack_epay_normalize_payment_type($type)
{
    $type = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', (string) $type));
    $aliases = [
        'wechat' => 'wxpay',
        'weixin' => 'wxpay',
        'wx' => 'wxpay',
        'qq' => 'qqpay',
        'cash' => 'cashier',
        'default' => 'cashier',
        'pay' => 'cashier',
    ];

    return $aliases[$type] ?? $type;
}

function whmcs_peakrack_epay_parse_payment_types($value)
{
    $value = str_replace(['，', '、', ';', '|', "\r", "\n", "\t"], ',', (string) $value);
    $parts = preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
    $types = [];

    foreach ($parts as $part) {
        $type = whmcs_peakrack_epay_normalize_payment_type($part);
        if ($type === '') {
            continue;
        }
        $types[$type] = $type;
    }

    return array_values($types);
}

function whmcs_peakrack_epay_enabled_payment_types(array $params)
{
    $fieldMap = [
        'enableAlipay' => 'alipay',
        'enableWxpay' => 'wxpay',
        'enableQqpay' => 'qqpay',
        'enableBank' => 'bank',
        'enableCashier' => 'cashier',
    ];
    $types = [];
    $hasToggleConfig = false;

    foreach ($fieldMap as $field => $type) {
        if (array_key_exists($field, $params)) {
            $hasToggleConfig = true;
        }
        if (($params[$field] ?? '') === 'on') {
            $types[$type] = $type;
        }
    }

    foreach (whmcs_peakrack_epay_parse_payment_types($params['customPaymentTypes'] ?? '') as $type) {
        $types[$type] = $type;
    }

    if (!$hasToggleConfig) {
        foreach (whmcs_peakrack_epay_parse_payment_types($params['paymentType'] ?? 'alipay') as $type) {
            $types[$type] = $type;
        }
    }

    if (!$types) {
        $types['alipay'] = 'alipay';
    }

    return array_values($types);
}

function whmcs_peakrack_epay_payment_type_label($type, array $params = [])
{
    $labels = [
        'zh' => [
            'alipay' => '支付宝支付',
            'wxpay' => '微信支付',
            'qqpay' => 'QQ 钱包',
            'bank' => '网银支付',
            'cashier' => '易支付收银台',
        ],
        'en' => [
            'alipay' => 'Alipay',
            'wxpay' => 'WeChat Pay',
            'qqpay' => 'QQ Wallet',
            'bank' => 'Online Banking',
            'cashier' => 'EPay Cashier',
        ],
    ];

    $locale = whmcs_peakrack_epay_is_chinese_language($params) ? 'zh' : 'en';

    return $labels[$locale][$type] ?? strtoupper((string) $type);
}

function whmcs_peakrack_epay_payment_type_icon($type, array $params = [])
{
    $type = whmcs_peakrack_epay_normalize_payment_type($type);

    if ($type === 'alipay' && !empty($params['systemurl'])) {
        $iconUrl = rtrim((string) $params['systemurl'], '/') . '/modules/gateways/peakrack_epay/alipay-logo-icon.png';

        return '<img src="' . htmlspecialchars($iconUrl, ENT_QUOTES, 'UTF-8') . '" alt="">';
    }

    $icons = [
        'alipay' => '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><rect width="32" height="32" rx="8" fill="#1677ff"/><path d="M8.7 23.8 14.7 8.2c.4-1 2.2-1 2.6 0l6 15.6h-3.5l-1.1-3.2h-7.4l-1.1 3.2H8.7Zm3.7-6h5.2L15 10.5l-2.6 7.3Z" fill="#fff"/><path d="M7.4 25.5c5.4 1.9 12.3 1.5 17.2-1.2" fill="none" stroke="#dbeafe" stroke-width="2" stroke-linecap="round"/></svg>',
        'wxpay' => '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><rect width="32" height="32" rx="8" fill="#07c160"/><path d="M13.4 9.2c-4 0-7.2 2.5-7.2 5.7 0 1.8 1 3.4 2.7 4.5l-.7 2.5 2.9-1.5c.7.2 1.5.3 2.3.3 4 0 7.2-2.5 7.2-5.8s-3.2-5.7-7.2-5.7Z" fill="#fff"/><path d="M19.3 14.1c3.5 0 6.4 2.2 6.4 5 0 1.6-.9 3-2.3 4l.6 2.1-2.5-1.3c-.7.2-1.4.3-2.2.3-3.5 0-6.4-2.2-6.4-5s2.9-5.1 6.4-5.1Z" fill="#dffbea"/><circle cx="10.8" cy="14" r="1.1" fill="#07c160"/><circle cx="15.8" cy="14" r="1.1" fill="#07c160"/><circle cx="17.2" cy="19" r="1" fill="#07c160"/><circle cx="21.7" cy="19" r="1" fill="#07c160"/></svg>',
        'qqpay' => '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><rect width="32" height="32" rx="8" fill="#111827"/><path d="M16 6.8c-3.2 0-5.6 2.9-5.6 7.1 0 1.3.2 2.4.7 3.5l-2.3 4.3c-.4.7.2 1.5 1 1.3l2.6-.5c.9 1.3 2.1 2 3.6 2s2.8-.8 3.6-2l2.6.5c.8.2 1.4-.6 1-1.3l-2.3-4.3c.4-1 .7-2.2.7-3.5 0-4.2-2.4-7.1-5.6-7.1Z" fill="#fff"/><circle cx="13.7" cy="13.4" r="1" fill="#111827"/><circle cx="18.3" cy="13.4" r="1" fill="#111827"/><path d="M13.6 17.9c1.3.7 3.5.7 4.8 0" fill="none" stroke="#111827" stroke-width="1.4" stroke-linecap="round"/></svg>',
        'bank' => '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><rect width="32" height="32" rx="8" fill="#475569"/><path d="M7 13h18L16 7l-9 6Zm2 2v7m5-7v7m5-7v7m5-7v7M7 25h18" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'cashier' => '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><rect width="32" height="32" rx="8" fill="#0f766e"/><path d="M8 11.5h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Zm0 4h18" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 9h12" stroke="#ccfbf1" stroke-width="2.2" stroke-linecap="round"/></svg>',
    ];

    return $icons[$type] ?? '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><rect width="32" height="32" rx="8" fill="#334155"/><path d="M9 12h14M9 17h14M9 22h9" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>';
}

function whmcs_peakrack_epay_normalize_submit_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }

    $path = (string) parse_url($url, PHP_URL_PATH);
    if (preg_match('#/submit\.php$#i', $path)) {
        return $url;
    }

    if (substr($url, -1) === '/') {
        return $url . 'submit.php';
    }

    return $url . '/submit.php';
}

function whmcs_peakrack_epay_build_sign_content(array $params)
{
    $filtered = [];

    foreach ($params as $key => $value) {
        if ($key === 'sign' || $key === 'sign_type') {
            continue;
        }
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        if ($value === null || $value === '') {
            continue;
        }
        $filtered[$key] = (string) $value;
    }

    ksort($filtered, SORT_STRING);

    $parts = [];
    foreach ($filtered as $key => $value) {
        $parts[] = $key . '=' . $value;
    }

    return implode('&', $parts);
}

function whmcs_peakrack_epay_api_mode(array $params)
{
    $value = strtolower(trim((string) ($params['apiVersion'] ?? '')));
    $value = str_replace([' ', '/', '-', '(', ')'], '_', $value);

    return (strpos($value, 'v2') !== false || strpos($value, 'rsa') !== false) ? 'v2_rsa' : 'v1_md5';
}

function whmcs_peakrack_epay_is_v2_mode(array $params)
{
    return whmcs_peakrack_epay_api_mode($params) === 'v2_rsa';
}

function whmcs_peakrack_epay_sign(array $params, $merchantKey)
{
    $merchantKey = trim((string) $merchantKey);
    if ($merchantKey === '') {
        throw new RuntimeException('Merchant key is empty.');
    }

    return strtolower(md5(whmcs_peakrack_epay_build_sign_content($params) . $merchantKey));
}

function whmcs_peakrack_epay_verify(array $params, $merchantKey)
{
    if (empty($params['sign'])) {
        return false;
    }

    try {
        $expected = whmcs_peakrack_epay_sign($params, $merchantKey);
    } catch (Throwable $e) {
        return false;
    }

    return hash_equals($expected, strtolower((string) $params['sign']));
}

function whmcs_peakrack_epay_clean_rsa_key($key)
{
    $key = trim((string) $key);
    if ($key === '') {
        return '';
    }

    $key = str_replace(["\r\n", "\r"], "\n", $key);
    $key = preg_replace('/^\xEF\xBB\xBF/', '', $key);

    return trim((string) $key);
}

function whmcs_peakrack_epay_rsa_pem_candidates($key, $type)
{
    $key = whmcs_peakrack_epay_clean_rsa_key($key);
    if ($key === '') {
        return [];
    }

    if (strpos($key, '-----BEGIN ') !== false) {
        return [$key];
    }

    $body = preg_replace('/\s+/', '', $key);
    if ($body === '') {
        return [];
    }

    $body = chunk_split($body, 64, "\n");
    if ($type === 'private') {
        return [
            "-----BEGIN PRIVATE KEY-----\n" . $body . "-----END PRIVATE KEY-----",
            "-----BEGIN RSA PRIVATE KEY-----\n" . $body . "-----END RSA PRIVATE KEY-----",
        ];
    }

    return [
        "-----BEGIN PUBLIC KEY-----\n" . $body . "-----END PUBLIC KEY-----",
        "-----BEGIN RSA PUBLIC KEY-----\n" . $body . "-----END RSA PUBLIC KEY-----",
    ];
}

function whmcs_peakrack_epay_rsa_sign(array $params, $merchantPrivateKey)
{
    if (!function_exists('openssl_sign') || !function_exists('openssl_pkey_get_private')) {
        throw new RuntimeException('PHP OpenSSL extension is not available.');
    }

    $content = whmcs_peakrack_epay_build_sign_content($params);
    foreach (whmcs_peakrack_epay_rsa_pem_candidates($merchantPrivateKey, 'private') as $candidate) {
        $privateKey = @openssl_pkey_get_private($candidate);
        if ($privateKey === false) {
            continue;
        }

        $signature = '';
        if (@openssl_sign($content, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return base64_encode($signature);
        }
    }

    throw new RuntimeException('Merchant RSA private key is invalid.');
}

function whmcs_peakrack_epay_rsa_verify(array $params, $platformPublicKey)
{
    if (empty($params['sign']) || !function_exists('openssl_verify') || !function_exists('openssl_pkey_get_public')) {
        return false;
    }

    $signature = base64_decode(str_replace(' ', '+', trim((string) $params['sign'])), true);
    if ($signature === false) {
        return false;
    }

    $content = whmcs_peakrack_epay_build_sign_content($params);
    foreach (whmcs_peakrack_epay_rsa_pem_candidates($platformPublicKey, 'public') as $candidate) {
        $publicKey = @openssl_pkey_get_public($candidate);
        if ($publicKey === false) {
            continue;
        }

        if (@openssl_verify($content, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1) {
            return true;
        }
    }

    return false;
}

function whmcs_peakrack_epay_verify_callback(array $params, array $gatewayParams)
{
    $signType = strtolower(preg_replace('/[^a-z0-9]/', '', (string) ($params['sign_type'] ?? '')));
    $tryRsa = in_array($signType, ['rsa', 'rsa2', 'sha256withrsa'], true);
    $tryMd5 = $signType === 'md5';

    if (!$tryRsa && !$tryMd5) {
        $tryRsa = whmcs_peakrack_epay_is_v2_mode($gatewayParams);
        $tryMd5 = true;
    }

    if ($tryRsa
        && !empty($gatewayParams['platformPublicKey'])
        && whmcs_peakrack_epay_rsa_verify($params, $gatewayParams['platformPublicKey'])
    ) {
        return true;
    }

    if ($tryMd5
        && !empty($gatewayParams['merchantKey'])
        && whmcs_peakrack_epay_verify($params, $gatewayParams['merchantKey'])
    ) {
        return true;
    }

    return false;
}

function whmcs_peakrack_epay_format_amount($amount)
{
    return number_format((float) $amount, 2, '.', '');
}

function whmcs_peakrack_epay_amounts_match($expected, $actual)
{
    return abs((float) $expected - (float) $actual) < 0.01;
}

function whmcs_peakrack_epay_invoice_balance($invoiceId)
{
    if (!function_exists('localAPI')) {
        return null;
    }

    $invoice = localAPI('GetInvoice', ['invoiceid' => (int) $invoiceId]);
    if (!is_array($invoice) || ($invoice['result'] ?? '') !== 'success') {
        return null;
    }

    if (isset($invoice['balance'])) {
        return whmcs_peakrack_epay_format_amount($invoice['balance']);
    }

    if (isset($invoice['total'])) {
        return whmcs_peakrack_epay_format_amount($invoice['total']);
    }

    return null;
}

function whmcs_peakrack_epay_clean_display_text($value, $fallback = '')
{
    $value = (string) $value;

    if ($value !== '' && function_exists('mb_check_encoding') && !mb_check_encoding($value, 'UTF-8')) {
        $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8,GB18030,GBK,GB2312,ISO-8859-1');
        if (is_string($converted)) {
            $value = $converted;
        }
    }

    $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = str_replace(["\r", "\n", "\t"], ' ', $value);

    if (preg_match('//u', $value)) {
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);
    } else {
        $value = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
    }

    $value = trim((string) $value);

    return $value !== '' ? $value : (string) $fallback;
}

function whmcs_peakrack_epay_truncate($value, $length)
{
    $value = trim((string) $value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length, 'UTF-8');
    }

    return substr($value, 0, $length);
}

function whmcs_peakrack_epay_invoice_item_summary($invoiceId)
{
    $invoiceId = (int) $invoiceId;
    $summary = [
        'first_item' => '',
        'item_count' => 0,
    ];

    if ($invoiceId <= 0) {
        return $summary;
    }

    try {
        if (class_exists('\WHMCS\Database\Capsule')) {
            $items = \WHMCS\Database\Capsule::table('tblinvoiceitems')
                ->where('invoiceid', $invoiceId)
                ->orderBy('id', 'asc')
                ->get(['description']);

            foreach ($items as $item) {
                $summary['item_count']++;
                if ($summary['first_item'] === '') {
                    $summary['first_item'] = whmcs_peakrack_epay_clean_display_text($item->description ?? '');
                }
            }

            return $summary;
        }
    } catch (Throwable $e) {
        $summary = [
            'first_item' => '',
            'item_count' => 0,
        ];
    }

    try {
        if (function_exists('localAPI')) {
            $invoice = localAPI('GetInvoice', ['invoiceid' => $invoiceId]);
            if (is_array($invoice) && ($invoice['result'] ?? '') === 'success') {
                $items = $invoice['items']['item'] ?? [];
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $summary['item_count']++;
                        if ($summary['first_item'] === '') {
                            $summary['first_item'] = whmcs_peakrack_epay_clean_display_text($item['description'] ?? '');
                        }
                    }
                }
            }
        }
    } catch (Throwable $e) {
        return [
            'first_item' => '',
            'item_count' => 0,
        ];
    }

    return $summary;
}

function whmcs_peakrack_epay_sanitize_prefix($prefix)
{
    $prefix = preg_replace('/[^A-Za-z0-9_]/', '', (string) $prefix);
    if ($prefix === '') {
        $prefix = 'PRK_';
    }

    return substr($prefix, 0, 24);
}

function whmcs_peakrack_epay_out_trade_no($invoiceId, $prefix)
{
    $invoiceId = (int) $invoiceId;
    $suffix = strtoupper(substr(base_convert((string) time(), 10, 36), -6));

    try {
        $suffix .= strtoupper(bin2hex(random_bytes(2)));
    } catch (Throwable $e) {
        $suffix .= strtoupper(substr(md5((string) microtime(true)), 0, 4));
    }

    $tail = $invoiceId . '_' . $suffix;
    $prefix = whmcs_peakrack_epay_sanitize_prefix($prefix);
    $prefix = substr($prefix, 0, max(0, 64 - strlen($tail)));

    return substr($prefix . $tail, 0, 64);
}

function whmcs_peakrack_epay_selection_token_secret(array $params)
{
    foreach (['merchantKey', 'merchantPrivateKey'] as $field) {
        $value = trim((string) ($params[$field] ?? ''));
        if ($value !== '') {
            return hash('sha256', 'peakrack_epay|' . $value);
        }
    }

    return hash('sha256', 'peakrack_epay|' . (string) ($params['merchantId'] ?? ''));
}

function whmcs_peakrack_epay_selection_token_payload($invoiceId, $amount, $currency, $paymentType, $issuedAt = null)
{
    $parts = [
        (int) $invoiceId,
        whmcs_peakrack_epay_format_amount($amount),
        strtoupper((string) $currency),
        whmcs_peakrack_epay_normalize_payment_type($paymentType),
    ];

    if ($issuedAt !== null) {
        $parts[] = (string) (int) $issuedAt;
    }

    return implode('|', $parts);
}

function whmcs_peakrack_epay_selection_token(array $params, $invoiceId, $amount, $currency, $paymentType, $issuedAt = null)
{
    return hash_hmac(
        'sha256',
        whmcs_peakrack_epay_selection_token_payload($invoiceId, $amount, $currency, $paymentType, $issuedAt),
        whmcs_peakrack_epay_selection_token_secret($params)
    );
}

function whmcs_peakrack_epay_verify_selection_token(array $params, $invoiceId, $amount, $currency, $paymentType, $token, $issuedAt = null)
{
    $expected = whmcs_peakrack_epay_selection_token($params, $invoiceId, $amount, $currency, $paymentType, $issuedAt);

    return hash_equals($expected, (string) $token);
}

function whmcs_peakrack_epay_selection_token_is_fresh($issuedAt, $ttlSeconds = 7200)
{
    $issuedAt = (int) $issuedAt;
    if ($issuedAt <= 0) {
        return false;
    }

    return abs(time() - $issuedAt) <= (int) $ttlSeconds;
}

function whmcs_peakrack_epay_payment_type_is_enabled($paymentType, array $params)
{
    $paymentType = whmcs_peakrack_epay_normalize_payment_type($paymentType);
    if ($paymentType === '') {
        return false;
    }

    return in_array($paymentType, whmcs_peakrack_epay_enabled_payment_types($params), true);
}

function whmcs_peakrack_epay_build_payment_request(array $params, $invoiceId, $amount, $paymentType)
{
    $invoiceId = (int) $invoiceId;
    $amount = whmcs_peakrack_epay_format_amount($amount);
    $paymentType = whmcs_peakrack_epay_normalize_payment_type($paymentType);
    $systemUrl = rtrim((string) ($params['systemurl'] ?? ''), '/');
    $callbackUrl = $systemUrl . '/modules/gateways/callback/peakrack_epay.php';
    $invoiceNumber = $params['invoicenum'] ?? $invoiceId;
    $invoiceLabel = whmcs_peakrack_epay_clean_display_text(
        ($params['companyname'] ?? '') . ' - Invoice #' . ($invoiceNumber ?: $invoiceId)
    );
    $itemSummary = whmcs_peakrack_epay_invoice_item_summary($invoiceId);
    $firstItem = whmcs_peakrack_epay_clean_display_text($itemSummary['first_item'], $invoiceLabel);
    $siteName = whmcs_peakrack_epay_clean_display_text($params['siteName'] ?? '', $params['companyname'] ?? '');

    $requestParams = [
        'pid' => trim((string) ($params['merchantId'] ?? '')),
        'notify_url' => $callbackUrl,
        'return_url' => $callbackUrl . '?return=1&invoiceid=' . $invoiceId,
        'name' => whmcs_peakrack_epay_truncate($firstItem, 127),
        'money' => $amount,
        'param' => whmcs_peakrack_epay_build_param($invoiceId, $amount, 'CNY'),
        'out_trade_no' => whmcs_peakrack_epay_out_trade_no($invoiceId, $params['orderPrefix'] ?? 'PRK_'),
    ];

    if ($paymentType !== 'cashier') {
        $requestParams['type'] = $paymentType;
    }

    if ($siteName !== '') {
        $requestParams['sitename'] = whmcs_peakrack_epay_truncate($siteName, 64);
    }

    if (whmcs_peakrack_epay_is_v2_mode($params)) {
        $requestParams['timestamp'] = (string) time();
        $requestParams['sign'] = whmcs_peakrack_epay_rsa_sign($requestParams, $params['merchantPrivateKey'] ?? '');
        $requestParams['sign_type'] = 'RSA';

        return $requestParams;
    }

    $requestParams['sign'] = whmcs_peakrack_epay_sign($requestParams, $params['merchantKey'] ?? '');
    $requestParams['sign_type'] = 'MD5';

    return $requestParams;
}

function whmcs_peakrack_epay_invoice_id_from_out_trade_no($outTradeNo, $prefix)
{
    $outTradeNo = (string) $outTradeNo;
    $prefix = whmcs_peakrack_epay_sanitize_prefix($prefix);

    if (strpos($outTradeNo, $prefix) === 0) {
        $candidate = substr($outTradeNo, strlen($prefix));
        if (preg_match('/^(\d+)(?:_[A-Za-z0-9]+)?$/', $candidate, $matches)) {
            return (int) $matches[1];
        }
    }

    if (preg_match('/(?:^|_)(\d+)(?:_[A-Za-z0-9]+)?$/', $outTradeNo, $matches)) {
        return (int) $matches[1];
    }

    if (preg_match('/(\d+)/', $outTradeNo, $matches)) {
        return (int) $matches[1];
    }

    return 0;
}

function whmcs_peakrack_epay_base64url_encode($value)
{
    return rtrim(strtr(base64_encode((string) $value), '+/', '-_'), '=');
}

function whmcs_peakrack_epay_base64url_decode($value)
{
    $value = strtr((string) $value, '-_', '+/');
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode($value, true);

    return $decoded === false ? '' : $decoded;
}

function whmcs_peakrack_epay_build_param($invoiceId, $amount, $currency = 'CNY')
{
    $json = json_encode(
        [
            'invoiceid' => (int) $invoiceId,
            'expected_amount' => whmcs_peakrack_epay_format_amount($amount),
            'expected_currency' => strtoupper((string) $currency),
        ],
        JSON_UNESCAPED_SLASHES
    );

    return whmcs_peakrack_epay_base64url_encode($json ?: '');
}

function whmcs_peakrack_epay_decode_param($param)
{
    $decoded = whmcs_peakrack_epay_base64url_decode($param);
    if ($decoded === '') {
        return [];
    }

    $data = json_decode($decoded, true);

    return is_array($data) ? $data : [];
}

function whmcs_peakrack_epay_is_success_status(array $params)
{
    $tradeStatus = strtoupper(trim((string) ($params['trade_status'] ?? '')));
    if (in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED', 'SUCCESS', 'PAID'], true)) {
        return true;
    }

    $status = strtolower(trim((string) ($params['status'] ?? '')));

    return in_array($status, ['1', 'success', 'trade_success', 'trade_finished', 'paid', 'complete', 'completed'], true);
}

function whmcs_peakrack_epay_render_hidden_inputs(array $params)
{
    $html = '';
    foreach ($params as $key => $value) {
        $html .= '<input type="hidden" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    return $html;
}

function whmcs_peakrack_epay_alert($type, $message)
{
    return '<div class="alert alert-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
        . '</div>';
}
