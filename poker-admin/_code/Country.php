<?php
/**
 * Country - 国家配置获取接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Country_control{	
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 取国家英文名列表
	 * @return array 列表
	 * @access public
	 */
	public static function search($_P) {
		$rs = postUrl([
			'query' => $_P['query']
		]);
		return $rs['data'];
	}//getList


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
			'code'   		=> $_P['code'],
			'country'   	=> $_P['country'],
			'blindBet'		=> $_P['blindBet'],
			'rmbToclubRb'	=> $_P['rmbToclubRb'],
			'clubRbLeast'	=> $_P['clubRbLeast'],
			'orders'    	=> $_P['orders'],
			'status'    	=> (int)$_P['status']
		]);

		if($rs['data']==-1) {
			CMD('COUNTRY_DUPLICATE');
		}

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
			'cid'     		=> (int)$_P['cid'],
			'title'     	=> $_P['title'],
			'code'   		=> $_P['code'],
			'country'   	=> $_P['country'],
			'blindBet'		=> $_P['blindBet'],
			'rmbToclubRb'	=> $_P['rmbToclubRb'],
			'clubRbLeast'	=> $_P['clubRbLeast'],
			'orders'    	=> $_P['orders'],
			'status'    	=> (int)$_P['status']
		]);

		if($rs['data']==-1) {
			CMD('COUNTRY_DUPLICATE');
		}

		return $rs['data'];

	}

	/**
	 * 上架/下家
	 * @param int $cid cid
	 * @param int $status 状态 1上架 0下架
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {
		$rs = postUrl([		
			'cid' 		=> (int)$_P['cid'],				
			'status'	=> (int)$_P['status']
		]);

		return $rs['data'];
	}

	
}