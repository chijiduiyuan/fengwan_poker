<?php
/**
 * 代理入口
 *
 * @author   HJH
 * @version  2017-8-4
 */


//是否需要检查停机维护状态
$stopServerFlag = true;

//是否开启session
$startSessionFlag = true;

require_once('common.inc.php');

//参数
if($_SERVER['REQUEST_METHOD']=='POST') {
	$_P = file_get_contents("php://input");
	$_P = json_decode($_P, true);
}else{
	$_P = $_GET;
}

//过滤参数
if(!get_magic_quotes_gpc()) {
	$_P = Fun::addslashe($_P);
}

$_c = $_P['_c'];
$_a = $_P['_a'];


//无需授权模块
$modAry = array(
	'verify',	//发送验证码
	'login',	//玩家登入
	'logout'	//玩家登出
);

if( !in_array($_c, $modAry) ) {

	//GM跳转标识
	$gmSourceFlag = false;
	if($_P['gmToken']) {

		$gmTokenAry = explode('_', $_P['gmToken']);

		$cache = getCache();
		if($_P['gmToken']==$cache->get('gm_agent_flag_'.(int)$gmTokenAry[2])) {

			$gmSourceFlag = true;

			$_P['clubId'] = (int)$gmTokenAry[1];

			//分模块处理
			$modAry = array(
				'clubData',  //统计
				'clubMember'    //俱乐部成员
			);
		}

	}

	if(!$gmSourceFlag) {
		
		//定义UID
		define('UID', (int)$_SESSION['uid']);

		//判断登录
		if( !UID ) {
			CMD(201);
		}

		//分模块处理
		$modAry = array(
			'user',         //用户
			'shop',   		//商店
			'club',   		//俱乐部
			'clubData', 	//统计
			'clubMember'    //俱乐部成员
		);
	}
}

if( in_array($_c, $modAry) ) {
	include('_code/'.$_c.'.code.php');
	
}else{
	CMD(202);
}

CMD();