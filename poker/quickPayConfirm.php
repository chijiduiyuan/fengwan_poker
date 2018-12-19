<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2017/10/27
 * Time: 10:35
 */
require_once("lib/pay.Config.php");
require_once("lib/Pay.class.php");

// 请求数据赋值


// 商户APINMAE，WEB渠道一般支付
$data['service'] = $APINAME_QUICKPAY_CONFIRM;
// 商户API版本
$data['version'] = $API_VERSION;
// 商户在支付平台的的平台号
$data['merId'] = $MERCHANT_ID;
//接口返回的订单号
$data['opeNo'] = $_POST["opeNo"];
// 接口返回的订单日期
$data['opeDate'] = $_POST["opeDate"];
// 接口返回的交易标识
$data['sessionID'] = $_POST["sessionID"];
//手机的动态验证码
$data['dymPwd'] = $_POST["dymPwd"];

// 初始化
$pPay = new Pay($KEY,$GATEWAY_URL);
// 准备待签名数据

$str_to_sign = $pPay->prepareSign($data);
// 数据签名
$signMsg = $pPay->sign($str_to_sign);

$data['sign'] = $signMsg;

// 准备请求数据
$to_requset = $pPay->prepareRequest($str_to_sign, $signMsg);
//请求数据
$resultData = $pPay->request($to_requset);

echo $resultData;




?>