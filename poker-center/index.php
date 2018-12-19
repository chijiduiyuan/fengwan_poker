<?php

//关闭错误提示(生产环境使用)
// error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);

//输出格式
header('Content-Type: application/json; charset=UTF-8');

$_P = $_GET+$_POST;

$channel     = (int)$_P['channel'];
$versionCode = (int)$_P['versionCode'];

$data = '';
if($channel==1) {
	
	//android
	$data = include('android.php');

}elseif($channel==2) {
	
	//ios
	$data = include('ios.php');
}

if( is_array($data) && $versionCode>=(int)$data['versionCode'] ) {
	$data = '';
}

$cmd = array(
	'code' => 200,
	'msg'  => '',
	'data' => $data
);

echo json_encode($cmd);