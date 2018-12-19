<?php
/**
 * GameServer - 服务器相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  GameServer_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 取玩家列表 
	 * @param  array 搜索条件
	 * @return array 玩家列表
	 * @access public
	 */
	public static function getList($_P) {

		return getCFG('gameServer');
	}//getList

	/**
	 * 取用户
	 * @param int $uid
	 * @return array 玩家详情
	 * @access public
	 */
	public static function set($_P) {
		$serverId = (int)$_P['id'];		

		$urlCFG 	= getCFG('gameServer');
		
		if(!$urlCFG[$serverId]){
			CMD('SERVER_NOT_FOUND');
		}

		$_SESSION['gameServer'] = $serverId;

	}

	
}