<?php
/**
 * 错误码配置
 *
 * @author   HJH
 * @version  2017-7-28
 */


$_CFG['code'] = array(

	200 						=> '成功',
	'BAD_REQUEST' 				=> '访问错误',
	'UNAUTHORIZED' 				=> '没有权限',
	'FAILE' 					=> '操作失败',
	'INTERNAL_SERVER_ERROR' 	=> '系统错误',
	'SERVICE_UNAVAILABLE' 		=> '服务不存在',
	'ILLEGAL_PARAM' 			=> '非法的参数',
	'ACCOUNT_ERROR' 			=> '账号密码错误',
	'TOO_MANY_ERRORS' 			=> '错误次数太多，请稍后再试',
	'ACCOUNT_LOCK' 				=> '账号被锁定',
	'ACCOUNT_DUPLICATE' 		=> '帐号重复',

	'USER_NOT_FOUND' 			=> '玩家不存在',
	'RECORD_NOT_FOUND' 			=> '记录不存在',

	'SERVER_NOT_FOUND' 			=> '游戏服务器不存在',
	'ILLEGAL_PARAM_VCODE' 		=> '无效的版本号参数',
	'COUNTRY_DUPLICATE' 		=> '国家重复'

);