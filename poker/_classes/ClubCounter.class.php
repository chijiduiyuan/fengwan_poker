<?php
/**
 * 俱乐部柜台操作类
 *
 * @author HJH
 * @version  2017-6-18
 */


class  ClubCounter{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}



	/**
	 * 新增收发记录
	 * @param int $clubId 俱乐部id
	 * @param int $uid 会员uid
	 * @param int $coin 收发数量
	 * @param int $scale 服务费
	 * @param int $type 收发类型
	 * @return int
	 * @access public
	 */
	public static function add($clubId, $uid, $coin, $scale, $type) {

		$ary = array(
			'clubId' 		=> $clubId,
			'uid_operator' 	=> UID,
			'uid_target' 	=> $uid,
			'coin' 			=> $coin,
			'scale' 		=> $scale,
			'type' 			=> $type,
			'time' 			=> time()
		);

		$DB = useDB();
		return $DB->insert('club_counter_record', $ary);
	}

	//添加抽成进账记录
	public static function addCut($clubId,$roomId,$actUid,$uid,$coin) {

		$ary = array(
			'clubId' 		=> $clubId,
			'roomId'		=> $roomId,
			'uid_operator' 	=> $actUid,
			'uid_target' 	=> $uid,
			'coin' 			=> $coin,
			'scale' 		=> 0,
			'type' 			=> 4,
			'time' 			=> time()
		);

		$DB = useDB();
		//$rel = $DB->getValue('SELECT * FROM club_counter_record WHERE clubId='.$clubId.' AND roomId='.$roomId.' AND uid_operator='.$actUid.' AND uid_target='.$uid);
		return $DB->insert('club_counter_record', $ary);
		// if($rel){
		// 	return $DB->exeSql('UPDATE club_counter_record SET coin='.$coin.' WHERE clubId='.(int)$clubId.' AND roomId='.$roomId.' AND uid_operator='.$actUid.' AND uid_target='.$uid);
		// }else{
		// 	return $DB->insert('club_counter_record', $ary);
		// }
	}


	/**
	 * 读取收发记录
	 * @param string $where 条件
	 * @return array 记录列表
	 * @access public
	 */
	public static function getList($where) {
		$DB = useDB();
		return $DB->getList('SELECT id,uid_operator,uid_target,coin,type,time FROM club_counter_record WHERE '.$where);
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

		return (int)$DB->getValue('SELECT SUM('.$fieldKey.') FROM club_counter_record WHERE '.$where);
	}

	//推广员自身抽成进账列表
	public function getInList($clubId,$inviteUid){
		if(!$clubId || !$inviteUid){
			return;
		};
		$DB = useDB();
		return $DB->getList('SELECT * FROM club_counter_record WHERE clubId='.$clubId.' AND uid_operator='.$inviteUid.' AND type=4 ORDER BY id DESC');
	}

	//推广员自身抽成出账列表
	public function getOutList($clubId,$inviteUid){
		if(!$clubId || !$inviteUid){
			return;
		};
		$DB = useDB();
		return $DB->getList('SELECT * FROM club_counter_record WHERE clubId='.$clubId.' AND uid_target='.$inviteUid.' AND type=3 ORDER BY id DESC');
	}

	//推广员下级抽成进账详情
	public function getMemberInfo($clubId,$inviteUid,$uid){
		$DB = useDB();
		return $DB->getList('SELECT * FROM club_counter_record WHERE clubId='.$clubId.' AND uid_operator='.$inviteUid.' AND uid_target='.$uid.' AND type=4 ORDER BY id DESC');
	}
}