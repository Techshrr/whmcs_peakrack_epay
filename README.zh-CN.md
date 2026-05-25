# WHMCS PeakRack 易支付网关

用于 WHMCS 9.x 的易支付兼容网关模块，支持 V1 `submit.php` 页面跳转支付、MD5 签名、V2/RSA 兼容签名、异步通知回调，以及 WHMCS 多币种转换为人民币支付。

English documentation: [README.md](README.md)

公开示例易支付站：[互六鼎付](https://pay.idcli.com/)（`Submit URL`：`https://pay.idcli.com/`）。

## 功能

- 易支付 V1 页面跳转支付 `submit.php`
- V1/MD5 请求签名和回调验签
- V2/RSA 兼容模式签名，支持 `timestamp` 和 SHA256WithRSA
- 支持后台同时启用 `alipay`、`wxpay`、`qqpay`、`bank`、收银台或自定义类型，客户付款时再选择具体方式
- WHMCS 发票回调入账
- 支持 WHMCS `Convert To For Processing = CNY`
- 易支付返回金额校验
- 后台配置页分区展示，提供中文 / English 语言切换
- 客户前台按钮和错误提示中英文切换

## 环境要求

- WHMCS 9.x 自托管安装
- 易支付兼容商户账号
- 商户 ID / PID
- V1/MD5 使用的商户密钥 / KEY
- 启用 V2/RSA 时，PHP 需要 OpenSSL 扩展
- 启用 V2/RSA 时，需要商户 RSA 私钥和平台公钥
- WHMCS 站点可通过公网 HTTPS 访问

本模块的 V2 支持面向“页面跳转兼容模式”：仍然把客户提交到 `submit.php`，但会增加 `timestamp` 并使用 RSA 签名，适合易支付后台开启 MD5+RSA 兼容签名的场景。

## 安装

把以下文件和目录上传到对应 WHMCS 网关路径：

```text
peakrack_epay.php            -> modules/gateways/peakrack_epay.php
peakrack_epay/               -> modules/gateways/peakrack_epay/
callback/peakrack_epay.php   -> modules/gateways/callback/peakrack_epay.php
```

上传后应包含：

```text
modules/gateways/peakrack_epay.php
modules/gateways/peakrack_epay/lib.php
modules/gateways/peakrack_epay/alipay-logo-icon.png
modules/gateways/peakrack_epay/whmcs.json
modules/gateways/callback/peakrack_epay.php
```

然后在 WHMCS `系统设置 > 支付网关` 中启用 `PeakRack EPay (易支付)`。

## 后台配置

填写以下字段：

- `Submit URL`，例如 `https://pay.idcli.com/`；从易支付后台复制出来的尾部 `/` 可以保留，模块会自动拼接 `submit.php`
- `签名方式`：默认使用 `V1 / MD5`；需要 RSA 时选择 `V2 / RSA`
- `商户 ID / PID`
- `商户密钥 / KEY`：V1/MD5 必填；V2 兼容模式下可作为 MD5 回调备用验签
- `商户私钥 / PRIVATE KEY`：V2/RSA 必填；这里填写生成密钥对时得到的商户私钥，不是商户公钥
- `平台公钥`：V2/RSA 必填，用于回调验签
- 勾选要启用的支付方式，例如支付宝、微信支付、QQ 钱包、网银支付或收银台
- `自定义支付类型`，可选；多个用英文逗号分隔，例如 `usdt,paypal`
- `订单号前缀`
- `网站名称`，可选

如果 WHMCS 默认货币是 USD，而易支付使用 CNY 收款，请把该网关公共设置里的：

```text
Convert To For Processing
```

设置为：

```text
CNY
```

WHMCS 会在客户跳转易支付前按后台汇率换算成人民币。易支付回调后，模块会校验人民币支付金额，再让 WHMCS 按该发票当前余额入账。

## 回调地址

模块会在每次支付请求中动态传入异步通知地址：

```text
https://你的WHMCS域名/modules/gateways/callback/peakrack_epay.php
```

客户浏览器返回地址为：

```text
https://你的WHMCS域名/modules/gateways/callback/peakrack_epay.php?return=1
```

站点必须能被易支付服务器通过公网 HTTPS 访问。

## 签名说明

模块会把所有非空参数按参数名 ASCII 升序排列，排除 `sign` 和 `sign_type`，拼接为 `key=value&key=value`。

`V1 / MD5` 模式会在待签名字符串末尾追加商户密钥后计算小写 MD5。

`V2 / RSA` 模式会增加 `timestamp`，再使用商户私钥对待签名字符串做 SHA256WithRSA 签名，签名结果 Base64 后提交 `sign_type=RSA`。

回调会按回传的 `sign_type` 验签：RSA 回调用平台公钥验签，MD5 回调用商户密钥验签。只有 `trade_status=TRADE_SUCCESS` 或兼容成功状态的回调会入账。

## 更新记录

### 2.0.0

- 增加 `签名方式` 配置，可选择 V1/MD5 或 V2/RSA。
- 增加 V2/RSA 请求签名：自动提交 `timestamp`，使用商户私钥做 SHA256WithRSA 签名，并用平台公钥验签回调。
- V1/MD5 仍然是默认模式，老安装不需要强制迁移。

### 1.0.2

- 恢复后台中文 / English 即时切换按钮，并挂到 `Submit URL` 说明行，避免恢复无实际即时作用的保存型下拉框。
- 优化 `Submit URL` 处理：从易支付后台复制 `https://pay.idcli.com/` 不需要删除尾部斜杠。
- 优化客户付款页和查看账单侧栏的支付按钮布局，改用内置 SVG 图标，不再用首字方块替代。

### 1.0.1

- 去掉无实际即时作用的后台语言保存下拉框，只保留右上角中文 / English 即时切换按钮。
- 把单一 `支付方式` 改为多个后台开关，并在客户前台生成多个支付按钮，让客户付款时选择支付宝、微信支付等具体方式。

### 1.0.0

- 初始 PeakRack 易支付网关。
- 增加页面跳转支付、MD5 签名、回调验签、CNY 金额校验和重复交易保护。

详细升级说明见 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。

## 免责声明

本项目是独立开发的 WHMCS 支付网关模块，不隶属于 WHMCS 或任何易支付平台，也未获得其官方背书。WHMCS 和相关支付平台商标归各自权利人所有。

## 开源协议

MIT License。详见 [LICENSE](LICENSE)。
