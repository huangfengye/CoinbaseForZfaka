# CoinbaseForZfaka
 ## 支持收取BTC,BCH,DAI,dogocoin,ETH,LTC,USDC

 ## 安装
 + 按照https://github.com/zlkbdotnet/zfaka  要求安装zfaka，建议使用页面中提示的宝塔教程进行安装，省时省力

 + 注册Coinbase商家版 https://commerce.coinbase.com/signup
 + 进入Coinbase商家版面板设置页面 https://commerce.coinbase.com/dashboard
 + 找到 Creat an Api Key，点击一下，创建一个Api Key.  
 + 找到Webhook Subscriptions ,点击Add a endpoint，填入自己的异步回调地址：  https://你的卡网域名/product/notify/?paymethod=coinpay
 + 点击回调地址 右边的Details，在弹出的窗口中 Events--->Edit, 在所有Events中仅仅勾选 charge:confirmed，然后点击保存
 

 + 将文件夹 coinpay  解压到网站目录application/library/Pay文件夹中，此时在Pay文件夹中会多出一个文件夹，名字分别为：coinpay

 + 将文件  coinpay.html 解压到网站目录application\modules\Admin\views\payment\tpl文件夹中   (注意，如果你更改过后台路径，请把这段目录地址中的Admin替换成你的自己的地址)  
 
 + 将coinbase.png 拷贝到网站目录/public/res/images/pay中

 + 修改数据库，在faka数据库中运行下面的sql语句，建议使用宝塔环境的phpmyadmin软件进行修改，省时省力
```
INSERT INTO `t_payment` (`payment`, `payname`, `payimage`, `alias`, `sign_type`, `app_id`, `app_secret`, `ali_public_key`, `rsa_private_key`, `configure3`, `configure4`, `overtime`, `active`) VALUES
('CoinPay', 'CoinPay', '/res/images/pay/coinbase.png', 'coinoay', 'MD5', '', '', '', '', '', '', 6000, 0);
```
 + 登录zfaka的后台，依次点击：设置中心->配置中心，在第三页修改参数“weburl”的值为你自己的网站域名url（必须修改，否则无法回调）

 + 依次点击：设置中心->支付设置，修改编辑“CoinPay”支付渠道，将你之前创建的Api Key通信密钥填入进去，设置自己的需要的费率，选中激活状态，点击确认修改

 + 按照zfaka的后台逻辑，自行添加商品，自行测试

## 付费服务
如果你不会设置，可以联系我QQ583648414，提供设置服务😁  80元一次.
## 赞助
如果您有经济条件，您可以赞助本项目的开发（下方收款码），如果您不想赞助，也请您点击上面的Star给一个星星，也是对我莫大的认同，感谢各位的支持。

![微信赞助](https://puu.sh/DF0jt/ded5938c8c.jpg)![支付宝赞助](https://puu.sh/DEYmS/32f8237fd8.jpg)

## 感谢
- https://github.com/huangfengye  集成此接口
- 感谢https://github.com/zlkbdotnet/zfaka  提供的发卡方案
