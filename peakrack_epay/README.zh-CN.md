# PeakRack 易支付资源目录

此目录需要上传到 WHMCS：

```text
modules/gateways/peakrack_epay/
```

包含：

- `lib.php`：签名、验签、金额、订单号、语言与展示辅助函数
- `alipay-logo-icon.png`：支付宝付款按钮图标
- `whmcs.json`：WHMCS 网关元数据

主网关文件位于源码根目录：

```text
peakrack_epay.php -> modules/gateways/peakrack_epay.php
```

回调文件位于：

```text
callback/peakrack_epay.php -> modules/gateways/callback/peakrack_epay.php
```
