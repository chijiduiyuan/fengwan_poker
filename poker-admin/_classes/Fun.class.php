<?php
/**
 * 工具函数类
 *
 * @author HJH
 * @version  2017-6-6
 */


class  Fun{

	/**
	 * 构造函数
	 */
	function __construct( ) {	
		
	}

	//密码加密
	public static function pwdEncrypt($pass, $encrypt) {
		return md5(md5($pass).$encrypt.LOGIN_SECRET_KEY);
	}


	/**
	 * 过期时间转换成剩余天数
	 * @param int $time 要转换的时间戳
	 * @return void
	 * @access public
	 */
	public static function ttod($time) {
		
		$t = (int)$time-time();
		if($t>0) {
			return ceil($t/86400);
		}else{
			return 0;
		}
	}


	//参数过滤
	public static function addslashe($param) {

		if(!$param || !is_array($param) || get_magic_quotes_gpc()) {
			return $param;
		}

		foreach($param as &$item) {
			if(is_array($item)) {
				$item = Fun::addslashe($item);
			}else{
				$item = addslashes($item);
			}
		}

		return $param;
	}


	/**
	 * 取当前ip
	 */
	public static function getIP() {
		
		global  $_SERVER;  
		
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$realip  =  $_SERVER["HTTP_X_FORWARDED_FOR"];  

		}elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$realip  =  $_SERVER["HTTP_CLIENT_IP"];

		}else{
			$realip  =  $_SERVER["REMOTE_ADDR"];  
		}

		return  $realip;
	}

}