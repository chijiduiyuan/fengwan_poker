<?php

$stopServerFlag = false;

//是否开启session
$startSessionFlag = false;
require_once('common.inc.php');
header('Content-type: text/html; charset=utf-8');
require_once("_lib/xhpay/lib/pay.Config.php");
require_once("_lib/xhpay/lib/Pay.class.php");



$orderNo = $_GET['orderNo'];

if(!$orderNo) {
    header("Status: 404 Not Found");
    exit();
}

$info = Shop::getOrderByOrderNo($orderNo);


if(!$info){
    header("Status: 404 Not Found");
    exit();
}
if(!$info['status'] == 0){
    header("Status: 404 Not Found");
    exit();
}

$type = $info['pay_type'];
if ($type != 7) {
 	header("Status: 404 Not Found");
    exit();
}


$tradeDate  = date("Ymd",$info['order_time']);
$amount = $info['price']/100.0; // 单位：元

   // 商户APINMAE，WEB渠道一般支付
   $data['service'] = $APINAME_PAY;
   // 商户API版本
   $data['version'] = $API_VERSION;
   // 商户在支付平台的的平台号
   $data['merId'] = $MERCHANT_ID;
   //商户订单号
   $data['tradeNo'] = $info["order_no"];

   // 商户订单日期
   $data['tradeDate'] = $tradeDate;
   // 商户交易金额
   $data['amount'] =$amount;
   // 商户通知地址
   $data['notifyUrl'] = $MERCHANT_NOTIFY_URL;
   // 商户扩展字段
   $data['extra'] = '';
   // 商户交易摘要
   $data['summary'] = '充值';
   //超时时间
   $data['expireTime'] = '';
    //客户端ip
   $data['clientIp'] = User::getIP();
   // 接收银行代码
   $data['bankId'] = "";

   //var_dump($data);
   //return;

   // 对含有中文的参数进行UTF-8编码
	// 将中文转换为UTF-8
  if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['notifyUrl']))
  {
    $data['notifyUrl'] = iconv("GBK","UTF-8", $data['notifyUrl']);
  }


   if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['extra']))
   {
	$data['extra'] = iconv("GBK","UTF-8", $data['extra']);
   }

   if(!preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $data['summary']))
	{
  	$data['summary'] = iconv("GBK","UTF-8", $data['summary']);
	}

  // 初始化
	$pPay = new Pay($KEY,$GATEWAY_URL);
	// 准备待签名数据
	$str_to_sign = $pPay->prepareSign($data);
	// 数据签名

	$signMsg = $pPay->sign($str_to_sign);
    //var_dump($signMsg);

    //return;

    //$signMsg='4F0D7B1A8DF615DABE147B1CC112B79C';
	$data['sign'] = $signMsg;
	// 生成表单数据
	echo $pPay->buildForm($data,$GATEWAY_URL);
	

?> 