<?php
/**
 * 授权入口
 *
 * @author   HJH
 * @version  2017-7-28
 */


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
$_P = Fun::addslashe($_P);


//路由解析
$route = $_P['route'];
if(!$route) {
	CMD('ILLEGAL_PARAM');
}
$routeAry = explode('-', $route);
$mod = $routeAry[0];
$op  = $routeAry[1];

//定义AID
define('AID', (int)$_SESSION['aid']);

//判断权限
$defPurview = ['Admin-login','Admin-logout','Pay-countDay','Pay-countMonth'];//不需要权限的 操作 登录 登出

if( !in_array($route,$defPurview) ){

	if(!AID){
		CMD('UNAUTHORIZED');
	}

	$userPurview = explode(',', $_SESSION['purview'] );
	$purview = getCFG('purview');
	if( array_key_exists($route, $purview) ) {
		if( !in_array($purview[$route], $userPurview) ) {
			CMD('UNAUTHORIZED');
		}
	}elseif( array_key_exists($mod, $purview) ) {
		if( !in_array($purview[$mod], $userPurview) ) {
			CMD('UNAUTHORIZED');
		}
	}
}


//分模块处理
$fileName = $global['path']['root'].'_code/'.$mod.'.php';

if( !file_exists($fileName)){
	CMD('BAD_REQUEST');
}else{
	include($fileName);
}

$mod .='_control';

if( !method_exists($mod,$op) ){
	CMD('BAD_REQUEST');
}


$rs = $mod::$op($_P);


if($rs){
	CMD(200,$rs);
}else{
	CMD(200);
}
