# 升级说明

本文档用于说明如何从旧版本升级本网关。

## 升级前准备

1. 备份 WHMCS 文件。
2. 备份 WHMCS 数据库。
3. 复制一份当前网关文件、支持目录和回调文件。
4. 升级前阅读 [CHANGELOG.md](CHANGELOG.md)。
5. 确认本次升级是否会影响网关配置项。

## 升级步骤

1. 从官方仓库下载最新版本：

   https://github.com/Techshrr/whmcs_peakrack_epay

2. 将网关文件替换到 WHMCS 对应目录：

   `modules/gateways/peakrack_epay.php`

   `modules/gateways/peakrack_epay/`

   `modules/gateways/callback/peakrack_epay.php`

3. 保留 WHMCS 网关设置中的商户凭据和私钥。
4. 登录 WHMCS 后台。
5. 打开网关设置，检查所有配置项。
6. 如果发票付款按钮没有更新，请清理 WHMCS 模板缓存。

## 数据库迁移

本版本不需要手动执行数据库迁移。

## 版本升级说明

### 从 2.1.1 升级到 2.1.2

- 不需要数据库变更。
- 现有商户凭据、启用的支付方式、签名模式和订单号前缀设置会保留。
- 支付成功回调现在会使用网关确认的实际支付金额写入 WHMCS 发票。
- 当 WHMCS 语言为 `zh-hk` 或等效繁体中文语言时，客户提示会显示香港繁体中文。
- 网关配置页面现在包含 GitHub 仓库入口和浏览器侧更新提示；该后台显示不需要服务端迁移。

### 从 2.0.x 升级到 2.1.x

- 不需要数据库变更。
- 必须存在本地 redirect 端点 `modules/gateways/peakrack_epay/redirect.php`。
- 现有 V1/MD5 和 V2/RSA 凭据会保留在 WHMCS 网关设置中。

### 从 1.x 升级到 2.x

- 现有安装会继续使用 `V1 / MD5`，除非管理员修改 `Signature Mode`。
- 如需启用 `V2 / RSA`，请确认 PHP OpenSSL 可用，并配置商户私钥和平台公钥。

## 回滚方法

如需回滚：

1. 恢复旧版本网关文件、支持目录和回调文件。
2. 如果升级修改过 WHMCS 记录，恢复数据库备份。
3. 清理 WHMCS 模板缓存。
4. 检查 WHMCS 网关日志和活动日志是否有错误。

## 注意事项

不要覆盖生产环境密钥、本地配置文件、自定义模板、回调密钥或支付凭据，除非升级说明明确要求。
