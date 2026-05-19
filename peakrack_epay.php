<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/peakrack_epay/lib.php';

function peakrack_epay_MetaData()
{
    return [
        'DisplayName' => 'PeakRack EPay (易支付)',
        'APIVersion' => '1.1',
    ];
}

function whmcs_peakrack_epay_admin_normalize_language($language): string
{
    return in_array((string) $language, ['zh', 'en'], true) ? (string) $language : '';
}

function whmcs_peakrack_epay_admin_language(): string
{
    $cookieName = 'prk_epay_admin_lang';
    $requestLanguage = whmcs_peakrack_epay_admin_normalize_language($_GET['prk_epay_admin_lang'] ?? '');
    if ($requestLanguage !== '') {
        $_COOKIE[$cookieName] = $requestLanguage;
        if (!headers_sent()) {
            setcookie($cookieName, $requestLanguage, time() + 31536000, '', '', false, true);
        }

        return $requestLanguage;
    }

    $cookieLanguage = whmcs_peakrack_epay_admin_normalize_language($_COOKIE[$cookieName] ?? '');
    if ($cookieLanguage !== '') {
        return $cookieLanguage;
    }

    try {
        if (class_exists('\WHMCS\Database\Capsule')) {
            $row = \WHMCS\Database\Capsule::table('tblpaymentgateways')
                ->where('gateway', 'peakrack_epay')
                ->whereIn('setting', ['adminLanguage', 'adminlanguage', 'AdminLanguage'])
                ->first(['value']);
            $storedLanguage = whmcs_peakrack_epay_admin_normalize_language($row->value ?? '');
            if ($storedLanguage !== '') {
                return $storedLanguage;
            }
        }
    } catch (Throwable $e) {
    }

    return 'zh';
}

function whmcs_peakrack_epay_admin_text(string $language, string $key): string
{
    $texts = [
        'zh' => [
            'admin_title' => 'PeakRack 易支付网关配置',
            'admin_subtitle' => '用于兼容易支付 V1/MD5 与 V2/RSA 的页面跳转支付接口。请填写易支付平台提供的商户 ID、密钥和 submit.php 地址。',
            'version_badge' => '版本 2.0.0',
            'language_zh' => '中文',
            'language_en' => 'English',
            'credentials_title' => '易支付凭据',
            'credentials_desc' => 'Submit URL 通常是易支付站点的 /submit.php。V1 使用 MD5 密钥；V2 使用商户私钥签名、平台公钥验签。',
            'order_title' => '订单与展示',
            'order_desc' => '控制客户前台可选择的支付方式、商户订单号前缀和易支付页面展示的网站名称。',
            'security_title' => '金额校验与回调',
            'security_desc' => '建议保持金额校验开启。notify_url 不带查询参数，return_url 会带 return=1 用于客户浏览器返回。',
            'help_title' => '上线检查',
            'help_desc' => '回调地址为 modules/gateways/callback/peakrack_epay.php。多币种站点请把此网关的 Convert To For Processing 设置为 CNY。',
            'submit_url' => 'Submit URL',
            'language_switch' => '后台语言',
            'submit_url_desc' => '易支付页面跳转支付地址，例如 https://pay.example.com/。复制接口地址时尾部斜杠可保留，模块会自动追加 submit.php。',
            'api_version' => '签名方式',
            'api_version_desc' => '默认使用 V1 / MD5。选择 V2 / RSA 时，付款请求会增加 timestamp，并使用商户私钥进行 SHA256WithRSA 签名。',
            'merchant_id' => '商户 ID / PID',
            'merchant_id_desc' => '易支付商户后台提供的 pid。',
            'merchant_key' => '商户密钥 / KEY',
            'merchant_key_desc' => '易支付商户后台提供的 MD5 签名密钥。V1 必填；V2 兼容模式下可作为 MD5 回调备用验签。',
            'merchant_private_key' => '商户私钥 / PRIVATE KEY',
            'merchant_private_key_desc' => 'V2/RSA 必填。这里填写生成密钥对时得到的商户私钥，不是商户公钥；商户公钥需要填到易支付后台。',
            'platform_public_key' => '平台公钥',
            'platform_public_key_desc' => 'V2/RSA 必填。填写易支付 API 信息页展示的平台公钥，用于验签回调。',
            'enable_alipay' => '启用支付宝',
            'enable_alipay_desc' => '客户前台显示支付宝付款按钮，提交 type=alipay。',
            'enable_wxpay' => '启用微信支付',
            'enable_wxpay_desc' => '客户前台显示微信支付按钮，提交 type=wxpay。',
            'enable_qqpay' => '启用 QQ 钱包',
            'enable_qqpay_desc' => '客户前台显示 QQ 钱包按钮，提交 type=qqpay。',
            'enable_bank' => '启用网银支付',
            'enable_bank_desc' => '客户前台显示网银支付按钮，提交 type=bank。',
            'enable_cashier' => '启用收银台',
            'enable_cashier_desc' => '客户前台显示易支付收银台按钮，不提交 type，由易支付平台让客户选择。',
            'custom_types' => '自定义支付类型',
            'custom_types_desc' => '可选。多个用英文逗号分隔，例如 usdt,paypal。会原样作为 type 提交。',
            'order_prefix' => '订单号前缀',
            'order_prefix_desc' => '只允许字母、数字和下划线。仅影响易支付商户订单号，不影响 WHMCS 发票号。',
            'site_name' => '网站名称',
            'site_name_desc' => '传给易支付的 sitename。留空时使用 WHMCS 公司名称。',
            'verify_amount' => '校验金额',
            'verify_amount_desc' => '建议开启。回调入账前校验易支付返回的 CNY 金额是否等于发起支付时的 CNY 金额。',
        ],
        'en' => [
            'admin_title' => 'PeakRack EPay Gateway Configuration',
            'admin_subtitle' => 'Configure EPay-compatible V1/MD5 and V2/RSA hosted payment. Enter the merchant ID, keys, and submit.php URL from your EPay provider.',
            'version_badge' => 'Version 2.0.0',
            'language_zh' => '中文',
            'language_en' => 'English',
            'credentials_title' => 'EPay Credentials',
            'credentials_desc' => 'Submit URL is usually the provider /submit.php endpoint. V1 uses the MD5 merchant key; V2 signs with the merchant private key and verifies callbacks with the platform public key.',
            'order_title' => 'Order and Display',
            'order_desc' => 'Controls the payment methods customers can choose, merchant order prefix, and site name shown by the EPay provider.',
            'security_title' => 'Amount Verification and Callback',
            'security_desc' => 'Amount verification should remain enabled. notify_url has no query string; return_url includes return=1 for browser returns.',
            'help_title' => 'Go-Live Checklist',
            'help_desc' => 'The callback endpoint is modules/gateways/callback/peakrack_epay.php. For multi-currency stores, set this gateway\'s Convert To For Processing option to CNY.',
            'submit_url' => 'Submit URL',
            'language_switch' => 'Admin Language',
            'submit_url_desc' => 'Hosted payment endpoint, for example https://pay.example.com/. A copied trailing slash may be kept; the module appends submit.php automatically.',
            'api_version' => 'Signature Mode',
            'api_version_desc' => 'Default is V1 / MD5. V2 / RSA adds timestamp and signs payment requests with SHA256WithRSA using the merchant private key.',
            'merchant_id' => 'Merchant ID / PID',
            'merchant_id_desc' => 'pid from the EPay merchant dashboard.',
            'merchant_key' => 'Merchant Key',
            'merchant_key_desc' => 'MD5 signing key from the EPay merchant dashboard. Required for V1 and used as an MD5 callback fallback in compatible V2 mode.',
            'merchant_private_key' => 'Merchant Private Key',
            'merchant_private_key_desc' => 'Required for V2/RSA. Paste the merchant private key generated with the RSA key pair, not the merchant public key. Upload the merchant public key to the EPay dashboard.',
            'platform_public_key' => 'Platform Public Key',
            'platform_public_key_desc' => 'Required for V2/RSA. Paste the platform public key from the EPay API information page to verify callbacks.',
            'enable_alipay' => 'Enable Alipay',
            'enable_alipay_desc' => 'Show an Alipay button to customers and submit type=alipay.',
            'enable_wxpay' => 'Enable WeChat Pay',
            'enable_wxpay_desc' => 'Show a WeChat Pay button to customers and submit type=wxpay.',
            'enable_qqpay' => 'Enable QQ Wallet',
            'enable_qqpay_desc' => 'Show a QQ Wallet button to customers and submit type=qqpay.',
            'enable_bank' => 'Enable Online Banking',
            'enable_bank_desc' => 'Show an online banking button to customers and submit type=bank.',
            'enable_cashier' => 'Enable Cashier',
            'enable_cashier_desc' => 'Show an EPay cashier button without type so the provider lets customers choose.',
            'custom_types' => 'Custom Payment Types',
            'custom_types_desc' => 'Optional. Separate multiple type codes with commas, for example usdt,paypal. They are submitted as type values.',
            'order_prefix' => 'Order Prefix',
            'order_prefix_desc' => 'Letters, numbers, and underscores only. This only affects the EPay merchant order number.',
            'site_name' => 'Site Name',
            'site_name_desc' => 'sitename sent to EPay. When empty, the WHMCS company name is used.',
            'verify_amount' => 'Verify Amount',
            'verify_amount_desc' => 'Recommended. Before applying payment, verify the returned CNY amount equals the original CNY payment amount.',
        ],
    ];

    return $texts[$language][$key] ?? $texts['zh'][$key] ?? $key;
}

function whmcs_peakrack_epay_admin_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function whmcs_peakrack_epay_admin_system(string $html): array
{
    return [
        'FriendlyName' => '',
        'Type' => 'System',
        'Value' => $html,
    ];
}

function whmcs_peakrack_epay_admin_language_links(string $language): string
{
    $zhUrl = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_language_url('zh'));
    $enUrl = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_language_url('en'));
    $base = 'display:inline-block;margin-left:6px;padding:3px 8px;border:1px solid #cfd8e3;border-radius:4px;text-decoration:none;font-size:12px;font-weight:700;';
    $inactive = $base . 'background:#fff;color:#475569;';
    $active = $base . 'background:#0f766e;color:#fff;';

    return '<a style="' . ($language === 'zh' ? $active : $inactive) . '" href="' . $zhUrl . '">中文</a>'
        . '<a style="' . ($language === 'en' ? $active : $inactive) . '" href="' . $enUrl . '">English</a>';
}

function whmcs_peakrack_epay_admin_language_url(string $language): string
{
    $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $queryString = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_QUERY);
    $query = [];
    if ($queryString !== '') {
        parse_str($queryString, $query);
    }
    $query['prk_epay_admin_lang'] = $language;

    return ($path !== '' ? $path : '') . '?' . http_build_query($query);
}

function whmcs_peakrack_epay_admin_intro(string $language): array
{
    $title = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_text($language, 'admin_title'));
    $subtitle = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_text($language, 'admin_subtitle'));
    $badge = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_text($language, 'version_badge'));
    $zhUrl = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_language_url('zh'));
    $enUrl = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_language_url('en'));
    $zhLabel = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_text($language, 'language_zh'));
    $enLabel = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_text($language, 'language_en'));

    return whmcs_peakrack_epay_admin_system('<style>
.prk-gw-admin{box-sizing:border-box;border:1px solid #d8e0ea;border-radius:6px;background:#fff;margin:8px 0 12px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
.prk-gw-admin__head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:14px 16px;border-bottom:1px solid #e7edf3;background:#fbfcfe}
.prk-gw-admin__title{margin:0 0 4px;font-size:16px;font-weight:700;color:#111827}
.prk-gw-admin__desc{margin:0;color:#6b7280;font-size:12px;line-height:1.5}
.prk-gw-admin__actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:flex-end}
.prk-gw-admin__badge{display:inline-flex;align-items:center;border-radius:999px;padding:3px 9px;background:#f0fdfa;color:#0f766e;border:1px solid #99f6e4;font-size:12px;font-weight:700;white-space:nowrap}
.prk-gw-lang{display:inline-flex;border:1px solid #cfd8e3;border-radius:6px;background:#fff;overflow:hidden}
.prk-gw-lang a{display:inline-flex;align-items:center;padding:6px 9px;color:#475569;text-decoration:none;font-size:12px;font-weight:700}
.prk-gw-lang a.active{background:#0f766e;color:#fff}
.prk-gw-section{box-sizing:border-box;border:1px solid #e7edf3;border-radius:6px;background:#fbfcfe;margin:8px 0;padding:12px 14px}
.prk-gw-section h4{margin:0 0 4px;font-size:14px;font-weight:700;color:#111827}
.prk-gw-section p{margin:0;color:#6b7280;font-size:12px;line-height:1.5}
@media (max-width:700px){.prk-gw-admin__head{display:block}.prk-gw-admin__badge{margin-top:10px}}
</style><div class="prk-gw-admin"><div class="prk-gw-admin__head"><div><h3 class="prk-gw-admin__title">' . $title . '</h3><p class="prk-gw-admin__desc">' . $subtitle . '</p></div><div class="prk-gw-admin__actions"><span class="prk-gw-admin__badge">' . $badge . '</span><div class="prk-gw-lang"><a class="' . ($language === 'zh' ? 'active' : '') . '" href="' . $zhUrl . '">' . $zhLabel . '</a><a class="' . ($language === 'en' ? 'active' : '') . '" href="' . $enUrl . '">' . $enLabel . '</a></div></div></div></div>');
}

function whmcs_peakrack_epay_admin_section(string $language, string $titleKey, string $descKey): array
{
    $title = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_text($language, $titleKey));
    $desc = whmcs_peakrack_epay_admin_e(whmcs_peakrack_epay_admin_text($language, $descKey));

    return whmcs_peakrack_epay_admin_system('<div class="prk-gw-section"><h4>' . $title . '</h4><p>' . $desc . '</p></div>');
}

function whmcs_peakrack_epay_payment_layout_class(): string
{
    $requestUri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
    $scriptName = strtolower((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $phpSelf = strtolower((string) ($_SERVER['PHP_SELF'] ?? ''));
    $action = strtolower((string) ($_GET['a'] ?? ''));

    if (($action === 'complete' && (strpos($requestUri, 'cart.php') !== false
            || strpos($scriptName, 'cart.php') !== false
            || strpos($phpSelf, 'cart.php') !== false))
        || strpos($requestUri, 'cart.php?a=complete') !== false
    ) {
        return 'prk-epay-payment-options--forward';
    }

    if (strpos($requestUri, 'viewinvoice.php') !== false
        || strpos($scriptName, 'viewinvoice.php') !== false
        || strpos($phpSelf, 'viewinvoice.php') !== false
    ) {
        return 'prk-epay-payment-options--invoice';
    }

    return 'prk-epay-payment-options--forward';
}

function peakrack_epay_config()
{
    $language = whmcs_peakrack_epay_admin_language();
    $t = static fn(string $key): string => whmcs_peakrack_epay_admin_text($language, $key);

    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'PeakRack EPay (易支付)',
        ],
        'adminUiIntro' => whmcs_peakrack_epay_admin_intro($language),
        'credentialsSection' => whmcs_peakrack_epay_admin_section($language, 'credentials_title', 'credentials_desc'),
        'submitUrl' => [
            'FriendlyName' => $t('submit_url'),
            'Type' => 'text',
            'Size' => '80',
            'Default' => '',
            'Description' => $t('submit_url_desc') . '<br>' . $t('language_switch') . ' ' . whmcs_peakrack_epay_admin_language_links($language),
        ],
        'apiVersion' => [
            'FriendlyName' => $t('api_version'),
            'Type' => 'dropdown',
            'Options' => 'V1 / MD5,V2 / RSA',
            'Default' => 'V1 / MD5',
            'Description' => $t('api_version_desc'),
        ],
        'merchantId' => [
            'FriendlyName' => $t('merchant_id'),
            'Type' => 'text',
            'Size' => '20',
            'Default' => '',
            'Description' => $t('merchant_id_desc'),
        ],
        'merchantKey' => [
            'FriendlyName' => $t('merchant_key'),
            'Type' => 'password',
            'Size' => '64',
            'Default' => '',
            'Description' => $t('merchant_key_desc'),
        ],
        'merchantPrivateKey' => [
            'FriendlyName' => $t('merchant_private_key'),
            'Type' => 'textarea',
            'Rows' => '7',
            'Cols' => '80',
            'Default' => '',
            'Description' => $t('merchant_private_key_desc'),
        ],
        'platformPublicKey' => [
            'FriendlyName' => $t('platform_public_key'),
            'Type' => 'textarea',
            'Rows' => '5',
            'Cols' => '80',
            'Default' => '',
            'Description' => $t('platform_public_key_desc'),
        ],
        'orderSection' => whmcs_peakrack_epay_admin_section($language, 'order_title', 'order_desc'),
        'enableAlipay' => [
            'FriendlyName' => $t('enable_alipay'),
            'Type' => 'yesno',
            'Default' => 'on',
            'Description' => $t('enable_alipay_desc'),
        ],
        'enableWxpay' => [
            'FriendlyName' => $t('enable_wxpay'),
            'Type' => 'yesno',
            'Default' => 'on',
            'Description' => $t('enable_wxpay_desc'),
        ],
        'enableQqpay' => [
            'FriendlyName' => $t('enable_qqpay'),
            'Type' => 'yesno',
            'Description' => $t('enable_qqpay_desc'),
        ],
        'enableBank' => [
            'FriendlyName' => $t('enable_bank'),
            'Type' => 'yesno',
            'Description' => $t('enable_bank_desc'),
        ],
        'enableCashier' => [
            'FriendlyName' => $t('enable_cashier'),
            'Type' => 'yesno',
            'Description' => $t('enable_cashier_desc'),
        ],
        'customPaymentTypes' => [
            'FriendlyName' => $t('custom_types'),
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => $t('custom_types_desc'),
        ],
        'orderPrefix' => [
            'FriendlyName' => $t('order_prefix'),
            'Type' => 'text',
            'Size' => '20',
            'Default' => 'PRK_',
            'Description' => $t('order_prefix_desc'),
        ],
        'siteName' => [
            'FriendlyName' => $t('site_name'),
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => $t('site_name_desc'),
        ],
        'securitySection' => whmcs_peakrack_epay_admin_section($language, 'security_title', 'security_desc'),
        'verifyAmount' => [
            'FriendlyName' => $t('verify_amount'),
            'Type' => 'yesno',
            'Default' => 'on',
            'Description' => $t('verify_amount_desc'),
        ],
        'helpSection' => whmcs_peakrack_epay_admin_section($language, 'help_title', 'help_desc'),
    ];
}

function peakrack_epay_link($params)
{
    $apiMode = whmcs_peakrack_epay_api_mode($params);
    $requiredFields = ['submitUrl', 'merchantId'];
    if ($apiMode === 'v2_rsa') {
        $requiredFields[] = 'merchantPrivateKey';
        $requiredFields[] = 'platformPublicKey';
    } else {
        $requiredFields[] = 'merchantKey';
    }

    foreach ($requiredFields as $requiredField) {
        if (empty($params[$requiredField])) {
            return whmcs_peakrack_epay_alert(
                'warning',
                whmcs_peakrack_epay_lang('missing_config', $params, ['field' => $requiredField])
            );
        }
    }

    if ($apiMode === 'v2_rsa' && (!function_exists('openssl_sign') || !function_exists('openssl_pkey_get_private'))) {
        return whmcs_peakrack_epay_alert('warning', whmcs_peakrack_epay_lang('openssl_missing', $params));
    }

    $submitUrl = whmcs_peakrack_epay_normalize_submit_url($params['submitUrl']);
    if ($submitUrl === '' || !filter_var($submitUrl, FILTER_VALIDATE_URL)) {
        return whmcs_peakrack_epay_alert('warning', whmcs_peakrack_epay_lang('invalid_submit_url', $params));
    }

    $invoiceId = (int) $params['invoiceid'];
    $amount = whmcs_peakrack_epay_format_amount($params['amount']);

    if ((float) $amount < 0.01) {
        return whmcs_peakrack_epay_alert('warning', whmcs_peakrack_epay_lang('min_amount', $params));
    }

    $currency = strtoupper((string) ($params['currency'] ?? ''));
    if ($currency !== '' && $currency !== 'CNY') {
        return whmcs_peakrack_epay_alert(
            'warning',
            whmcs_peakrack_epay_lang('currency_error', $params, ['currency' => $currency])
        );
    }

    $systemUrl = rtrim((string) $params['systemurl'], '/');
    $callbackUrl = $systemUrl . '/modules/gateways/callback/peakrack_epay.php';
    $returnUrl = $callbackUrl . '?return=1';
    $invoiceLabel = whmcs_peakrack_epay_clean_display_text(
        $params['companyname'] . ' - Invoice #' . ($params['invoicenum'] ?: $invoiceId)
    );
    $itemSummary = whmcs_peakrack_epay_invoice_item_summary($invoiceId);
    $firstItem = whmcs_peakrack_epay_clean_display_text($itemSummary['first_item'], $invoiceLabel);
    $name = whmcs_peakrack_epay_truncate($firstItem, 127);
    $siteName = whmcs_peakrack_epay_clean_display_text($params['siteName'] ?? '', $params['companyname'] ?? '');

    $baseRequestParams = [
        'pid' => trim((string) $params['merchantId']),
        'notify_url' => $callbackUrl,
        'return_url' => $returnUrl,
        'name' => $name,
        'money' => $amount,
        'param' => whmcs_peakrack_epay_build_param($invoiceId, $amount, 'CNY'),
    ];

    if ($siteName !== '') {
        $baseRequestParams['sitename'] = whmcs_peakrack_epay_truncate($siteName, 64);
    }

    $paymentTypes = whmcs_peakrack_epay_enabled_payment_types($params);
    $buttonStyles = '<style>
.prk-epay-payment-options {
    box-sizing: border-box;
    margin: 16px auto 0;
    max-width: 520px;
    width: 100%;
}
.prk-epay-payment-options__grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(var(--prk-epay-cols, 1), minmax(0, 148px));
    justify-content: center;
    margin: 0 auto;
    max-width: var(--prk-epay-max-width, 148px);
    width: 100%;
}
.prk-epay-payment-form {
    margin: 0;
    min-width: 0;
    width: 100%;
}
.prk-epay-payment-button.btn {
    align-items: center !important;
    background: #ffffff !important;
    border: 1px solid #dbe4ef !important;
    border-radius: 8px !important;
    box-shadow: 0 1px 2px rgba(16, 24, 40, .05) !important;
    box-sizing: border-box !important;
    color: #111827 !important;
    display: flex !important;
    gap: 9px !important;
    height: 48px !important;
    justify-content: center !important;
    line-height: 1 !important;
    min-width: 0 !important;
    padding: 8px 10px !important;
    text-align: center !important;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    width: 100% !important;
}
.prk-epay-payment-button.btn:hover,
.prk-epay-payment-button.btn:focus {
    border-color: #2563eb !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, .16) !important;
    color: #0f172a !important;
    outline: none !important;
    transform: translateY(-1px) !important;
}
.prk-epay-payment-button__icon {
    align-items: center !important;
    display: inline-flex !important;
    flex: 0 0 26px !important;
    height: 26px !important;
    justify-content: center !important;
    line-height: 0 !important;
    width: 26px !important;
}
.prk-epay-payment-button__icon svg,
.prk-epay-payment-button__icon img {
    display: block !important;
    height: 26px !important;
    object-fit: contain !important;
    width: 26px !important;
}
.prk-epay-payment-button__icon--alipay img {
    transform: translateY(2px) !important;
}
.prk-epay-payment-button__label {
    display: block !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    line-height: 18px !important;
    margin: 0 !important;
    min-width: 0 !important;
    overflow: hidden !important;
    padding: 0 !important;
    text-overflow: ellipsis !important;
}
@media (max-width: 520px) {
    .prk-epay-payment-options {
        margin-top: 12px;
        max-width: 100%;
    }
    .prk-epay-payment-options__grid {
        grid-template-columns: 1fr;
        max-width: 100%;
    }
}
.prk-epay-payment-options--invoice {
    margin-top: 12px;
    max-width: 100%;
}
.prk-epay-payment-options--invoice .prk-epay-payment-options__grid {
    grid-template-columns: 1fr !important;
    max-width: 100%;
}
.prk-epay-payment-options--invoice .prk-epay-payment-button__icon--alipay img {
    transform: translateY(4px) !important;
}
</style>';

    $forms = '';
    foreach ($paymentTypes as $paymentType) {
        $requestParams = $baseRequestParams;
        $requestParams['out_trade_no'] = whmcs_peakrack_epay_out_trade_no($invoiceId, $params['orderPrefix'] ?? 'PRK_');
        if ($paymentType !== 'cashier') {
            $requestParams['type'] = $paymentType;
        }

        if ($apiMode === 'v2_rsa') {
            $requestParams['timestamp'] = (string) time();
            try {
                $requestParams['sign'] = whmcs_peakrack_epay_rsa_sign($requestParams, $params['merchantPrivateKey']);
            } catch (Throwable $e) {
                return whmcs_peakrack_epay_alert(
                    'danger',
                    whmcs_peakrack_epay_lang('signing_failed', $params, ['message' => $e->getMessage()])
                );
            }
            $requestParams['sign_type'] = 'RSA';
        } else {
            try {
                $requestParams['sign'] = whmcs_peakrack_epay_sign($requestParams, $params['merchantKey']);
            } catch (Throwable $e) {
                return whmcs_peakrack_epay_alert(
                    'danger',
                    whmcs_peakrack_epay_lang('signing_failed', $params, ['message' => $e->getMessage()])
                );
            }
            $requestParams['sign_type'] = 'MD5';
        }

        $buttonLabel = whmcs_peakrack_epay_payment_type_label($paymentType, $params);

        $forms .= '<form class="prk-epay-payment-form" method="post" accept-charset="UTF-8" action="' . htmlspecialchars($submitUrl, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . whmcs_peakrack_epay_render_hidden_inputs($requestParams)
        . '<button type="submit" class="btn btn-primary prk-epay-payment-button">'
        . '<span class="prk-epay-payment-button__icon prk-epay-payment-button__icon--' . htmlspecialchars($paymentType, ENT_QUOTES, 'UTF-8') . '">' . whmcs_peakrack_epay_payment_type_icon($paymentType, $params) . '</span>'
        . '<span class="prk-epay-payment-button__label">' . htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8') . '</span>'
        . '</button>' . "\n"
        . '</form>';
    }

    $columns = min(max(count($paymentTypes), 1), 3);
    $gridMaxWidth = (148 * $columns) + (10 * max(0, $columns - 1));
    $layoutClass = whmcs_peakrack_epay_payment_layout_class();

    return $buttonStyles
        . '<div class="prk-epay-payment-options ' . htmlspecialchars($layoutClass, ENT_QUOTES, 'UTF-8') . '">'
        . '<div class="prk-epay-payment-options__grid" style="--prk-epay-cols:' . (int) $columns . ';--prk-epay-max-width:' . (int) $gridMaxWidth . 'px;">'
        . $forms
        . '</div>'
        . '</div>';
}
