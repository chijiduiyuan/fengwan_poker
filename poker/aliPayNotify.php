<?php
/**
 * 支付回调路口类
 *
 * @author HDS
 * @version  2017-10-30
 */

//是否需要检查停机维护状态
$stopServerFlag = true;

//是否开启session
$startSessionFlag = true;

require_once('common.inc.php');

$file_in = $_POST;

if(!$file_in) {
	echo "FAIL";
	exit();
}

$result = Shop::aliPayNotify($file_in);
echo $result;