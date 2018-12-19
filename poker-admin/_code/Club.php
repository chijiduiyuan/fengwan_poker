<?php
/**
 * Club - 俱乐部 相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Club_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 取俱乐部列表	
	 * @param string $curPage 当前页
	 * @param string $pageSize 每页条数
	 * @return array 公告列表
	 * @access public
	 */
	public static function getList($_P) {
		$rs = postUrl([		
			'clubId' 	=> $_P['clubId'],
			'title' 	=> $_P['title'],
			'status' 	=> $_P['status'],
			'create_uid'=> $_P['create_uid'],
			
			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize'  =>  $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];
	}

	
	/**
	 * 锁定/解锁 俱乐部
	 * @param int $clubId 公告id
	 * @param int $status 状态 1解锁 0 锁定
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {
		$rs = postUrl([		
			'clubId' 	=> $_P['clubId'],
			'status' 	=> $_P['status'],
			
			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize'  =>  $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];

	}


	/**
	 * 详情
	 * @param int $clubId 俱乐部id
	 * @return void
	 * @access public
	 */
	public static function getDetail($_P) {
		$rs = postUrl([
			'aid'	 => AID,
			'clubId' => $_P['clubId']
		]);

		return $rs['data'];

	}
		
}