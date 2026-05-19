# 升级说明

## 1.0.2

无需数据库迁移。

- 中文 / English 后台即时切换按钮现在显示在 `Submit URL` 说明行。
- `Submit URL` 可直接粘贴易支付后台复制出来的尾部斜杠地址，例如 `https://pay.example.com/`。
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
