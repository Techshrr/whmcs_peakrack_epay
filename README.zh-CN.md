# PeakRack WHMCS EPay Gateway

> 官方仓库：https://github.com/Techshrr/whmcs_peakrack_epay
> 许可证：Apache License 2.0

PeakRack WHMCS EPay Gateway 是一个用于易支付兼容页面跳转支付平台的 WHMCS 支付网关。

## 项目说明

本支付网关把客户提交到易支付兼容平台的 `submit.php` 页面跳转支付接口，并通过 WHMCS 网关回调路径处理异步通知和浏览器返回。

模块支持 V1/MD5 签名和 V2/RSA 兼容页面跳转模式。后台可以启用多个支付方式，但只有客户选择具体支付方式后才会创建平台订单。

## 功能特性

- 易支付 `submit.php` 页面跳转支付提交。
- V1/MD5 请求签名和回调验签。
- V2/RSA 兼容签名，支持 `timestamp` 和 SHA256WithRSA。
- 支持支付宝、微信支付、QQ 钱包、网银、收银台和自定义平台类型。
- 使用本地 redirect 端点处理客户选择的支付方式。
- WHMCS 发票回调入账和重复交易检查。
- 可在入账前校验易支付返回的 CNY 金额。
- 后台配置标签和客户提示支持中文和英文。

## 环境要求

- WHMCS 9.0.x
- PHP 8.2 或更高版本
- 易支付兼容商户账号
- 商户 ID / PID
- V1/MD5 模式使用的商户密钥
- V2/RSA 模式需要 PHP OpenSSL 扩展、商户私钥和平台公钥
- WHMCS 回调地址可通过公网 HTTPS 访问

## 安装方法

1. 从官方仓库下载最新版本。
2. 将网关文件上传到对应 WHMCS 路径：

   `peakrack_epay.php` -> `modules/gateways/peakrack_epay.php`

   `peakrack_epay/` -> `modules/gateways/peakrack_epay/`

   `callback/peakrack_epay.php` -> `modules/gateways/callback/peakrack_epay.php`

3. 登录 WHMCS 后台。
4. 在 **System Settings > Payment Gateways** 启用 **PeakRack EPay (易支付)**。
5. 生产环境使用前，请检查所有配置项。

## 配置说明

| 配置项 | 说明 | 默认值 |
|---|---|---|
| Submit URL | 易支付页面跳转地址；模块会按需追加 `submit.php` | 空 |
| Signature Mode | 选择 V1/MD5 或 V2/RSA 签名 | V1 / MD5 |
| Merchant ID / PID | 平台提供的商户编号 | 空 |
| Merchant Key | V1 签名密钥，也可用于兼容回调验签 | 空 |
| Merchant Private Key | V2/RSA 请求签名使用的商户私钥 | 空 |
| Platform Public Key | V2/RSA 回调验签使用的平台公钥 | 空 |
| Enable Alipay | 显示支付宝付款选项 | 开启 |
| Enable WeChat Pay | 显示微信支付付款选项 | 开启 |
| Enable QQ Wallet | 显示 QQ 钱包付款选项 | 关闭 |
| Enable Online Banking | 显示网银付款选项 | 关闭 |
| Enable Cashier | 显示平台收银台选项 | 关闭 |
| Custom Payment Types | 英文逗号分隔的平台 type 值 | 空 |
| Order Prefix | 平台商户订单号前缀 | PRK_ |
| Site Name | 传给平台的网站名称 | 空 |
| Verify Amount | 入账前校验回调金额和预期 CNY 金额 | 开启 |

如果 WHMCS 是多币种站点，而易支付平台按 CNY 收款，请在 WHMCS 网关公共设置中将 **Convert To For Processing** 设置为 `CNY`。

## 使用说明

管理员配置商户凭据、签名方式、启用的支付方式、订单号前缀和金额校验开关。

客户查看发票时，网关会显示已启用的付款方式。客户选择一种方式后，本地 redirect 端点会生成签名请求并提交到平台。回调文件会校验商户 ID、签名、支付状态、交易号和金额，然后再给 WHMCS 发票入账。

## 回调地址

异步通知地址为：

`https://your-whmcs.example/modules/gateways/callback/peakrack_epay.php`

浏览器返回地址包含 `return=1` 和发票 ID，用于把客户带回发票页面。

## 升级说明

请查看 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。

## 英文文档

请查看 [README.md](README.md)。

## 安全说明

请勿提交生产环境凭据、API Key、数据库密码、支付密钥、WHMCS 授权信息、客户数据、身份证件或私有签名密钥。

安全问题报告方式请查看 [SECURITY.md](SECURITY.md)。

## 许可证

本项目基于 Apache License 2.0 发布。完整许可证请查看 [LICENSE](LICENSE)。

其他项目声明请查看 [NOTICE](NOTICE)。
