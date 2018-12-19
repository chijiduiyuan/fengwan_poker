<?php
/**
 * 俱乐部房间(桌子)操作类
 *
 * @author HJH
 * @version  2017-6-23
 */


class  ClubRoom{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}



	/**
	 * 创建房间
	 * @param array $ary 房间信息
	 * @return void
	 * @access public
	 */
	public static function createRoom($ary) {
		
		if(!$ary)return;

		$DB = useDB();
		
		return $DB->insert('club_room', $ary);
	}


	/**
	 * 读取房间列表
	 * @param string $field 字段
	 * @param string $where 条件
	 * @return array 房间列表
	 * @access public
	 */
	public static function getRoomList( $field, $where ) {
		$DB = useDB();
		return $DB->getList('SELECT '.$field.' FROM club_room WHERE '.$where);
	}


	/**
	 * 读取房间信息
	 * @param string $field 字段
	 * @param string $where 条件
	 * @return array 房间列表
	 * @access public
	 */
	public static function getRoomInfo( $field, $where ) {
		$DB = useDB();
		return $DB->getValue('SELECT '.$field.' FROM club_room WHERE '.$where);
	}


	/**
	 * 创建买入数据
	 * @param int $gameType 游戏类型 1=德州 2=牛加水
	 * @param int $bet 买入筹码数量
	 * @param int $clubId 俱乐部id
	 * @param int $roomId 房间id
	 * @param int $uid 会员uid
	 * @return void
	 * @access public
	 */
	public static function createAddBet($gameType, $bet, $clubId, $roomId, $uid=UID) {
		
		$ary = array(
			'clubId'		=> $clubId,
			'roomId'		=> $roomId,
			'uid'			=> $uid,
			'gameType'		=> $gameType,
			'curBet'		=> $bet,
			'totalBuyBet'	=> $bet
		);

		$DB = useDB();
		return $DB->insert('club_room_user', $ary);
	}


	/**
	 * 读取买入列表
	 * @param string $field 字段
	 * @param string $where 条件
	 * @return array 玩家战绩数据列表
	 * @access public
	 */
	public static function getBetList( $field, $where ) {
		$DB = useDB();
		return $DB->getList('SELECT '.$field.' FROM club_room_user WHERE '.$where);
	}


	/**
	 * 读取买入信息
	 * @param string $field 字段
	 * @param string $where 条件
	 * @return array 玩家战绩数据
	 * @access public
	 */
	public static function getBetInfo( $field, $where ) {
		$DB = useDB();
		return $DB->getValue('SELECT '.$field.' FROM club_room_user WHERE '.$where);
	}


	/**
	 * 读取买入的筹码数据
	 * @param string $field 字段
	 * @param int $roomId 房间id
	 * @param int $uid 会员uid
	 * @return array 玩家信息
	 * @access public
	 */
	public static function getBetData( $field, $roomId, $uid=UID) {
		return ClubRoom::getBetInfo($field, 'roomId='.$roomId.' AND uid='.$uid);
	}


	/**
	 * 预约买入筹码
	 * @param int $bet 买入筹码数量
	 * @param int $id 记录id
	 * @return void
	 * @access public
	 */
	public static function preAddBet($bet, $id) {
		$DB = useDB();
		return $DB->exeSql('UPDATE club_room_user SET preAddBet=preAddBet+'.$bet.', totalBuyBet=totalBuyBet+'.$bet.' WHERE id='.(int)$id);
	}


	/**
	 * 合并预约买入筹码
	 * @param int $id 记录id
	 * @return void
	 * @access public
	 */
	public static function plusPreBet($id) {
		$DB = useDB();
		return $DB->exeSql('UPDATE club_room_user SET curBet=curBet+preAddBet, preAddBet=0, profitLoss=(cast(curBet as signed)-cast(totalBuyBet as signed)) WHERE id='.(int)$id);
	}


	/**
	 * 增减当前筹码
	 * @param int $roomId 房间id
	 * @param int $uid 玩家id
	 * @param int $num 要增加的数量
	 * @param bool $add true增加,false减少
	 * @param bool $flag true可以减到0,false不足提示失败
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function bet( $roomId, $uid, $num, $add=true, $flag=false ) {		
		
		$DB = useDB();
		
		if($add){
			$sql='curBet+'.$num;

		}else{
			$oldNum = (int)ClubRoom::getBetData('curBet', $roomId, $uid);
			if($oldNum<$num){
				if($flag) {
					$num = $oldNum;
				}else{
					return false;
				}
			}
			$sql='curBet-'.$num;
		}
		
		$rs = $DB->exeSql('UPDATE club_room_user SET curBet='.$sql.', profitLoss=(cast(curBet as signed)-cast(totalBuyBet as signed)) WHERE roomId='.$roomId.' AND uid='.$uid);
		if(!$rs) {
			return false;
		}
		
		$nowNum = (int)ClubRoom::getBetData('curBet', $roomId, $uid);
		
		return $nowNum;	
	}


	/**
	 * 设置房间状态为开局
	 * @param int $roomId 房间id
	 * @return bool 成功则返回true,否则返回false
	 * @access public
	 */
	public static function setStart($roomId) {

		if(!$roomId)return;

		$DB = useDB();

		//更新状态
		return $DB->update('club_room', array('status'=>1,'startTime'=>time()), 'roomId='.$roomId);
	}


	/**
	 * 计入服务费
	 * @param int $roomId 房间id
	 * @param int $uid 玩家id
	 * @param int $num 要增加的数量
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function addScale( $roomId, $uid, $num ) {
		
		$DB = useDB();

		$rs = $DB->exeSql('UPDATE club_room_user SET scale=scale+'.(int)$num.' WHERE roomId='.$roomId.' AND uid='.$uid);
		if(!$rs) {
			return false;
		}
		
		return true;
	}


	/**
	 * 计入手牌数量
	 * @param int $roomId 房间id
	 * @param int $uid 玩家id
	 * @param int $bet 输赢数
	 * @param int $enterPool 德州入池
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function addHandNum( $roomId, $uid, $bet, $enterPool=0 ) {

		$sql = '';

		if($bet>0) {
			$sql .= ', handNumWin=handNumWin+1';
		}
		if( (int)$enterPool ) {
			$sql .= ', handNumPool=handNumPool+1';
		}
		
		$DB = useDB();
		$rs = $DB->exeSql('UPDATE club_room_user SET handNum=handNum+1'.$sql.' WHERE roomId='.$roomId.' AND uid='.$uid);
		if(!$rs) {
			return false;
		}
		
		return true;
	}


	/**
	 * 统计求和
	 * @param string $fieldKey 字段
	 * @param string $where 条件
	 * @return int 统计值
	 * @access public
	 */
	public static function getSum( $fieldKey, $where ) {
		
		$DB = useDB();

		return (int)$DB->getValue('SELECT SUM('.$fieldKey.') FROM club_room_user WHERE '.$where);
	}


	/**
	 * 统计计数
	 * @param string $where 条件
	 * @return int 统计值
	 * @access public
	 */
	public static function getCount($where) {
		
		$DB = useDB();

		return (int)$DB->getValue('SELECT COUNT(id) FROM club_room_user WHERE '.$where);
	}


	/**
	 * 房间统计计数
	 * @param string $where 条件
	 * @return int 统计值
	 * @access public
	 */
	public static function getRoomCount($where) {
		
		$DB = useDB();

		return (int)$DB->getValue('SELECT COUNT(roomId) FROM club_room WHERE '.$where);
	}


	/**
	 * 手牌统计计数
	 * @param string $where 条件
	 * @return int 统计值
	 * @access public
	 */
	public static function getHandCount($clubId, $where) {
		
		$DB = useDB();

		return (int)$DB->getValue('SELECT COUNT(handId) FROM '.Fun::handHash($clubId).' WHERE '.$where);
	}


	/**
	 * 根据uid取roomId
	 * @param int $uid 会员uid
	 * @return int roomId
	 * @access public
	 */
	public static function getRoomId( $uid=UID ) {

		if(!(int)$uid) return;
		
		$roomId = 0;

		$cache = getCache();

		$keys = $cache->keys('upPlayers:*:'.$uid);
		if( count($keys)<=0 ) {
			$keys = $cache->keys('players:*:'.$uid);
		}

		if( count($keys)>0 ) {
			$keyAry = explode(':',$keys[0]);
			$roomId = (int)$keyAry[1];
		}

		return $roomId;
	}


	/**
	 * 根据roomId取clubId
	 * @param int $roomId 房间id
	 * @return int clubId
	 * @access public
	 */
	public static function getClubId( $roomId ) {
		
		if(!(int)$roomId) return;
		
		$clubId = 0;

		$cache = getCache();

		$keys = $cache->keys('roomInfo:*:'.$roomId);
		if( count($keys)>0 ) {
			$keyAry = explode(':',$keys[0]);
			$clubId = (int)$keyAry[1];
		}

		return $clubId;
	}


	/**
	 * 读取房间缓存信息
	 * @param int $roomId 房间id
	 * @return array
	 * @access public
	 */
	public static function getRoomCache($roomId) {

		if(!(int)$roomId) return;

		$cache = getCache();

		//查找房间key
		$keys = $cache->keys('roomInfo:*:'.$roomId);
		if( count($keys)<=0 ) {
			return false;
		}

		//读取房间信息
		$roomInfo = $cache->getArray($keys[0]);
		
		//获取房间剩余缓存过期时间
		if($roomId>0) { //只有私人俱乐部房间才做过期判断
			$timeFlag = (int)$roomInfo['status'] ? 3600 : 0;
			$roomttl = $cache->ttl($keys[0]);
			if($roomttl<=$timeFlag) {

				//如果游戏正在进行中，则不判断房间超时
				if( (int)$roomInfo['playTimeout']<=time() ) {
					ClubRoom::delRoomCache($roomId); //房间过期清除缓存
					return false;
				}
			}
		}

		//坐下及旁观者
		$roomInfo['players']   = array();
		$roomInfo['upPlayers'] = array();

		//查找房间中坐下的人
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			//读取玩家信息
			$userInfo = $cache->getArray($pItem);

			//合并玩家信息到房间信息
			$roomInfo['players'][$userInfo['uid']] = $userInfo;
		}

		//查找房间中旁观的人
		$upKeys = $cache->keys('upPlayers:'.$roomId.':*');
		foreach($upKeys as $upItem) {
			//读取玩家信息
			$userInfo = $cache->getArray($upItem);

			//合并玩家信息到房间信息
			$roomInfo['upPlayers'][$userInfo['uid']] = $userInfo;
		}

		//牌局剩余时间换算
		if($roomId>0) {
			$sytime = $roomttl-$timeFlag;
			$roomInfo['roomTime'] = ($sytime>0) ? $sytime : 0 ;
		}

		return $roomInfo;
	}


	/**
	 * 清除过期的房间及玩家
	 * @param int $roomId 房间id
	 * @return bool
	 * @access public
	 */
	public static function delRoomCache($roomId) {

		$cache = getCache();

		//清除房间
		$keys = $cache->keys('roomInfo:*:'.$roomId);
		foreach($keys as $item) {
			$cache->delete($item);
		}

		//清除玩家
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			$cache->delete($pItem);
		}
		$upKeys = $cache->keys('upPlayers:'.$roomId.':*');
		foreach($upKeys as $upItem) {
			$cache->delete($upItem);
		}
		
		return true;
	}


	/**
	 * 读取房间中的玩家uid
	 * @param int $roomId 房间id
	 * @return array
	 * @access public
	 */
	public static function getUID($roomId) {

		if(!(int)$roomId) return;
		
		$cache = getCache();

		$uidArr = array();

		//查找房间中坐下的人
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			$keyAry = explode(':',$pItem);
			$uidArr[] = (int)$keyAry[2];
		}

		//查找房间中旁观的人
		$pKeys = $cache->keys('upPlayers:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			$keyAry = explode(':',$pItem);
			$uidArr[] = (int)$keyAry[2];
		}

		return $uidArr;
		
	}


	/**
	 * 写入手牌数据
	 * @param int $clubId 俱乐部id
	 * @param int $roomId 房间id
	 * @param json $underCards 底牌
	 * @param json $handJson 手牌
	 * @return void
	 * @access public
	 */
	public static function addHandData($clubId, $roomId, $gameType, $underCards, $handJson) {
		
		$ary = array(
			'clubId'	 => $clubId,
			'roomId' 	 => $roomId,
			'gameType' 	 => $gameType,
			'underCards' => json_encode($underCards),
			'handJson' 	 => json_encode($handJson),
			'createTime' => time()
		);

		$DB = useDB();
		return $DB->insert(Fun::handHash($clubId), $ary);
	}


	/**
	 * 读取手牌列表
	 * @param string $field 字段
	 * @param string $where 条件
	 * @return array 手牌列表
	 * @access public
	 */
	public static function getHandList($clubId, $field, $where) {
		$DB = useDB();
		return $DB->getList('SELECT '.$field.' FROM '.Fun::handHash($clubId).' WHERE '.$where);
	}


	/**
	 * 写入幸运牌数据
	 * @param int $clubId 俱乐部id
	 * @param int $roomId 房间id
	 * @param int $uid 会员uid
	 * @param int $cardType 幸运牌类型
	 * @param json $cardList 牌组
	 * @return void
	 * @access public
	 */
	public static function addLuckyData($clubId, $roomId, $uid, $cardType, $cardList) {
		
		$ary = array(
			'clubId'	 => $clubId,
			'roomId' 	 => $roomId,
			'uid' 	 	 => $uid,
			'cardType' 	 => $cardType,
			'cardList' 	 => json_encode($cardList)
		);

		$DB = useDB();
		return $DB->insert('club_room_lucky', $ary);
	}

	//获取当前俱乐部当前玩家的战绩列表
	public static function getTuiUserList($uid,$clubId) {
		if(!$clubId || !$uid){
			return;
		}
		$DB = useDB();
		return $DB->getList('SELECT * FROM club_room_user WHERE uid='.$uid.' AND clubId='.$clubId);
	}
}