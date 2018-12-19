<?php
/**
 * node操作入口
 *
 * @author   HJH
 * @version  2017-6-24
 */


require_once('common.inc.php');

//判断来路IP
if(Fun::getIP()!=NODE_PHP_IP) {
	// exit();
}

//参数
$_P = $_GET+$_POST;

//控制器和操作
$mod = $_P['mod'];
$op  = $_P['op'];


//定义UID
define('UID', (int)$_P['uid']);


//分模块处理
$modAry = array(
	'common',
	'room'
);


if( in_array($mod, $modAry) ) {
	
	if($mod=='common') {
		include('_code/_common.code.php');

	}else{

		//获取roomId
		$roomId = (int)$_P['roomId'];

		//判断参数
		if(!$roomId && !UID) {
			CMD(202, '缺少roomId或uid', 'msg');

		}elseif(!$roomId) {
			$roomId = ClubRoom::getRoomId();
			if(!$roomId) {
				CMD(210, '玩家不在房间中，玩家ID：'.UID, 'msg');
			}
		}

		//房间类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202, '缺少game参数', 'msg');
		}
		
		//判断俱乐部房间类型 私人/公共
		if($roomId<0) {
			include('_code/_public_club.code.php');
		}else{
			include('_code/_node.code.php');
		}
	}
	
}else{
	CMD(202);
}

CMD();