<?php
/**
 * 公共主文件
 *
 * @author   HJH
 * @version  2017-6-6
 */


//环境判断
$sysEnv = 'dev/';
if(@$_SERVER['SYS_ENV']) {
	$sysEnv = @$_SERVER['SYS_ENV'].'/';
}

//路径
$global = array();
$global['path']['root'] 	= dirname(__FILE__) . '/';
$global['path']['conf'] 	= $global['path']['root'] . '_conf/';		//配置文件目录
$global['path']['classes'] 	= $global['path']['root'] . '_classes/';	//类文件目录
$global['path']['lib'] 		= $global['path']['root'] . '_lib/';		//第三方库目录


//读取头部配置信息
require($global['path']['conf'].$sysEnv.'header.conf.php');

//读取常量配置
require($global['path']['conf'].$sysEnv.'const.conf.php');


/*
 * 动态加载需要的类文件
 */
spl_autoload_register(function($className) {
	global $global,$_CFG;

	$fileName = $global['path']['classes'].$className.'.class.php';
    if ( file_exists($fileName) ) {
		include($fileName);
	}    
});


/* 
 * 数据库连接
 */	
$DB = 0;
function useDB(){
	
	global $global,$DB,$sysEnv;
	
	if(!$DB){				
		include_once($global['path']['conf'].$sysEnv.'db.conf.php');
		include_once($global['path']['classes'] . 'DB.class.php');
		$DB = new DB($dbConf);
	}			
	return $DB;
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
	
	global $global,$_CFG, $sysEnv;

	if( !$name ) return;

	if(!$_CFG[$name]) {

		if($name=='data') {
			$_CFG[$name] = Conf::get();
		}else{
			include_once($global['path']['conf']. $sysEnv . $name . '.conf.php');
		}
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
function CMD($code=200, $data=''){
	
	$cmd = array(
		'code' => $code,
		'msg'  => '',
		'data' => []
	);

	if($code==200) {
		$cmd['data'] = $data;
	}else{
		$cmd['msg']  = $data;
	}

	echo json_encode($cmd);
	exit();
}


//开启session
if($startSessionFlag) {
	require($global['path']['classes'] . 'Session.class.php');
}

//检查是否停机维护
if($stopServerFlag) {
	$cache = getCache();
	if( (int)$cache->get('stop_server') ) {
		CMD(100000);
	}
}