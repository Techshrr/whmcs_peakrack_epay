# 升级说明

## 2.0.2

无需数据库迁移。

本版本加强后台 `签名方式` 切换脚本，兼容 WHMCS 模板把网关设置字段名或表格行渲染成不同结构的情况。选择 `V1 / MD5` 时，RSA 密钥行应更稳定地隐藏；选择 `V2 / RSA` 时，MD5 商户密钥行应更稳定地隐藏。

## 2.0.1

无需数据库迁移。

网关后台现在会根据当前 `签名方式` 自动切换要显示的密钥字段：

- `V1 / MD5` 显示并要求填写 `商户密钥 / KEY`。
- `V2 / RSA` 显示并要求填写 `商户私钥 / PRIVATE KEY` 和 `平台公钥`。
- 隐藏字段不会被禁用，所以切换模式并保存时，WHMCS 仍会保留这些字段已有的值。

## 2.0.0

无需数据库迁移。

已有安装会继续使用 `V1 / MD5`，除非你手动把新的 `签名方式` 切换为 `V2 / RSA`。

启用 V2/RSA 时：

- 确认 WHMCS 服务器已启用 PHP OpenSSL 扩展。
- 将 `签名方式` 设置为 `V2 / RSA`。
- `商户私钥 / PRIVATE KEY` 填写易支付 RSA 密钥对生成时得到的商户私钥。
- `平台公钥` 填写易支付 API 信息页展示的平台公钥。
- 如果易支付后台开启的是 MD5+RSA 兼容模式，建议保留 `商户密钥 / KEY`，便于兼容 MD5 回调验签。

本模块仍然把客户页面跳转提交到 `submit.php`；V2/RSA 模式会增加 `timestamp`，使用 SHA256WithRSA 签名，并用平台公钥验签 RSA 回调。

## 1.0.2

无需数据库迁移。

- 中文 / English 后台即时切换按钮现在显示在 `Submit URL` 说明行。
- `Submit URL` 可直接粘贴易支付后台复制出来的尾部斜杠地址，例如 `https://pay.idcli.com/`。
- 客户付款按钮改为内置 SVG 图标和更紧凑的响应式布局。

## 1.0.1

原来的 `支付方式` 单一输入框已改为多个支付方式开关：

- `启用支付宝`
- `启用微信支付`
- `启用 QQ 钱包`
- `启用网银支付`
- `启用收银台`
- `自定义支付类型`

客户现在会在发票付款页选择具体方式。旧版保存的 `paymentType` 只在新开关字段不存在时作为兼容回退。

已移除保存型 `后台语言` 下拉框。请使用网关标题右上角的中文 / English 按钮即时切换。

## 1.0.0

初始版本。把所有文件安装到对应 WHMCS 网关路径：

```text
peakrack_epay.php            -> modules/gateways/peakrack_epay.php
peakrack_epay/               -> modules/gateways/peakrack_epay/
callback/peakrack_epay.php   -> modules/gateways/callback/peakrack_epay.php
```

安装后启用 `PeakRack EPay (易支付)` 并配置：

- `Submit URL`
- `商户 ID / PID`
- `商户密钥 / KEY`
- 支付方式开关
- `订单号前缀`
- 如果 WHMCS 发票货币不是 CNY，请设置 `Convert To For Processing = CNY`
