<?php
/**
 * 游戏配置操作类
 *
 * @author HJH
 * @version  2017-7-28
 */


class  Conf{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 读取游戏配置
	 * @param string $conf_type 配置类型
	 * @return array 配置信息
	 * @access public
	 */
	public static function get($conf_type) {

		$rs = postUrl([
			'route' 	=> 'Conf-get',
			'conf_type' => $conf_type
		]);

		return $rs['data'];
	}


	/**
	 * 写入游戏配置
	 * @param string $conf_type 配置类型
	 * @param string $conf_key 配置key
	 * @param string $conf_val 配置值
	 * @return bool 是否成功
	 * @access public
	 */
	public static function set($conf_type, $conf_key, $conf_val) {

		$rs = postUrl([
			'route' 	=> 'Conf-set',
			'conf_type' => $conf_type,
			'conf_key'  => $conf_key,
			'conf_val'  => $conf_val
		]);

		return $rs['data'];
	}


	/**
	 * 读取配置信息
	 * @param string $conf_key 配置key
	 * @return array 配置信息
	 * @access public
	 */
	public static function getInfo($conf_key) {

		$rs = postUrl([
			'route' 	=> 'Conf-getInfo',
			'conf_key'  => $conf_key
		]);

		return $rs['data'];
	}
	
}