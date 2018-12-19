<?php
/**
 * 验证码操作模块
 *
 * @author HJH
 * @version  2017-6-6
 */

if(!$_P){exit;}


switch ($_a) {


	//获取国家
	case 'country':

		//读取国家配置
		$list = Country::getList('*','status=1 ORDER BY orders DESC');
		foreach($list as &$item) {
			unset( $item['country'] );
			unset( $item['blindBet'] );
			unset( $item['rmbToclubRb'] );
			unset( $item['clubRbLeast'] );
			unset( $item['status'] );
			unset( $item['orders'] );
		}
		
		CMD(200, $list);

		break;



	//发送验证码
	case 'send':

		//获取国家id
		$cid = 1;
		if($cid<=0) {
			CMD(202);
		}

		//获取手机号
		$phone = trim($_P['phone']);
		$uid      = (int)$_P['uid'];
		if(!$phone || !$uid) {
			CMD(202);
		}

		//代理端标识
		$agent = (int)$_P['agent'];

		if(!$agent){
			$cfg = getCFG('data');
			$info = User::get('verify_date,verify_num','uid=\''.$uid.'\'');

			//发送验证码
			$rs = Verify::send($cid,$phone, $cfg['smsTplNote']);
			if($rs){
				//发送成功累计短信次数
				if( $info['verify_date']!=date('Y-m-d') ) {
					User::edit( array('verify_date'=>date('Y-m-d'), 'verify_num'=>1), $uid);
				}elseif( $info['verify_num']<$cfg['smsNumMax'] ) {
					User::edit( array('verify_num'=>(int)$info['verify_num']+1), $uid );
				}
			}

		}else{
			Verify::send($cid,$phone,$cfg['smsTplNote']);
		}
		CMD(200);
		break;


	//版本更新
	case 'version':

		//渠道号
		$channel = (int)$_P['channel'];
		if(!$channel) {
			CMD(202);
		}

		//当前版本号
		$versionCode = $_P['versionCode'];
		if($versionCode=='') {
			CMD(202);
		}

		CMD(200);

		break;

}

