<?php
/**
 * 日志类
 *
 * @author HJH
 * @version  2017-6-6
 */

class Log {	
	
	/**
	 * 构造函数
	 */
	function __construct() {	

	}


	/**
	 * 写入日志
	 * @param string $title 日志内容
	 * @param integer $uid 操作用户id
	 * @param integer $level 日志等级 1=普通|2=中等|3=危险
	 * @return array  返回日志id
	 * @access public
	 */
	public static function write($title,$uid,$level=1) {
		
		$DBGM = useDBGM();
		return $DBGM->insert('log',array(
			'title' 	  => $title,
			'uid' 		  => $uid,
			'username'	  => $_SESSION['username'],
			'nickname'	  => $_SESSION['nickname'],
			'level' 	  => $level,
			'ip' 		  => '127.0.0.1',
			'create_time' => time(),
		));
	}
}