<?php
/**
 * User - 玩家管理相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
 */


class  User_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 根据条件搜索玩家uid,nickname 
	 * @param  array uid OR phone Or nickname
	 * @return array 玩家列表
	 * @access public
	 */
	public static function search($_P) {
		$DB = useDB();

		$query 		= $_P['query'];
		
		$whereArr = [];
		if( is_numeric($query) ){
			$whereArr[] = 'uid='.(int)$query;
		}

		$whereArr[] = 'phone like \''.$query.'%\'';
		$whereArr[] = 'nickname like \''.$query.'%\'';
	
		$where = implode(' OR ',$whereArr);
		$list = $DB->getList('SELECT uid,phone,nickname FROM user WHERE '.$where.' LIMIT 0,20');

		$total = sizeof($list);

		return [
			'list' 	=> $list,
			'total' => $total
		];
	}
	
	/**
	 * 取玩家列表 
	 * @param  array 搜索条件
	 * @return array 玩家列表
	 * @access public
	 */
	public static function getList($_P) {
		
		$DB = useDB();

		$uid 		= (int)$_P['uid'];
		$phone		= $_P['phone'];
		$nickname	= $_P['nickname'];		
		$status 	= $_P['status'];
		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$whereArr = [];
		$whereArr[] = '1=1';

		if( $uid ){
			$whereArr[] = 'uid='.$uid;		
		}	
		if( is_numeric($status) ){
			$whereArr[] = 'status='.$status;		
		}	
		if( $phone ){
			$whereArr[] = 'phone=\''.$phone.'\'' ;		
		}	
		if( $nickname ){
			$whereArr[] = 'nickname=\''.$nickname.'\'' ;		
		}		
		$where = implode(' AND ',$whereArr);		
		$total = $DB->getCount('user',$where);	

		$limitForm = ($curPage-1)*$pageSize;			

		$where .= ' ORDER BY last_time DESC LIMIT '.$limitForm.','.$pageSize;
	
		$list = $DB->getList('SELECT * FROM user WHERE '.$where);
		
		$cfg = getCFG('data');
		foreach($list as &$item){
			unset($item['pos']);
			unset($item['audio']);
						

			// -------- VIP 信息  ----------- BEGIN
			$item['expir'] = $item['vip_endtime'];

			if((int)$item['vip_endtime']<=time()) {

				$item['vip_card'] = 0;
				
				$item['card_club_num']  = $cfg['clubCreateLimit'];
				$item['card_emoji_num'] = 0;
				$item['card_delay_num'] = 0;

			}
			unset( $item['vip_endtime'] );

			//转换过期时间
			Fun::ttod($item);

			// -------- VIP 信息  ----------- END



			// -------- 在线,房间信息  ----------- BEGIN
			// 
			//玩家位置 dzPoker=德州扑克 cowWater=牛加水 其他表示大厅中
			$item['room_type'] = ''; 

			//玩家在线情况0=不在线 1=在线 2=在游戏房间旁观 3=在游戏座位上 4,在座位上且游戏中
			$item['online_status'] = 0; 

			$uidWhere = 'uid='.$item['uid'];

			$online = User::getOnline('one','uid',$uidWhere) ;

			if( $online ){
				$item['online_status'] = 1;
			}

			$roomId = ClubRoom::getRoomId($uid);
			if($roomId){
				//表示在房间中
				$roomInfo = getRoomCache($roomId);	
				$item['roomType'] = $roomInfo['game']; //游戏类型
				$item['online_status'] = 2; //旁观中
				if($roomInfo['players'][$uid]){
					$item['online_status'] = 3;//座位上
					if( (int)$roomInfo['playTimeout']>time() ){
						$item['online_status'] = 4;//座位上且在游戏中
					}
				}
			}

			// -------- 在线,房间信息  ----------- END
		}

		return [
			'list' 	=> $list,
			'total' => $total
		];
	}//getList

	/**
	 * 取用户
	 * @param int $uid
	 * @return array 玩家详情
	 * @access public
	 */
	public static function getValue($_P) {
		$userList = User_control::getList($_P);

		if( sizeof($userList['list'])==0 ){
			CMD('FAILE');
		}

		$userInfo = $userList['list'][0];

		return $userInfo;

	}

	/**
	 * 锁定/解锁 玩家
	 * @param int $uid 玩家uid
	 * @param int $status 状态 1=正常 0=冻结
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {
		$DB = useDB();
		$uid 		= (int)$_P['uid'];	
		$status		= (int)$_P['status'];	

		$rs = $DB->update('user',[						
				'status'	=> $status
			],'uid='.$uid);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 删除玩家
	 * @param int array $ids 玩家uid列表
	 * @return void
	 * @access public
	 */
	public static function delete($_P) {
		$DB = useDB();

		$ids 	= $_P['ids'];		
		$idArr  = explode(',',$ids);
		//id必须是整形
		foreach ($idArr as $item) {
			if( !is_numeric( $item ) ){
				CMD('ILLEGAL_PARAM');
			}
		}

		$where = 'uid in ('.implode(',',$idArr).')';
		$rs = $DB->delete( 'user',$where );
		if(!$rs){
			CMD('RECORD_NOT_FOUND');
		}
	}

	/**
	 * 玩家 增加rmb
	 * @param int array $ids 玩家uid列表
	 * @param int $status 状态 1=正常 0=冻结
	 * @return void
	 * @access public
	 */
	public static function rmb($_P) {
		$DB 	= useDB();

		$aid    = $_P['aid']; //管理员id

		$ids    = $_P['ids'];		
		$rmb    = (int)$_P['rmb'];		
		$gold   = (int)$_P['gold'];		
		$idArr  = explode(',',$ids);
		//id必须是整形
		foreach ($idArr as $item) {
			if( !is_numeric( $item ) ){
				CMD('ILLEGAL_PARAM');
			}
		}

		$where = 'uid in ('.implode(',',$idArr).')';

		if( $rmb ==0 && $gold==0 ){
			CMD('FAILE');
		}


		$upArr = [];
		if($rmb !==0 ){
			$upStr = 'rmb=rmb';
			if($rmb>0){
				$upStr .= '+'.$rmb;
			}else{
				$upStr .= '-'.$rmb;
			}
			$upArr[] = $upStr;
		}

		if($gold !==0 ){
			$upStr = 'gold=gold';
			if($gold>0){
				$upStr .= '+'.$gold;
			}else{
				$upStr .= '-'.$gold;
			}
			$upArr[] = $upStr;
		}

		$upStr = implode(',',$upArr);

		$rs = $DB->exeSql('UPDATE user SET '.$upStr.' WHERE '.$where);
		if(!$rs){
			CMD('FAILE');
		}


		//生成充值订单
		foreach($idArr as $uid) {

			$orderNo = $uid.time().mt_rand();

			$ary = array(
				'order_no'		=> $orderNo,
				'uid'			=> $uid,
				'num'			=> $rmb,
				'extra'			=> 0,
				'price'			=> 0,
				'price_type'	=> 0,
				'pay_type'		=> 3,
				'status'		=> 1,
				'order_time'	=> time(),
				'order_date'	=> date('Y-m-d H:i:s'),
				'note'			=> '管理员赠送钻石,管理员aid：'.$aid
			);

			$DB->insert('pay_record', $ary);
		}

		$userList = $DB->getList('SELECT uid,rmb,gold FROM user WHERE '.$where);
		foreach ($userList as $item) {
			//循环给在线玩家推送消息
			$uid = (int)$item['uid'];
			$rmb = (int)$item['rmb'];
			$gold= (int)$item['gold'];

			$modifyInfo = [];

			if($rmb !==0 ){
				$modifyInfo['rmb'] = $rmb;
			}
			if($gold !==0 ){
				$modifyInfo['gold'] = $gold;
			}
			$rs = Fun::addNotify( [$uid], $modifyInfo,'user_info' );
		}
	}

	public static function gold($_P) {
		User_control::rmb($_P);
	}


	/**
	 * 读取在线玩家人数
	 * @return int status 服务器状态 1=开启状态  0=关闭状态
	 * @access public
	 */
	public static function getOnlineNum($_P) {		

		return [
			'onlineNum' => User::getOnlineNum()
		];
	}
}