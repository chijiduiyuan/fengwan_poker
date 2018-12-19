<?php
/**
 * 商店操作类
 *
 * @author HJH
 * @version  2017-6-6
 */


include_once($global['path']['lib'] . '/paypal/paypal.php');
include_once($global['path']['lib'] . '/AppleIAP/AppleInAppPurchaseVerification.php');
include_once($global['path']['lib'] . 'pay/src/Pay.php');

use AppleIAP\AppleInAppPurchaseVerification;
use Yansongda\Pay\Pay;

class  Shop{
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

// 测试平台账号
	public static $ali_config = [
		'alipay'=>[
			'app_id' => '2018032302435301',
			'notify_url' => 'http://172.247.193.210/poker/aliPayNotify.php',
			'ali_public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCx6XtF07Cedhfkt72fpM0zS1SQtHPKJruyFVP8nle5IN3gjcyfrNhX2Z/CQK0dw/LX9ie3bQVvwraqcy5dAB9wqxqTxiMrBcv+uoH5fvrLXZmm8Cqrj4mCj5uKuPXfzD5FZlaOKDCBK6YcDGxXWaVnizS0mEzZtLNYgLxsnKN52wIDAQAB',
			'private_key' => 'MIICXQIBAAKBgQDbeg1LKuUlLXiczkmV8yXL3RfBehy6epOMvlPAdM0UBhKZQu31I/h3vNvBXIPGAdd9/w4AaVxmE/nhYcFwXul+7WSUkMyyR942d4sMTjQeadPZQNzTdBSdtajg2xu+jb/mHYuDKic0W54q/OiH1YY+pONrp65+9b9qXsCky2PFswIDAQABAoGARgdgnH5YVQ94L5hGtHi7s5udC9fZuMqJr9u+v2bFVMFHR+2qHZDXo+T3vW+2gARwbLxoDEuR9uQi0/4sCZ3PJGAPQd9p+5/X1p9j7tPwpm4mfOxsLsqFw05uunYU/3AuEaamAZ+u7eisVk/ySZx3SE8JXJfaOpFhCddjYwyD79ECQQD/MnqC+IDeR6w4D0CsMXjg4o0JhSu/Qnds/td4pkLBgEbrosOYOJItT+hDPv933BgSEhULYO1hnTFq2oGCXIy5AkEA3CrOYDJ+1dRPLQDIEmbPH+qWh0V/P7pp6IMk29k6XDmG+IVsNzJ/d2beMyLGMVxgskKY0IsX98RPAhwMPxAnywJBAK7ShdrzIlUkBHbi/Ar6WTS1/qhm8nEzt1yTuEiOnWyx0+PYvOWq23jvJM3selZCELtQZ9pDrFsStKfeyJnljQECQQCqVjP1GRwoM2pOVxxzoDb/am+rmIkqtP7bdRs/PIF6eMeD3zYqPld/+YZP6ceMyPvG7t9r+TFB8A9wgmK3J7ihAkA4RzXI9sH2n4DHU4WYKajB5a6AHXUy3fqoguzkx1rLypGmxcDO2M46ck8p9CHApOrm741chwJoPh+jQ//y6wEJ'
		]
	];

	public static $wechat_config = [
		'wechat'=>[
			'app_id' => 'wx27b28501b1b617db',  // AppID
			'mch_id' => '1503745631',          // 商户编号
			'notify_url' => 'http://172.247.193.210/poker/weChatPayNotify.php',  // 回调地址
			'key' => 'nifaaafewfeejl41654301fsakfjsalK',  // API秘钥
			'cert_client' => '',  // 客户端证书路径，退款时需要用到
			'cert_key' => ''  // 客户端证书路径，退款时需要用到
		]
	];


	/**
	 * 取商品信息
	 * @param string $param 字段
	 * @param int $id 商品id
	 * @return array 商品信息
	 * @access public
	 */
	public static function getValue($param, $id) {				
		$DB = useDB();
		return $DB->getValue('SELECT '.$param.' FROM shop WHERE id=' . (int)$id . ' AND status=1');
	}


	/**
	 * 取商品列表
	 * @param string $param 字段
	 * @param string $where 条件
	 * @return array 商品列表
	 * @access public
	 */
	public static function getList($param, $where) {
		$DB = useDB();
		return $DB->getList('SELECT '.$param.' FROM shop WHERE status=1 AND ('.$where.') ORDER BY orders ASC,id DESC');
	}


	/**
	 * 创建购买钻石订单
	 * @param int $id 商品id
	 * @param int $pay_type 支付方式 1=paypal 2=apple 3=管理员充值
	 * @return string 订单号
	 * @access public
	 */
	public static function createOrder($id, $pay_type) {
		$DB = useDB();

		$info = Shop::getValue('num,extra,price', $id);
		if(!$info) {
			return false;
		}

		$orderNo = UID.time().mt_rand();

		$ary = array(
			'order_no'		=> $orderNo,
			'uid'			=> UID,
			'num'			=> $info['num'],
			'extra'			=> $info['extra'],
			'price'			=> $info['price'],
			'price_type'	=> 1,
			'pay_type'		=> $pay_type,
			'order_time'	=> time(),
			'order_date'	=> date('Y-m-d H:i:s')
		);

		$rs = $DB->insert('pay_record', $ary);
		if($rs) {
			return $orderNo;
		}else{
			return false;
		}
	}

	/**
	 * 支付宝或者微信支付
	 * @param int $id 商品id
	 * @param int $payType 支付方式 1=支付宝 2=微信支付
	 * @return string 支付信息
	 * @access public
	 */
	public static function aliOrWeChatPay($id, $clientHost, $payType) {
		$DB = useDB();

		$info = Shop::getValue('num,extra,price', $id);			//数量 赠送得数量 价格(分)
		if(!$info) {
			return false;
		}
		$orderNo = UID.time().mt_rand();			//订单号

		$config_biz = array();
		$driver = '';
		$pay_type=null;
		$result = null;
		// 支付宝支付
		if ($payType == 1) {
			$pay_type=4; // 支付宝支付
			$driver='alipay';

			$config_biz['out_trade_no'] = $orderNo;
			$config_biz['total_amount'] = $info['price']/100.0; // 单位：元
			$config_biz['subject'] = '支付宝支付';

			$result = Shop::aliAppPay(Shop::$ali_config, $config_biz);
		} else if ($payType == 2){ // 微信支付
			$pay_type=5; // 微信支付 
			$driver='wechat';

			$config_biz['out_trade_no'] = $orderNo;
			$config_biz['total_fee'] = $info['price'];  // 单位：分
			$config_biz['body'] = '微信支付';
			$config_biz['spbill_create_ip'] = $clientHost;

			$result = Shop::weChatAppPay(Shop::$wechat_config, $config_biz);
		} else {
			return false;
		}

		if ($result) {
			$ary = array(
				'order_no'		=> $orderNo,
				'uid'			=> UID,
				'num'			=> $info['num'],
				'extra'			=> $info['extra'],
				'price'			=> $info['price'],
				'price_type'	=> 1,
				'pay_type'		=> $pay_type,
				'order_time'	=> time(),
				'order_date'	=> date('Y-m-d H:i:s')
			);

			$DB = useDB();
			$rs = $DB->insert('pay_record', $ary);
			if($rs) {
				return $result;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	private static function weChatAppPay($config, $config_biz) {
		$pay = new Pay($config);
		$payResult = $pay->driver('wechat')->gateway('app')->pay($config_biz);
		$sign = $payResult['sign'];
		unset($payResult['sign']);
		$payResult['paySign']=$sign; // 改变sign变量名称
		$payResult['pkg']='Sign=WXPay';
		return $payResult;
	}

	private static function aliAppPay($config, $config_biz) {
		$pay = new Pay($config);
		return $pay->driver('alipay')->gateway('app')->pay($config_biz);
	}

	public static function getOrderByOrderNo($orderNo){
		$DB = useDB();
		$info = $DB->getValue('SELECT * FROM pay_record WHERE order_no=\''.$orderNo.'\'');
		return $info;
	}

	public static function xhPayNotify($orderNo,$totalFee,$outNo) {
		// 查询订单
		$DB = useDB();
		$info = $DB->getValue('SELECT num,extra,price,status,uid FROM pay_record WHERE order_no=\''.$orderNo.'\'');
		if( (int)$info['status'] ) {
			return 'SUCCESS';
		}

		if (!(($totalFee * 100) == $info['price'])) {
				return false;
		}

		//支付成功
		$ary = array(
			'status'   => 1,
			'pay_time' => time(),
			'pay_date' => date('Y-m-d H:i:s')
		);

		if($outNo){
			$ary['pay_id'] = $outNo;
		}

		$DB->beginTransaction();  //开启事务

		//修改状态
		$rs1 = $DB->update('pay_record', $ary, 'order_no=\''.$orderNo.'\'');

		//新增rmb
		$rmb = User::rmb(((int)$info['num']+(int)$info['extra']), true, $info['uid']);
		
		if($rs1 && $rmb) {
			$DB->commit();    //提交
			// 发送通知
			$modifyInfo = [];
			$modifyInfo['rmb'] = $rmb;
			Fun::addNotify( [$info['uid']], $modifyInfo,'user_info' );
			return 'SUCCESS';
		}else{
			$DB->rollBack();  //回滚
			return false;
		}
	}

	public static function weChatNotify($content) {
		$pay = new Pay(Shop::$wechat_config);
		$verify = $pay->driver('wechat')->gateway('app')->verify($content);
		if (!$verify) {
			return false;
		}
		$content = json_decode(json_encode(simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
		// 判断业务是否正常
		if ($content['return_code'] == 'SUCCESS' && $content['result_code'] != null && $content['result_code'] == 'SUCCESS') {
			$orderNo = $content['out_trade_no'];
			$totalFee = $content['total_fee'];
			// 查询订单
			$DB = useDB();
			$info = $DB->getValue('SELECT num,extra,price,status,uid FROM pay_record WHERE order_no=\''.$orderNo.'\'');
			if( (int)$info['status'] ) {
				return 'success';
			}

			if (!($totalFee == $info['price'])) {
				return false;
			}

			//支付成功
			$ary = array(
				'status'   => 1,
				'pay_time' => time(),
				'pay_date' => date('Y-m-d H:i:s')
			);

			$DB->beginTransaction();  //开启事务

			//修改状态
			$rs1 = $DB->update('pay_record', $ary, 'order_no=\''.$orderNo.'\'');

			//新增rmb
			$rmb = User::rmb(((int)$info['num']+(int)$info['extra']), true, $info['uid']);
			
			if($rs1 && $rmb) {
				$DB->commit();    //提交
				// 发送通知
				$modifyInfo = [];
				$modifyInfo['rmb'] = $rmb;
				Fun::addNotify( [$info['uid']], $modifyInfo,'user_info' );
				return 'success';
			}else{
				$DB->rollBack();  //回滚
				return false;
			}
		}
		return false;
	}

	public static function aliPayNotify($content) {
		$pay = new Pay(Shop::$ali_config);
		$verify = $pay->driver('alipay')->gateway('app')->verify($content);
		if (!$verify) {
			return false;
		}
		$tradeState = $content['trade_status'];
		// 判断业务是否正常
		if ($tradeState == 'TRADE_SUCCESS' || $tradeState == 'TRADE_FINISHED') {
			$orderNo = $content['out_trade_no'];
			$totalFee = $content['total_amount'];
			// 查询订单
			$DB = useDB();
			$info = $DB->getValue('SELECT num,extra,price,status,uid FROM pay_record WHERE order_no=\''.$orderNo.'\'');
			if( (int)$info['status'] ) {
				return 'success';
			}

			if (!(($totalFee * 100) == $info['price'])) {
				return false;
			}

			//支付成功
			$ary = array(
				'status'   => 1,
				'pay_time' => time(),
				'pay_date' => date('Y-m-d H:i:s')
			);

			$DB->beginTransaction();  //开启事务

			//修改状态
			$rs1 = $DB->update('pay_record', $ary, 'order_no=\''.$orderNo.'\'');

			//新增rmb
			$rmb = User::rmb(((int)$info['num']+(int)$info['extra']), true, $info['uid']);
			
			if($rs1 && $rmb) {
				$DB->commit();    //提交

				// 发送通知
				$modifyInfo = [];
				$modifyInfo['rmb'] = $rmb;
				Fun::addNotify( [$info['uid']], $modifyInfo,'user_info' );

				return 'success';
			}else{
				$DB->rollBack();  //回滚
				return false;
			}
		} 
		return false;
	}

	/**
	 * 验证购买钻石订单(paypal)
	 * @param string $orderNo 订单号
	 * @param string $payId 支付id
	 * @return array 商品信息
	 * @access public
	 */
	public static function verifyOrder($orderNo, $payId) {
		
		//判断paypal是否支付成功
		$payment = getPayment($payId);
		$payment = json_decode($payment, true);

		//判断支付状态
		if($payment['state']=='approved') {

			//判断是否已支付成功
			$DB = useDB();
			$info = $DB->getValue('SELECT num,extra,price,status FROM pay_record WHERE order_no=\''.$orderNo.'\'');
			if( (int)$info['status'] ) {
				return false;
			}

			//判断支付金额
			$price = $payment['transactions'][0]['amount']['total']*100;
			if($price!=$info['price']) {
				return false;
			}

			//支付成功
			$ary = array(
				'status'   => 1,
				'pay_id'   => $payId,
				'pay_time' => time(),
				'pay_date' => date('Y-m-d H:i:s')
			);

			$DB->beginTransaction();  //开启事务

			//修改状态
			$rs1 = $DB->update('pay_record', $ary, 'order_no=\''.$orderNo.'\'');

			//新增rmb
			$rmb = User::rmb(((int)$info['num']+(int)$info['extra']));
			
			if($rs1 && $rmb) {
				$DB->commit();    //提交
				return $rmb;
			}else{
				$DB->rollBack();  //回滚
				return false;
			}
		}else{
			return false;
		}
	}


	/**
	 * 验证购买钻石订单(apple)
	 * @param string $orderNo 订单号
	 * @param string $receipt 支付receipt
	 * @return array 商品信息
	 * @access public
	 */
	public static function verifyOrderApple($orderNo, $receipt) {
		
		//判断apple是否支付成功
		$appleIAP = new AppleInAppPurchaseVerification($receipt, '', false);
		$result = $appleIAP->validateReceipt();
		$data = json_decode($result, true);
		if (!$data || !is_array($data)) {
	        return false;
	    }

	    //判断是否为沙盒测试
	    if( $data['status'] == 21007 ) {
	        $appleIAP = new AppleInAppPurchaseVerification($receipt, '', true);
			$result = $appleIAP->validateReceipt();
			$data = json_decode($result, true);
			if (!$data || !is_array($data)) {
		        return false;
		    }
	    }

	    //判断购买时候成功
	    if(!isset($data['status']) || $data['status'] != 0) {
	        return false;
	    }

	    //支付成功
	    $DB = useDB();

		//判断是否已支付成功
		$info = $DB->getValue('SELECT num,extra,status FROM pay_record WHERE order_no=\''.$orderNo.'\'');
		if( (int)$info['status'] ) {
			return false;
		}

		//支付成功
		$ary = array(
			'status'   => 1,
			'pay_id'   => $payId,
			'pay_time' => time(),
			'pay_date' => date('Y-m-d H:i:s')
		);

		$DB->beginTransaction();  //开启事务

		//修改状态
		$rs1 = $DB->update('pay_record', $ary, 'order_no=\''.$orderNo.'\'');

		//新增rmb
		$rmb = User::rmb(((int)$info['num']+(int)$info['extra']));
		
		if($rs1 && $rmb) {
			$DB->commit();    //提交
			return $rmb;
		}else{
			$DB->rollBack();  //回滚
			return false;
		}
		
	}

}