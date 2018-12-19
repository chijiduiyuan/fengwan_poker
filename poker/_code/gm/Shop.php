<?php
/**
 * Shop - 商店配置相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
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
		
		$DB = useDB();

		$flag  = $_P['flag'];

		$stype = $_P['stype'];
		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$whereArr = [];
		$whereArr[] = '(flag='.$flag.' OR flag=3)';

		if( $stype ){
			$whereArr[] = 'stype=\''.$stype.'\'' ;
		}

		$where = implode(' AND ',$whereArr);
		$total = $DB->getCount('shop',$where);

		$limitForm = ($curPage-1)*$pageSize;

		$where .= ' ORDER BY orders DESC LIMIT '.$limitForm.','.$pageSize;

		$list = $DB->getList('SELECT * FROM shop WHERE '.$where);
		// foreach($list as &$item) {
		// 	if($item['stype']=='card') {
		// 		$item['param'] = json_decode($item['param']);
		// 	}
		// }

		return [
			'list' 	=> $list,
			'total' => $total
		];
	}//getList

	/**
	 * 添加出售道具
	 * @param array 道具信息	 
	 * @return void
	 * @access public
	 */
	public static function add($_P) {

		$DB = useDB();

		$info = [				
				'title'     => $_P['title'],
				'stype'     => $_P['stype'],
				'avatar'   	=> $_P['avatar'],
				'num'   	=> $_P['num'],
				'extra'   	=> $_P['extra'], //赠送的数量
				'price'    	=> $_P['price'], 
				'param'     => $_P['param'],
				'intro'     => $_P['intro'], //商品描述				
				'orders'    => $_P['orders'],	
				'status'    => $_P['status'], //状态 1=启用 0=禁用
				'flag'    	=> $_P['flag'] //显示 1=app 2=web 3=全部
			];
		
		$rs = $DB->insert('shop',$info);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 修改出售道具
	 * @param array 道具信息	
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
		
		$DB = useDB();

		$id 		= (int)$_P['id'];
		$info = [
			'title'     => $_P['title'],
			'stype'     => $_P['stype'],
			'avatar'   	=> $_P['avatar'],
			'num'   	=> $_P['num'],
			'extra'   	=> $_P['extra'], //赠送的数量
			'price'    	=> $_P['price'],
			'param'     => $_P['param'],
			'intro'     => $_P['intro'], //商品描述
			'orders'    => $_P['orders'],
			'status'    => $_P['status'], //状态 1=启用 0=禁用
			'flag'    	=> $_P['flag'] //显示 1=app 2=web 3=全部
		];
		
		$DB->update('shop',$info,'id='.$id);
	}

	/**
	 * 上架/下家 商城道具
	 * @param int $id 道具id
	 * @param int $status 状态 1上架 0下架
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {
		$DB = useDB();
		$id 		= (int)$_P['id'];
		$status		= $_P['status'];

		$rs = $DB->update('shop',[						
				'status'	=> $status
			],'id='.$id);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 删除商店道具
	 * @param int array $ids 商店道具id列表
	 * @return void
	 * @access public
	 */
	public static function delete($_P) {
		$DB = useDB();

		$ids 	= $_P['ids'];		

		$idArr = explode(',',$ids);
		//id必须是整形
		foreach ($idArr as $item) {
			if( !is_numeric( $item ) ){
				CMD('ILLEGAL_PARAM');
			}
		}

		$where = 'id in ('.implode(',',$idArr).')';
		$rs = $DB->delete( 'shop',$where );
		if(!$rs){
			CMD('RECORD_NOT_FOUND');
		}
	}


}