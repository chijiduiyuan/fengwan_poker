<?php
/**
 * Country - 国家配置相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
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

		$query = $_P['query'];

		$data = [];

		$DB = useDB();
		$list = $DB->getList('SELECT country FROM ip2nationcountries WHERE country LIKE \''.$query.'%\' LIMIT 20');
		foreach($list as $item) {
			$data[] = $item['country'];
		}

		return $data;
		
	}//search


	/**
	 * 取列表
	 * @return array 列表
	 * @access public
	 */
	public static function getList($_P) {
		
		$DB = useDB();

		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$total = $DB->getCount('country');

		$limitForm = ($curPage-1)*$pageSize;

		$order = ' ORDER BY orders DESC LIMIT '.$limitForm.','.$pageSize;

		$list = $DB->getList('SELECT * FROM country '.$order);

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

		//判断重复
		$cid = Country::getValue('cid', 'country=\''.$_P['country'].'\'');
		if( (int)$cid ) {
			return -1;
		}

		$info = [				
			'title'     	=> $_P['title'],
			'code'   		=> $_P['code'],
			'country'   	=> $_P['country'],
			'blindBet'		=> $_P['blindBet'],
			'rmbToclubRb'	=> $_P['rmbToclubRb'],
			'clubRbLeast'	=> $_P['clubRbLeast'],
			'status'     	=> $_P['status'],
			'orders'    	=> $_P['orders']
			];
		
		$rs = $DB->insert('country',$info);

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

		$cid = (int)$_P['cid'];


		//判断重复
		$rs = Country::getValue('cid', 'cid<>'.$cid.' AND country=\''.$_P['country'].'\'');
		if( (int)$rs ) {
			return -1;
		}

		$info = [
			'title'     	=> $_P['title'],
			'code'   		=> $_P['code'],
			'country'   	=> $_P['country'],
			'blindBet'		=> $_P['blindBet'],
			'rmbToclubRb'	=> $_P['rmbToclubRb'],
			'clubRbLeast'	=> $_P['clubRbLeast'],
			'status'     	=> $_P['status'],
			'orders'    	=> $_P['orders']
		];

		$DB->update('country',$info,'cid='.$cid);
	}

	/**
	 * 上架/下家
	 * @param int $cid cid
	 * @param int $status 状态 1上架 0下架
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {
		$DB = useDB();
		$cid 		= (int)$_P['cid'];	
		$status		= (int)$_P['status'];

		$rs = $DB->update('country',[						
				'status' => $status
			],'cid='.$cid);

		if(!$rs){
			CMD('FAILE');
		}
	}



}