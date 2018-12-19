<?php
/**
 * Club - 俱乐部 相关操作接口
 *
 * @author CXF
 * @version  2017-8-3
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
		$DB = useDB();

		$clubId 	= (int)$_P['clubId'];
		$title 		= $_P['title'];
		$status 	= $_P['status'];
		$create_uid	= (int)$_P['create_uid'];
		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$whereArr = [];
		$whereArr[] = '1=1';

		if( $clubId ){
			$whereArr[] = 'clubId='.$clubId ;		
		}
		if( $create_uid ){
			$whereArr[] = 'create_uid='.$create_uid ;		
		}
		if( $title ){
			$whereArr[] = 'title=\''.$title.'\'' ;		
		}
		if( is_numeric($status) ){
			$whereArr[] = 'status='.$status ;		
		}		
		$where = implode(' AND ',$whereArr);		
		
		$total = $DB->getCount('club',$where);	

		$limitForm = ($curPage-1)*$pageSize;			

		$where .= ' ORDER BY clubId DESC LIMIT '.$limitForm.','.$pageSize;

		$list = $DB->getList('SELECT * FROM club WHERE '.$where);

		//附加国家信息
		$countryList = Country::getList('cid,title', '1=1');
		foreach($list as &$item) {
			foreach($countryList as $val) {
				if($item['cid']==$val['cid']) {
					$item['country'] = $val['title'];
				}
			}
		}

		return [
			'list' 	=> $list,
			'total' => $total
		];

	}

	
	/**
	 * 锁定/解锁 俱乐部
	 * @param int $clubId 公告id
	 * @param int $status 状态 1解锁 0 锁定
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {

		$clubId 	= (int)$_P['clubId'];	
		$status		= (int)$_P['status'];		

		$rs = Club::edit([						
				'status'	=> $status
			],$clubId);

		if(!$rs){
			CMD('FAILE');
		}
	}


	/**
	 * 详情
	 * @param int $clubId 俱乐部id
	 * @return void
	 * @access public
	 */
	public static function getDetail($_P) {

		//管理员id
		$aid = (int)$_P['aid'];

		//俱乐部id
		$clubId = (int)$_P['clubId'];

		//生成token
		$token = md5(time().mt_rand());

		$val = $token.'_'.$clubId.'_'.$aid;

		$cache = getCache();
		$cache->set('gm_agent_flag_'.$aid, $val);

		return $val;
	}

		
}