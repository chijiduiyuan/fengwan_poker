<?php
/**
 * Room - 房间相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
 */


class  Room_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 取公共俱乐部房间列表
	 * @param  string $game 房间类型 dzPoker=德州扑克 cowWater=牛加水
	 * @return array 房间列表
	 * @access public
	 */
	public static function getList($_P) {
		
		$DB = useDB();

		$game = $_P['game'];
		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$whereArr = [];
		$whereArr[] = '1=1';

		if( $game ){
			$whereArr[] = 'game=\''.$game.'\'' ;		
		}		
		$where = implode(' AND ',$whereArr);		
		$total = $DB->getCount('public_room',$where);	

		$limitForm = ($curPage-1)*$pageSize;			

		$where .= ' ORDER BY blindBet ASC,id DESC LIMIT '.$limitForm.','.$pageSize;

		$roomList = $DB->getList('SELECT * FROM public_room WHERE '.$where);

		$list = [];
		foreach ($roomList as $item) {		
			
			//组织数据
			$arr = [
				'id'       	  => $item['id'],
				'title'       => $item['title'],
				'game'        => $item['game'],
				'opTimeout'   => $item['opTimeout'],
				'costScale'   => $item['costScale'],
				'playerNum'   => $item['playerNum'],
				'blindBet'    => $item['blindBet'], //盲注/底注
				'minBet'      => $item['minBet'],
				'maxBet'      => $item['maxBet']
			];

			$list[] = $arr;
		}

		return [
			'list' 	=> $list,
			'total' => $total
		];
	}//getList

	/**
	 * 添加公共房间	 
	 * @param array 房间信息	 
	 * @return void
	 * @access public
	 */
	public static function add($_P) {

		$DB = useDB();

		$roomInfo = [
				'title'       => $_P['title'],
				'game'        => $_P['game'],
				'opTimeout'   => $_P['opTimeout'],
				'costScale'   => $_P['costScale'],
				'playerNum'   => $_P['playerNum'],
				'blindBet'    => $_P['blindBet'], //盲注/底注
				'minBet'      => $_P['minBet'],
				'maxBet'      => $_P['maxBet']
		];
		
		$rs = $DB->insert('public_room',$roomInfo);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 修改公共房间信息	 
	 * @param array 房间信息	
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
		$DB = useDB();

		$id 		= (int)$_P['id'];
		$roomInfo = [
				'title'       => $_P['title'],
				'game'        => $_P['game'],
				'opTimeout'   => $_P['opTimeout'],
				'costScale'   => $_P['costScale'],
				'playerNum'   => $_P['playerNum'],
				'blindBet'    => $_P['blindBet'], //盲注/底注
				'minBet'      => $_P['minBet'],
				'maxBet'      => $_P['maxBet']
		];
		
		$rs = $DB->update('public_room',$roomInfo,'id='.$id);

		if(!$rs){
			CMD('FAILE');
		}
	}



	/**
	 * 删除公共房间
	 * @param int array $ids 房间id列表
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
		$rs = $DB->delete( 'public_room',$where );
		if(!$rs){
			CMD('RECORD_NOT_FOUND');
		}
	}


	/**
	 * 初始化公共俱乐部房间
	 * @return void
	 * @access public
	 */
	public static function initPublicRoom($_P) {

		$clubId = -1;
		$roomId = -1;

		$DB = useDB();
		$list = $DB->getList('SELECT * FROM public_room ORDER BY blindBet ASC,id DESC');

		foreach ($list as $item) {

			//清除旧房间及玩家缓存
			ClubRoom::delRoomCache($roomId);
			
			//组织数据
			$ary = array(
				'roomId'      => $roomId,
				'clubId'      => $clubId,
				'title'       => $item['title'],
				'game'        => $item['game'],
				'opTimeout'   => $item['opTimeout'],
				'costScale'   => $item['costScale'],
				'playerNum'   => $item['playerNum'],
				'blindBet'    => $item['blindBet'],
				'minBet'      => $item['minBet'],
				'maxBet'      => $item['maxBet'],
				'enableBuy'   => 0,
				'status'      => 1,
				'playTimeout' => 0,
				'public'      => 1,
				'bankerSite'  => -1
			);
			
			//牛加水处理
			if($item['game']!='dzPoker') {
				$ary['baseBet'] = $ary['blindBet'];
				$ary['siteNum'] = $ary['playerNum'];
			}
			
			//写入缓存
			$cache = getCache();
			$cache->setArray('roomInfo:'.$clubId.':'.$roomId, $ary, false);

			$roomId--;
		}

	}
}