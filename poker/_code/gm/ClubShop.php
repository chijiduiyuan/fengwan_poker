<?php
/**
 * ClubShop - 俱乐部商店配置相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
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
		
		$DB = useDB();

		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$total = $DB->getCount('shop');

		$limitForm = ($curPage-1)*$pageSize;

		$order = ' ORDER BY orders DESC LIMIT '.$limitForm.','.$pageSize;

		$list = $DB->getList('SELECT * FROM club_level_shop '.$order);

		return [
			'list' 	=> $list,
			'total' => $total
		];
	}//getList

	/**
	 * 添加
	 * @param array 信息	 
	 * @return void
	 * @access public
	 */
	public static function add($_P) {

		$DB = useDB();

		$info = [				
				'title'     	=> $_P['title'],
				'avatar'   		=> $_P['avatar'],
				'level'   		=> $_P['level'],
				'subagent_num'	=> $_P['subagent_num'],
				'member_num'	=> $_P['member_num'],
				'lucky_flag'	=> $_P['lucky_flag'],
				'time_num'		=> $_P['time_num'],
				'price'     	=> $_P['price'],
				'orders'    	=> $_P['orders'],
				'status'    	=> $_P['status']
			];
		
		$rs = $DB->insert('club_level_shop',$info);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 修改
	 * @param array 信息	
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
		$DB = useDB();

		$id = (int)$_P['id'];
		$info = [
			'title'     	=> $_P['title'],
			'avatar'   		=> $_P['avatar'],
			'level'   		=> $_P['level'],
			'subagent_num'	=> $_P['subagent_num'],
			'member_num'	=> $_P['member_num'],
			'lucky_flag'	=> $_P['lucky_flag'],
			'time_num'		=> $_P['time_num'],
			'price'     	=> $_P['price'],
			'orders'    	=> $_P['orders'],
			'status'    	=> $_P['status']
		];
		
		$rs = $DB->update('club_level_shop',$info,'id='.$id);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 上架/下家
	 * @param int $id id
	 * @param int $status 状态 1上架 0下架
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {
		$DB = useDB();
		$id 		= (int)$_P['id'];	
		$status		= (int)$_P['status'];	

		$rs = $DB->update('club_level_shop',[						
				'status' => $status
			],'id='.$id);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 删除
	 * @param int array $ids 商店id
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
		$rs = $DB->delete( 'club_level_shop',$where );
		if(!$rs){
			CMD('RECORD_NOT_FOUND');
		}
	}


}