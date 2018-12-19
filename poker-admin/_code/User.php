<?php
/**
 * User - 玩家管理相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  User_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 根据条件搜索玩家uid,nickname 
	 * @param  array uid OR phone Or nickname
	 * @return array 玩家列表
	 * @access public
	 */
	public static function search($_P) {

		$query = trim($_P['query']);
		if( !$query ){
			CMD('ILLEGAL_PARAM');
		}

		$rs = postUrl([		
			'query' 		=> $query
		]);

		return $rs['data'];
	}

	/**
	 * 取玩家列表 
	 * @param  array 搜索条件
	 * @return array 玩家列表
	 * @access public
	 */
	public static function getList($_P) {

		$rs = postUrl([		
			'uid' 		=> (int)$_P['uid'],
			'phone' 	=> $_P['phone'],
			'nickname' 	=> $_P['nickname'],
			'status' 	=> $_P['status'],
			'curPage' 	=> $_P['username'],

			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize' =>  $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];
	}

	/**
	 * 取用户
	 * @param int $uid
	 * @return array 玩家详情
	 * @access public
	 */
	public static function getValue($_P) {
				
		$rs = postUrl([		
			'uid' 		=> (int)$_P['uid']		
		]);

		return $rs['data'];
	}

	/**
	 * 锁定/解锁 玩家
	 * @param int $id 道具id
	 * @param int $status 状态 1=正常 0=冻结
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {	
		$rs = postUrl([		
			'uid' 		=> (int)$_P['uid'],
			'status' 	=> (int)$_P['status']
		]);

		return $rs['data'];
	}

	/**
	 * 删除玩家
	 * @param int array $ids 玩家uid列表
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
	 * 玩家 增加rmb
	 * @param int array $ids 玩家uid列表
	 * @param int $status 状态 1=正常 0=冻结
	 * @return void
	 * @access public
	 */
	public static function rmb($_P) {
		
		$rmb    = (int)$_P['rmb'];		
		
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
			'ids' 		=> $ids,
			'rmb' 		=> $rmb,
			'aid' 		=> AID
		]);

		return $rs['data'];

	}

	/**
	 * 玩家 增加gold
	 * @param int array $ids 玩家uid列表
	 * @param int $status 状态 1=正常 0=冻结
	 * @return void
	 * @access public
	 */
	public static function gold($_P) {
		
		$gold    = (int)$_P['gold'];		
		
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
			'ids' 		=> $ids,
			'gold' 		=> $gold,
			'aid' 		=> AID
		]);

		return $rs['data'];

	}


	/**
	 * 取在线玩家数
	 * @return int 在线玩家数
	 * @access public
	 */
	public static function getOnlineNum($_P) {
		$rs = postUrl([]);
		return $rs['data'];
	}
}