什么是免签支付
---
免签支付就是给个人用的支付接口，一般的支付接口都需要营业执照才能申请，个人很难申请的到，或者是没有资质去申请，要和支付商进行签约的。免签，顾名思义就是不需要签约。那么个人免签支付就有市场了，就是为了解决个人无法轻易申请到支付接口的问题。

>**免签的方案有很多种**
1. APP监听收款码的支付结果，然后修改页面的支付结果。
2. 二次清算。款先到拥有官方支付接口的商户中，由商户给你结算。
3. Xposed微信插件实现全自动监听创建收款码、以及收款过程，容易封号。

方案其实有很多种，但是以上的方案都有不少的缺点，当然这些方案仍有不少人在用，没办法，确实是解决问题的一种办法。而本次文章我也是通过简单的技术开发实现第一种APP监听收款码的免签支付方式。

运行流程
---
访问URL -> 检查数据库2分钟内未支付的订单金额 -> 如果2分钟内未支付的订单金额中存在当前订单一样的金额，需要在当前金额基础上+0.01元用于区分订单的唯一性 -> 用户扫码支付 -> 安卓手机APP监听到这笔订单的收款 -> 将收款金额异步发送到服务器 -> 服务器修改数据库该笔订单金额的支付状态 -> 扫码页面一直在轮询订单的支付状态，发现已支付就修改页面的支付结果 -> 完成支付。

notify.php配置
---
notify.php是回调监听的重要程序，这个文件在server目录内，你可以使用编辑器修改里面的 `SecretKey` ，这个是用于验证生成签名的Key，自己随便设置一个就可以，不要泄漏。<br/><br/>
在监听APP设置的时候，就要填写notify.php的URL，这个URL就是notify.php在你服务器的链接。<br/>

假设你的域名是：www.qq.com <br/>
代码你上传到根目录下的pay目录，那么notify.php对应的URL是：<br/>
```
https://www.qq.com/pay/server/notify.php
```
监听APP下载
---
开源地址：https://gitee.com/pp/SmsForwarder <br>
网盘：https://wws.lanzoui.com/b025yl86h 访问密码：pppscn（建议下载3.2.0版本）

监听APP配置
---

**发送通道配置：**

打开APP，选择发送通道->Webhook->选择GET请求->输入notify.php所在服务器的URL。

消息模板填写以下参数：
```
orderMsg=[msg]&time=[timestamp]&sign=[sign]
```

`Secret` 需要和上一步的 `SecretKey` 一致。

![](https://img10.360buyimg.com/imgzone/jfs/t1/234778/20/23066/61303/66838b52F2a954ec2/731c238b2796526d.jpg)

**转发规则配置：**

2、打开APP，点击右上角+号添加规则，选择通道，按下图输入配置信息。

![](https://img10.360buyimg.com/imgzone/jfs/t1/238650/12/12322/47494/66838b52Fe909b433/717e2789dc0f2e31.jpg)

到这里，APP基本完成配置，然后将这个APP的自动启动开启，以及加入电池优化白名单，保证这个APP能一直在后台运行不被杀死。

赞赏码获取
---

为什么用赞赏码而不用收款码？因为收款码更容易被风控，收款码更加适合面对面扫码收款，而不适合线上远程收款，因为你的每一笔支付，都会记录付款ip地址，定位等信息，扫码次数多了，就会被系统判断远程付款，容易触发风控。赞赏码是用于网络上的赞赏使用，相对来说是比收款码安全的。

![image.png](https://t.focus-img.cn/sh740wsh/bbs/p2/84d0b846eb40b042864486cc7bd53bd2.png)

获得自己的赞赏码后，将赞赏码的那部分裁剪出来，替换掉源码中 **`img/qrcode.png`** 这个文件就行了。赞赏码是可以设置赞赏的引导语的，可以将引导语修改为【请点击其他金额输入】，引导用户。

数据库创建
---
请直接使用以下SQL语句创建：
```
CREATE TABLE `wxpay_zsm_orders` (
  `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `order_num` varchar(32) DEFAULT NULL COMMENT '订单号',
  `order_title` varchar(255) DEFAULT NULL COMMENT '订单标题',
  `order_time` varchar(32) DEFAULT NULL COMMENT '创建时间',
  `order_amount` varchar(10) DEFAULT NULL COMMENT '实际支付价格',
  `order_price` varchar(10) DEFAULT NULL COMMENT '订单价格',
  `order_status` int(1) NOT NULL DEFAULT '1' COMMENT '支付状态',
  `order_paytime` varchar(32) DEFAULT NULL COMMENT '支付时间',
  `order_type` varchar(10) DEFAULT NULL COMMENT '支付类型',
  `order_extra` varchar(255) DEFAULT NULL COMMENT '自定义参数',
  `order_content` text COMMENT '挂机监听回调原文'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信赞赏码支付订单信息表';
```
以phpMyAdmin为例：<br/>

![](https://img10.360buyimg.com/imgzone/jfs/t1/231405/6/22443/58327/6683a1adF6b791baf/3b0b12c470a032cb.jpg)

配置清单
---
请检查你已经完成以下配置：
```
1、将所有代码上传到服务器；
2、notify.php配置；
3、安装【监听器APP】并配置好APP的发送通道和转发规则；
4、修改Db.php里面的数据库配置信息；
5、数据库创建；
6、进入img目录替换qrcode.png赞赏码
```

在线演示
---
https://demo.likeyunba.com/wxpayZsm/

作者
---
TANKING

加入开发者交流群
---
请联系微信：sansure2016 即可加入我的开发者交流群，目前已有9个群，每个群人数均480+，属于高质量活跃群！<br>

![](https://img10.360buyimg.com/imgzone/jfs/t1/246098/25/13070/28619/6683b0abF2ecc1b35/73c9df9448800689.jpg)

赞赏
---
如果你喜欢我的作品，请给予一些支持！

![image.png](https://t.focus-img.cn/sh740wsh/bbs/p2/25b4c4dc3a50be9b6f2c9a4ffe68deba.png)
