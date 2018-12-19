<?php
/**
 * Notice - 公告 相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Notice_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 取公告列表
	 * @param int $status 公告状态0=未发布 1=已发布 
	 * @param string $curPage 当前页
	 * @param string $pageSize 每页条数
	 * @return array 公告列表
	 * @access public
	 */
	public static function getList($_P) {
		$rs = postUrl([		
			'status' 	=> $_P['status'],
			
			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize'  =>  $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];

	}

	/**
	 * 取公告详情
	 * @param int $id 公告id
	 * @return array 公告详情
	 * @access public
	 */
	public static function getValue($_P) {

		$rs = postUrl([		
			'id' 	=> (int)$_P['id']
		]);

		return $rs['data'];
	}

	/**
	 * 添加公告	 
	 * @param string $title 公告标题
	 * @param string $content 公告内容
	 * @param int $status 公告状态 1发布 0 未发布
	 * @return void
	 * @access public
	 */
	public static function add($_P) {
		$rs = postUrl([		
			'title' 	=> $_P['title'],
			'content' 	=> $_P['content'],				
			'status'	=> (int)$_P['status'],
			'createTime' => (int)$_p['createTime']    
		]);

		return $rs['data'];
	}

	/**
	 * 修改公告	 
	 * @param int $id 公告id
	 * @param string $title 公告标题
	 * @param string $content 公告内容
	 * @param int $status 公告状态 1发布 0 未发布
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
		
		$rs = postUrl([		
			'id' 		=> (int)$_P['id'],
			'title' 	=> $_P['title'],
			'content' 	=> $_P['content'],				
			'status'	=> (int)$_P['status'],
			'createTime' => (int)$_p['createTime']    
		]);

		return $rs['data'];
	}

	/**
	 * 发布/不发布 公告	 
	 * @param int $id 公告id
	 * @param int $status 公告状态 1发布 0 未发布
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {

		$rs = postUrl([		
			'id' 		=> (int)$_P['id'],				
			'status'	=> (int)$_P['status']
		]);

		return $rs['data'];
	}

	/**
	 * 删除公告
	 * @param int array $ids 公告id列表
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


	
}