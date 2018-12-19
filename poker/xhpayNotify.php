<?php
/* *
 * 功能：支付回调文件
 * 版本：1.0
 * 日期：2015-03-26
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码。
 */
 
 $stopServerFlag = false;

//是否开启session
$startSessionFlag = false;
require_once('common.inc.php');
header('Content-type: text/html; charset=utf-8');
require_once("_lib/xhpay/lib/pay.Config.php");
require_once("_lib/xhpay/lib/Pay.class.php");


	// 请求数据赋值
	$data = "";
	$data['service'] = $_REQUEST["service"];
	// 通知时间
	$data['merId'] = $_REQUEST["merId"];
	// 支付金额(单位元，显示用)
	$data['tradeNo'] = $_REQUEST["tradeNo"];
	// 商户号
	$data['tradeDate'] = $_REQUEST["tradeDate"];
	// 商户参数，支付平台返回商户上传的参数，可以为空
	$data['opeNo'] = $_REQUEST["opeNo"];
	// 订单号
	$data['opeDate'] = $_REQUEST["opeDate"];
	// 订单日期
	$data['amount'] = $_REQUEST["amount"];
	// 支付订单号
	$data['status'] = $_REQUEST["status"];
	// 支付账务日期
	$data['extra'] = $_REQUEST["extra"];
	// 订单状态，0-未支付，1-支付成功，2-失败，4-部分退款，5-退款，9-退款处理中
	$data['payTime'] = $_REQUEST["payTime"];
	// 签名数据
	$data['sign'] = $_REQUEST["sign"];
    $data['notifyType'] = $_REQUEST["notifyType"];
	// 初始化
    $pPay = new Pay($KEY,$GATEWAY_URL);
	// 准备准备验签数据
	$str_to_sign = $pPay->prepareSign($data);
	// 验证签名
	$resultVerify = $pPay->verify($str_to_sign, $data['sign']);
	//var_dump($data);
	if ($resultVerify) 
	{   
          // echo "验证签名成功";

          /**
           * 验证通过后，请在这里加上商户自己的业务逻辑处理代码.
           * 比如：
           * 1、根据商户订单号取出订单数据
           * 2、根据订单状态判断该订单是否已处理（因为通知会收到多次），避免重复处理
           * 3、比对一下订单数据和通知数据是否一致，例如金额等
           * 4、接下来修改订单状态为已支付或待发货
           * 5、...
           */ 
        // 判断通知类型，若为后台通知需要回写"SUCCESS"给支付系统表明已收到支付通知
        // 否则支付系统将按一定的时间策略在接下来的24小时内多次发送支付通知。
        
        if((int)$data['status'] == 1) {
            $result = Shop::xhPayNotify($data['tradeNo'],$data['amount'],$data['opeNo']);
            if ($result && '1' == $data["notifyType"]) {
                echo $result;
                return true;
            }
        }		

	}     
	else
	{
		header('Location: '.$COMMIT_URL);
        return false;
	}


?>