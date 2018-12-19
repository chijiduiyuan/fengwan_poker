<?php
/**
 * 玩家授权入口
 *
 * @author   HJH
 * @version  2017-6-6
 */


//是否需要检查停机维护状态
$stopServerFlag = true;

//是否开启session
$startSessionFlag = true;

require_once('common.inc.php');

//参数
$_P = $_GET+$_POST;

//过滤参数
if(!get_magic_quotes_gpc()) {
	$_P = Fun::addslashe($_P);
}

//控制器和操作
$_c = $_P['_c'];
$_a = $_P['_a'];


//定义UID
define('UID', (int)$_SESSION['uid']);

//判断登录
if( !UID ) {
	CMD(201);
}

//更新在线时间
if( ($_SERVER['REQUEST_TIME']-$_SESSION['online'])>300 ) {
	User::upOnline();
}

//分模块处理
$modAry = array(
	'user',   			//玩家
	'shop',   			//商店
	'club',   			//俱乐部
	'clubLevel',  		//俱乐部等级商店
	'clubApply',   		//俱乐部申请加入
	'clubMember',   	//俱乐部会员
	'clubCounter',   	//俱乐部柜台
	'clubRoom',   		//房间(桌子)
	'clubData',   		//统计
	'mail',   			//邮件
	'ping',   			//心跳
	'sts',   			//阿里云OSS授权
	'checkInviteUid',	//检查邀请人uid
	'tuiguang',			//推广流水
);

if( in_array($_c, $modAry) ) {
	include('_code/'.$_c.'.code.php');
	
}else{
	CMD(202);
}

CMD();