<?php
/**
 * Room - 房间相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Room_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 取公共俱乐部房间列表
	 * @param  string $game 房间类型 dzPoker=德州扑克 cowWater=牛加水
	 * @return array 房间列表
	 * @access public
	 */
	public static function getList($_P) {
		$rs = postUrl([		
			'game' 		=> $_P['game'],
			
			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize'  =>  $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];

	}//getList

	/**
	 * 添加公共房间	 
	 * @param array 房间信息	 
	 * @return void
	 * @access public
	 */
	public static function add($_P) {
		$rs = postUrl([		
			'title'       => $_P['title'],
			'game'        => $_P['game'],
			'opTimeout'   => $_P['opTimeout'],
			'costScale'   => $_P['costScale'],
			'playerNum'   => $_P['playerNum'],
			'blindBet'    => $_P['blindBet'], //盲注/底注
			'minBet'      => $_P['minBet'],
			'maxBet'      => $_P['maxBet'],	
		]);

		return $rs['data'];

	}

	/**
	 * 修改公共房间信息	 
	 * @param array 房间信息	
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
		$rs = postUrl([		
			'id'       	  => (int)$_P['id'],
			'title'       => $_P['title'],
			'game'        => $_P['game'],
			'opTimeout'   => $_P['opTimeout'],
			'costScale'   => $_P['costScale'],
			'playerNum'   => $_P['playerNum'],
			'blindBet'    => $_P['blindBet'], //盲注/底注
			'minBet'      => $_P['minBet'],
			'maxBet'      => $_P['maxBet'],	
		]);

		return $rs['data'];
	}


	/**
	 * 删除公共房间
	 * @param int array $ids 房间id列表
	 * @return void
	 * @access public
	 */
	public static function delete($_P) {
		$idArr  = $_P['ids'];
		if(!is_array($idArr)) {
			CMD('ILLEGAL_PARAM');
		}
		//id必须是整形
		foreach ($idArr as $item) {
			if( !is_numeric( $item ) ){
				CMD('ILLEGAL_PARAM');
			}
		}
		$ids 	= implode(',',$idArr);

		$rs = postUrl([		
			'ids' 		=> $ids
		]);

		return $rs['data'];
	}


	/**
	 * 初始化公共俱乐部房间
	 * @return void
	 * @access public
	 */
	public static function initPublicRoom($_P) {
		$rs = postUrl([]);

		return $rs['data'];
	}
}