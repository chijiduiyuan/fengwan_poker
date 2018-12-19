<?php
/**
 * 游戏配置操作类
 *
 * @author HJH
 * @version  2017-8-7
 */


class  Conf{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 读取游戏配置并缓存
	 * @return array 配置信息
	 * @access public
	 */
	public static function get() {

		$cache = getCache();
		$cfg = $cache->get('game_conf_data');
		if($cfg) {
			return json_decode($cfg, true);

		}else{

			//抢锁
			$flag = false;
			if($cache->SETNX('game_conf_set_flag', 1)) {
				$flag = true;
			}


			$data = array();

			$DB = useDB();
			$list = $DB->getList('SELECT conf_key,conf_val FROM game_conf');
			foreach($list as $item) {

				$conf_val = json_decode($item['conf_val'], true);
				if( !isset($conf_val) ) {
					$conf_val = $item['conf_val'];
				}
				$data[$item['conf_key']] = $conf_val;
			}

			//写入缓存
			if($flag) {
				$cache->set('game_conf_data', json_encode($data), false);
				$cache->delete('game_conf_set_flag');
			}

			return $data;
		}

	}//get



}