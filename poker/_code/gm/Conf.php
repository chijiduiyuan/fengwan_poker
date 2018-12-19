<?php
/**
 * Conf - 游戏配置相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
 */


class  Conf_control{	
	
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
	public static function get($_P) {

		$conf_type = $_P['conf_type'];

		$DB = useDB();
		return $DB->getList('SELECT conf_key,conf_val FROM game_conf WHERE conf_type=\''.$conf_type.'\'');
	}//get

	
	/**
	 * 写入游戏配置
	 * @param string $conf_type 配置类型
	 * @param string $conf_key 配置key
	 * @param string $conf_val 配置值
	 * @return bool 是否成功
	 * @access public
	 */
	public static function set($_P) {

		$conf_type = $_P['conf_type'];
		$conf_key  = $_P['conf_key'];
		$conf_val  = $_P['conf_val'];
		
		$DB = useDB();

		$info = $DB->getValue('SELECT conf_key,conf_type FROM game_conf WHERE conf_key=\''.$conf_key.'\'');
		if(!$info) {
			$DB->insert('game_conf', array('conf_type'=>$conf_type,'conf_key'=>$conf_key,'conf_val'=>$conf_val));
			return true;

		}elseif($info['conf_type']!=$conf_type) {
			return false;
		}

		$DB->update('game_conf', array('conf_val'=>$conf_val), 'conf_key=\''.$conf_key.'\' AND conf_type=\''.$conf_type.'\'');

		$cache = getCache();
		$cache->delete('game_conf_data');
		return true;
	}


	/**
	 * 读取配置信息
	 * @param string $conf_key 配置key
	 * @return array 配置信息
	 * @access public
	 */
	public static function getInfo($_P) {

		$conf_key  = $_P['conf_key'];

		$DB = useDB();
		return $DB->getValue('SELECT conf_val FROM game_conf WHERE conf_key=\''.$conf_key.'\'');
	}


}