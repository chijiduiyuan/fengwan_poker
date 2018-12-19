<?php
/**
 * Shop - 商店配置相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Shop_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 取配置列表 按类型
	 * @param  string $stype 类型 rmb=钻石 gold=金币 card=VIP卡	
	 * @return array 物品列表
	 * @access public
	 */
	public static function getList($_P) {
		$rs = postUrl([
			'flag' 		=> (int)$_P['flag'],

			'stype' 	=> $_P['stype'],
			
			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize'  => $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];

	}//getList

	/**
	 * 添加出售道具
	 * @param array 道具信息	 
	 * @return void
	 * @access public
	 */
	public static function add($_P) {

		$rs = postUrl([		
			'title'     => $_P['title'],
			'stype'     => $_P['stype'],
			'avatar'   	=> $_P['avatar'],
			'num'   	=> $_P['num'],
			'extra'   	=> $_P['extra'], //赠送的数量
			'price'    	=> $_P['price'], 
			'param'     => json_encode($_P['param']),
			'intro'     => $_P['intro'], //商品描述				
			'orders'    => $_P['orders'],	
			'status'    => (int)$_P['status'], //状态 1=启用 0=禁用
			'flag'    	=> $_P['flag']
		]);

		return $rs['data'];
	}

	/**
	 * 修改出售道具
	 * @param array 道具信息	
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {

		$rs = postUrl([		
			'id'     	=> (int)$_P['id'],
			'title'     => $_P['title'],
			'stype'     => $_P['stype'],
			'avatar'   	=> $_P['avatar'],
			'num'   	=> $_P['num'],
			'extra'   	=> $_P['extra'], //赠送的数量
			'price'    	=> $_P['price'], 
			'param'     => json_encode($_P['param']),
			'intro'     => $_P['intro'], //商品描述				
			'orders'    => $_P['orders'],	
			'status'    => (int)$_P['status'], //状态 1=启用 0=禁用
			'flag'    	=> $_P['flag']
		]);

		return $rs['data'];

	}

	/**
	 * 上架/下家 商城道具
	 * @param int $id 道具id
	 * @param int $status 状态 1上架 0下架
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {
		$rs = postUrl([
			'id' 		=> (int)$_P['id'],
			'status'	=> $_P['status']
		]);

		return $rs['data'];
	}

	/**
	 * 删除商店道具
	 * @param int array $ids 商店道具id列表
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