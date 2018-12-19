<?php
/**
 * 玩家公共入口
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


//分模块处理
$modAry = array(
	'version',			//版本
	'notice',   		//公告
	'verify',   		//发送验证码
	'login',   			//玩家登入
	'logout',			//玩家登出
	'bind',				//绑定手机
	'feedback'			//意见反馈
);

if( in_array($_c, $modAry) ) {
	include('_code/'.$_c.'.code.php');
	
}else{
	CMD(202);
}

CMD();