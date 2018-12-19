<?php
/**
 * 公共主文件
 *
 * @author   HJH
 * @version  2017-7-28
 */

//关闭错误提示(生产环境使用)
// error_reporting(0);
// error_reporting(E_ALL ^ E_NOTICE);

ini_set("display_errors",0);
error_reporting(E_ALL ^ E_NOTICE);
// ini_set("error_reporting",E_ALL);
// ini_set("error_log","/var/log/php-error.log");
ini_set("log_errors",1);

//输出格式
header('Content-Type: application/json; charset=UTF-8');

//时区
function_exists('date_default_timezone_set') && date_default_timezone_set('PRC');

//环境判断
$sysEnv = 'dev/';
if($_SERVER['SYS_ENV']) {
	$sysEnv = $_SERVER['SYS_ENV'].'/';
}


//路径
$global = array();
$global['path']['root'] 	= dirname(__FILE__) . '/';

$global['path']['conf'] 	= $global['path']['root'] . '_conf/';		//配置文件目录
$global['path']['classes'] 	= $global['path']['root'] . '_classes/';	//类文件目录
$global['path']['lib'] 		= $global['path']['root'] . '_lib/';		//第三方库目录
$global['path']['output'] 	= $global['path']['root'] . 'output/';		//配置等文件输出目录


require($global['path']['conf'] . $sysEnv . 'header.conf.php');

//读取常量配置
require($global['path']['conf'] . $sysEnv . 'const.conf.php');


//开启session
if($startSessionFlag) {
	require($global['path']['classes'] . 'Session.class.php');
}


/*
 * 动态加载需要的类文件
 */
function __autoload($className) {
	
	global $global,$_CFG;

	$fileName = $global['path']['classes'].$className.'.class.php';
    
    if ( file_exists($fileName) ) {
		include($fileName);

	}else{
		
		$msg = 'Can\'t Find This Class: "'. $className. '" ';
		die($msg);
	}    
}

/* 
 * 数据库连接(GM)
 */	
$DBGM = 0;
function useDBGM(){
	
	global $global,$DBGM,$sysEnv;
	
	if(!$DBGM){
		include_once($global['path']['conf'] . $sysEnv . 'db.conf.php');
		include_once($global['path']['classes'] . 'DB.class.php');
		$DBGM = new DB($dbConfGM);
	}
	return $DBGM;
}

/**
 *  url请求
 */
function postUrl($data){

	global $_P;
	if(!$data['route']){
		$data['route'] = $_P['route'];
	}

	$urlCFG 	= getCFG('gameServer');
	$serverId 	= (int)$_SESSION['gameServer'];

	if(!$urlCFG[$serverId]){
		CMD('SERVER_NOT_FOUND');
	}

	$url = $urlCFG[$serverId]['url'];


	$ch = curl_init();  
	/*在这里需要注意的是，要提交的数据不能是二维数组或者更高 
	*例如array('name'=>serialize(array('tank','zhang')),'sex'=>1,'birth'=>'20101010') 
	*例如array('name'=>array('tank','zhang'),'sex'=>1,'birth'=>'20101010')这样会报错的*/  
	
	curl_setopt($ch, CURLOPT_URL, $url);  
	curl_setopt($ch, CURLOPT_POST, 1);  
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$rs=curl_exec($ch);  

 	curl_close($ch);  

	$json = json_decode($rs,1);

	if(!$json){
		var_export($rs);
		CMD('INTERNAL_SERVER_ERROR');		
	}
	
	if($json['code'] !== 200 ){			
		CMD($json['code']);
	}

 	return $json;
}

/* 
 * 内存缓存
 */	
$CACHE = 0;
function getCache(){
	
	global $CACHE;

	if(!$CACHE){
		$cacheType = CACHE_TYPE.'Cache';
		$CACHE= new $cacheType();
	}

	return $CACHE;
}


/**
 *  读取配置
 */
$_CFG = array();
function getCFG($name){
	
	global $global,$_CFG,$sysEnv;

	if( !$name ) return;

	if(!$_CFG[$name]) {
		include_once($global['path']['conf']. $sysEnv . $name . '.conf.php');
	}

	return $_CFG[$name];
}


/**
 *  随机数取值
 */
function randNum($min=1,$max=100){
	$randNumArray = array();
	for($i=$min;$i<=$max;$i++) {
		$randNumArray[] = $i;
	}
	$randNum_key = array_rand($randNumArray);
	$randNum = $randNumArray[$randNum_key];
	return $randNum;
}


/**
 *  输出JSON
 */
function CMD($code=200, $data=[]){
	
	$cfg = getCFG('code');


	$gameServer = getCFG('gameServer');
	$serverId 	 = (int)$_SESSION['gameServer'];
	$serverTitle = $gameServer[$serverId]['title'];

	$cmd = array(
		'server' => [
			'id' 	=> $serverId,
			'title' => $serverTitle
		],
		'code' => $code,
		'msg'  => $cfg[$code],
		'data' => $data
	);

	echo json_encode($cmd);
	exit();
}


