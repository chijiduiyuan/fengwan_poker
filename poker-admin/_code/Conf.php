<?php
/**
 * Conf - 游戏配置 相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Conf_control{	
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 取游戏开关配置
	 * @return array 配置
	 * @access public
	 */
	public static function gameGet($_P) {

		$data = array();

		$list = Conf::get('game');
		foreach($list as $item) {
			$data[$item['conf_key']] = (int)$item['conf_val'];
		}

		return $data;
	}

	/**
	 * 保存游戏开关配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function gameSet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('game', $key, (int)$value);
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}





	/**
	 * 取玩家配置
	 * @return array 配置
	 * @access public
	 */
	public static function userGet($_P) {

		$data = array();

		$list = Conf::get('user');
		foreach($list as $item) {
			$data[$item['conf_key']] = (int)$item['conf_val'];
		}

		return $data;
	}

	/**
	 * 保存玩家配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function userSet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('user', $key, (int)$value);
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}





	/**
	 * 取俱乐部配置
	 * @return array 配置
	 * @access public
	 */
	public static function clubGet($_P) {

		$data = array();

		$list = Conf::get('club');
		foreach($list as $item) {
			$data[$item['conf_key']] = (float)$item['conf_val'];
		}

		return $data;
	}

	/**
	 * 保存俱乐部配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function clubSet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('club', $key, $value);
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}





	/**
	 * 取幸运玩家配置
	 * @return array 配置
	 * @access public
	 */
	public static function luckyGet($_P) {

		$data = array();

		$list = Conf::get('lucky');
		foreach($list as $item) {
			$data[$item['conf_key']] = json_decode($item['conf_val']);
		}

		return $data;
	}


	/**
	 * 保存幸运玩家配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function luckySet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('lucky', $key, json_encode($value));
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}





	/**
	 * 取道具配置
	 * @return array 配置
	 * @access public
	 */
	public static function propGet($_P) {

		$data = array();

		$list = Conf::get('prop');
		foreach($list as $item) {
			$data[$item['conf_key']] = (int)$item['conf_val'];
		}

		return $data;
	}

	/**
	 * 保存道具配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function propSet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('prop', $key, (int)$value);
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}





	/**
	 * 取短信配置
	 * @return array 配置
	 * @access public
	 */
	public static function smsGet($_P) {

		$data = array();

		$list = Conf::get('sms');
		foreach($list as $item) {
			$data[$item['conf_key']] = $item['conf_val'];
		}

		return $data;
	}

	/**
	 * 保存短信配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function smsSet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('sms', $key, $value);
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}





	/**
	 * 取系统配置
	 * @return array 系统配置
	 * @access public
	 */
	public static function systemGet($_P) {

		$data = array();

		$list = Conf::get('system');
		foreach($list as $item) {
			$data[$item['conf_key']] = $item['conf_val'];
		}

		return $data;
	}

	/**
	 * 保存系统配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function systemSet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('system', $key, $value);
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}





	/**
	 * 取房间配置
	 * @return array 系统配置
	 * @access public
	 */
	public static function roomGet($_P) {

		$data = array();

		$list = Conf::get('room');
		foreach($list as $item) {
			$data[$item['conf_key']] = json_decode($item['conf_val']);
		}

		return $data;
	}

	/**
	 * 保存房间配置
	 * @param  array $_P 配置
	 * @return bool true成功 false失败
	 * @access public
	 */
	public static function roomSet($_P) {

		unset($_P['route']);

		foreach ($_P as $key => $value) {
			$rs = Conf::set('room', $key, json_encode($value));
			if(!$rs) {
				CMD('ILLEGAL_PARAM');
			}
		}
	}
	
		
}