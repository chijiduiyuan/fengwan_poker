<?php
/**
 * gm操作入口
 *
 * @author   HJH
 * @version  2017-7-17
 */


require_once('common.inc.php');

//判断来路IP
if(Fun::getIP()!=GM_MANAGE_IP) {
	// exit();
}


//参数
$_P = $_GET+$_POST;

//控制器和操作
$routeAry = explode('-', $_P['route']);
$mod = $routeAry[0];
$op  = $routeAry[1];


$fileName = $global['path']['root'].'_code/gm/'.$mod.'.php';

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
	CMD();
}
exit;