<?php
/**
 * 商店操作模块
 *
 * @author HJH
 * @version  2017-6-6
 */

include_once($global['path']['lib'] . '/pay/vendor/autoload.php');

require_once($global['path']['lib'] . "/xhpay/lib/pay.Config.php");

if(!$_P){exit;}

switch ($_a) {


	//商店列表-代理端
	case 'agentList':
		
		CMD(200, Shop::getList('id,title,avatar,num,extra,price,intro','flag=2'));

		break;
	

	//商店列表
	case 'list':
		
		CMD(200, Shop::getList('id,title,stype,avatar,num,extra,price,param,intro', 'flag=1 OR flag=3'));

		break;


	//购买
	case 'buy':
		
		//商品id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD(202);
		}

		$info = Shop::getValue('id,title,stype,num,extra,price,param', $id);
		if(!$info) {
			CMD(210);
		}

		$ary = array();

		switch ($info['stype']) {
			

			//购买钻石
			case 'rmb':
				
				$total = ((int)$info['num']+(int)$info['extra']);
				$rmb   = User::rmb($total);
				$gold  = User::get('gold');
				
				break;


			//购买金币
			case 'gold':
				
				$rmb = User::rmb($info['price'], false);
				if($rmb!==false) {
					$total = ((int)$info['num']+(int)$info['extra']);
					$gold = User::gold($total);
					
				}else{
					CMD(206);
				}
				
				break;
			

			//购买VIP卡					
			case 'card':
				$rmb = User::rmb($info['price'], false);
				if($rmb!==false) {
					$userInfo = User::get('vip_card,vip_endtime,card_club_num,card_emoji_num,card_delay_num');
					$param = json_decode($info['param'], true);

					// //判断玩家当前VIP卡等级
					if($userInfo['vip_card']==$param['vip'] && $userInfo['vip_endtime']>time()) {
						//相同则全部叠加
						User::edit( array('vip_endtime'=>((int)$userInfo['vip_endtime']+((int)$param['time']*86400)),'card_emoji_num'=>($userInfo['card_emoji_num']+$param['emoji']),'card_delay_num'=>($userInfo['card_delay_num']+$param['delay'])) );
					
						
					 //不同或过期则覆盖
					  }
					else{
						 User::edit( array('vip_card'=>$param['vip'],'vip_endtime'=>(time()+((int)$param['time']*86400)),'card_club_num'=>$param['club'],'is_look_undercard'=>$param['undercard'],'card_emoji_num'=>($userInfo['card_emoji_num']+$param['emoji']),'card_delay_num'=>($userInfo['card_delay_num']+$param['delay'])) );
					 }

					 $gold  = User::get('gold');

				}else{
					CMD(206);
				}

				break;
		}

		$ary['gold'] = $gold;
		$ary['rmb']  = $rmb;

		CMD(200, $ary);

		break;

/*
	//购买钻石(paypal)
	case 'commit':

		//商品id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD(202);
		}

		$orderNo = Shop::createOrder($id, 1);
		if(!$orderNo) {
			CMD(210);
		}

		CMD(200, array('orderNo'=>$orderNo));

		break;
*/
	//购买钻石(支付宝|微信APP支付)
	case 'commit':

		//商品id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD(202);
		}
		// 1: alipay; 2: wechat-pay
		$payType = (int)$_P['payType'];  
		if($payType != 1 && $payType != 2) {
			CMD(202);
		}

		$result = Shop::aliOrWeChatPay($id, '8.8.8.8', $payType);
		if(!$result) {
			CMD(210);
		}
		// throw new Exception("result: ".json_encode($result));
		CMD(200, array('sign'=>$result));

		break;

	//支付验证(paypal)
	case 'verify':

		$orderNo = $_P['orderNo'];
		$payId   = $_P['payId'];

		$rmb = (int)Shop::verifyOrder($orderNo, $payId);
		if(!$rmb) {
			CMD(235);
		}

		CMD(200, array('rmb'=>$rmb));

		break;

	case 'commit_xh':		
		//商品id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD(202);
		}

		// 5=星和支付 微信  6=星和支付qq 7=星和网页支付
		$payType = (int)$_P['payType'];  
		if($payType <5 || $payType > 7) {
			CMD(202);
		}
		
		$orderNo = Shop::createOrder($id, $payType);
		if(!$orderNo) {
			CMD(210);
		}

		$payUrl = $COMMIT_URL;
        if ($payType == 7) {
        	$payUrl = $payUrl.'poker/xhpay.php';
        }else {
        	$payUrl = $payUrl.'poker/h5pay.php';
        }		

		CMD(200, array('orderNo'=>$orderNo,
                        'url' => $payUrl
	                    ));

		break;
	//购买钻石(apple)
	case 'commit_a':

		//商品id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD(202);
		}

		$orderNo = Shop::createOrder($id, 4);
		if(!$orderNo) {
			CMD(210);
		}

		CMD(200, array('orderNo'=>$orderNo));

		break;


	//支付验证(apple)
	case 'verify_a':

		$orderNo = $_P['orderNo'];
		$receipt = $_P['receipt'];

		$rmb = (int)Shop::verifyOrderApple($orderNo, $receipt);
		if(!$rmb) {
			CMD(235);
		}

		CMD(200, array('rmb'=>$rmb));

		break;
}