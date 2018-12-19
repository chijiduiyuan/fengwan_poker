<?php
/**
 * ClubShop - 俱乐部等级商店配置相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  ClubShop_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 取列表
	 * @return array 列表
	 * @access public
	 */
	public static function getList($_P) {
		$rs = postUrl([		
			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize'  => $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];

	}//getList

	/**
	 * 添加
	 * @param array 信息	 
	 * @return void
	 * @access public
	 */
	public static function add($_P) {
		$rs = postUrl([		
			'title'     	=> $_P['title'],
			'avatar'   		=> $_P['avatar'],
			'level'   		=> $_P['level'],
			'subagent_num'	=> $_P['subagent_num'],
			'member_num'	=> $_P['member_num'],
			'lucky_flag'	=> $_P['lucky_flag'],
			'time_num'		=> $_P['time_num'],
			'price'     	=> $_P['price'],
			'orders'    	=> $_P['orders'],
			'status'    	=> (int)$_P['status']
		]);

		return $rs['data'];
	}

	/**
	 * 修改
	 * @param array 信息	
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
		$rs = postUrl([		
			'id'     		=> (int)$_P['id'],
			'title'     	=> $_P['title'],
			'avatar'   		=> $_P['avatar'],
			'level'   		=> $_P['level'],
			'subagent_num'	=> $_P['subagent_num'],
			'member_num'	=> $_P['member_num'],
			'lucky_flag'	=> $_P['lucky_flag'],
			'time_num'		=> $_P['time_num'],
			'price'     	=> $_P['price'],
			'orders'    	=> $_P['orders'],
			'status'    	=> (int)$_P['status']
		]);

		return $rs['data'];

	}

	/**
	 * 上架/下家
	 * @param int $id id
	 * @param int $status 状态 1上架 0下架
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
	 * 删除
	 * @param int array $ids 商店id
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
			'ids' => $ids
		]);

		return $rs['data'];
	}


}