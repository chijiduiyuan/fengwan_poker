<?php
/**
 * 俱乐部会员操作类
 *
 * @author HJH
 * @version  2017-6-10
 */


class  ClubMember{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 添加会员
	 * @param int $clubId 俱乐部id
	 * @return void
	 * @access public
	 */
	public static function add($clubId, $uid,$inviteUid) {
		
		if(!$clubId || !$uid)return;

		$DB = useDB();

		$id = (int)$DB->getValue('SELECT id FROM club_member WHERE clubId='.$clubId.' AND uid='.$uid);
		if($id>0) {
			$rs = $DB->update('club_member', array('status'=>1,'manage'=>0,'purview'=>'[]','time'=>time(),'tuiguang'=>0), 'id='.$id);

		}else{
			$memAry = array(
				'clubId'=> $clubId,
				'uid'	=> $uid,
				'time'	=> time(),
				'inviteUid'=>$inviteUid
			);
			$rs = $DB->insert('club_member', $memAry);
		}
		
		return $rs;
	}


	/**
	 * 修改会员资料
	 * @param array $param 修改的参数数组
	 * @param int $clubId 俱乐部id
	 * @param int $uid 会员id
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function edit( $param, $clubId, $uid, $where='' ) {
		$DB = useDB();

		if($where) {
			$where = ' AND '.$where;
		}
		return $DB->update('club_member', $param, 'clubId='.$clubId.' AND uid='.$uid.$where);
	}


	/**
	 * 设置会员状态为删除
	 * @param int $clubId 俱乐部id
	 * @param int $uid 会员id
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function delete( $clubId, $uid ) {

		if(!$clubId || !$uid)return;

		//更新状态
		return ClubMember::edit(array('status'=>2), $clubId, $uid, 'manage<>'.CLUB_MANAGE_CREATOR);
	}


	/**
	 * 备注会员资料
	 * @param int $clubId 俱乐部id
	 * @param int $uid 会员id
	 * @param string $note 备注
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function note( $clubId, $uid, $note ) {

		if(!$clubId || !$uid || !$note)return;

		//更新状态
		return ClubMember::edit(array('note'=>$note), $clubId, $uid);
	}


	/**
	 * 增减会员俱乐部币(筹码)
	 * @param int $clubId 俱乐部id
	 * @param int $uid 会员id
	 * @param int $num 要增加的数量
	 * @param bool $add true增加,false减少
	 * @param bool $flag true可以减到0,false不足提示失败
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function coin( $clubId, $uid, $num, $add=true, $flag=false ) {		
		
		$DB = useDB();
		
		if($add){
			$sql='coin+'.$num;

		}else{
			$oldNum = (int)ClubMember::getInfo('coin', $clubId, $uid);
			if($oldNum<$num){
				if($flag) {
					$num = $oldNum;
				}else{
					return false;
				}
			}
			$sql='coin-'.$num;
		}
		
		$rs = $DB->exeSql('UPDATE club_member SET coin='.$sql.' WHERE clubId='.$clubId.' AND uid='.$uid.' AND status=1');
		if(!$rs) {
			return false;
		}
		
		$nowNum = (int)ClubMember::getInfo('coin', $clubId, $uid);
		
		return $nowNum;	
	}

	
	/**
	 * 取会员信息
	 * @param string $field 字段
	 * @param int $clubId 俱乐部id
	 * @param int $uid 会员id
	 * @return array 会员信息
	 * @access public
	 */
	public static function getInfo( $field, $clubId, $uid=UID ) {
		$DB = useDB();
		return $DB->getValue('SELECT '.$field.' FROM club_member WHERE clubId='.(int)$clubId.' AND uid='.(int)$uid.' AND status=1');
	}


	/**
	 * 读取当前会员的俱乐部权限
	 * @param int $clubId 俱乐部id
	 * @return void
	 * @access public
	 */
	public static function getPurview( $clubId ) {
		$DB = useDB();

		return ClubMember::getInfo('manage,purview', $clubId);
	}

	public static function getTuiguangview( $clubId ) {
		$DB = useDB();

		return ClubMember::getInfo('tuiguang,tuiguangview', $clubId);
	}


	/**
	 * 判断会员是否为俱乐部创建者
	 * @param int $clubId 俱乐部id
	 * @return void
	 * @access public
	 */
	public static function isCreator( $clubId, $ret=false ) {
		$DB = useDB();
		
		$info = ClubMember::getPurview($clubId);
		if( (int)$info['manage'] != CLUB_MANAGE_CREATOR ) {
			if($ret)return false;
			CMD(209);
		}
		return true;
	}


	/**
	 * 判断会员是否为俱乐部管理者
	 * @param int $clubId 俱乐部id
	 * @return void
	 * @access public
	 */
	public static function isManage( $clubId, $ret=false ) {
		$DB = useDB();
		
		$info = ClubMember::getPurview($clubId);
		if( !(int)$info['manage'] ) {
			if($ret)return false;
			CMD(209);
		}
		return true;
	}


	/**
	 * 判断会员的俱乐部权限
	 * @param int $flag 权限标识 例：编辑俱乐部的标识为5
	 * @param int $clubId 俱乐部id
	 * @return void
	 * @access public
	 */
	public static function isPurview( $flag, $clubId, $ret=false ) {
		$DB = useDB();
		
		$info = ClubMember::getPurview($clubId);
		
		//非管理
		if( !(int)$info['manage'] ) {
			if($ret)return false;
			CMD(209);

		//副代理
		}elseif( (int)$info['manage'] == CLUB_MANAGE_SUBAGENT ) {
			$purview = json_decode($info['purview']);
			if( !in_array($flag, $purview) ) {
				if($ret)return false;
				CMD(209);
			}
		}
		return true;
	}


	/**
	 * 统计俱乐部副代理数量
	 * @param int $clubId 俱乐部id
	 * @return int 会员数量
	 * @access public
	 */
	public static function getSubagentCount($clubId) {
		
		if(!$clubId)return;

		$DB = useDB();
		
		return $DB->getCount('club_member', 'clubId='.$clubId.' AND status=1 AND manage='.CLUB_MANAGE_SUBAGENT);
	}


	/**
	 * 统计俱乐部会员数量
	 * @param int $clubId 俱乐部id
	 * @return int 会员数量
	 * @access public
	 */
	public static function getMemberCount($clubId) {
		
		if(!$clubId)return;

		$DB = useDB();
		
		return $DB->getCount('club_member', 'clubId='.$clubId.' AND status=1');
	}


	/**
	 * 读取俱乐部管理uid
	 * @param int $clubId 俱乐部id
	 * @return array 管理uid
	 * @access public
	 */
	public static function getUID($clubId) {
		
		if(!$clubId)return;

		$DB = useDB();

		$uidArr = array();

		$list = $DB->getList('SELECT id,uid FROM club_member WHERE clubId='.$clubId.' AND status=1 AND manage>0');
		foreach($list as $item) {
			$uidArr[] = $item['uid'];
		}
		
		return $uidArr;
	}


	public static function getCut($uid,$clubId){
		if(!$uid || !$clubId)return;
		$DB = useDB();
		return ClubMember::getInfo('cut', $clubId, $uid);
	}

	public static function getCutNum($uid,$clubId){
		if(!$uid || !$clubId){
			return;
		}
		$DB = useDB();
		return $DB->getValue('SELECT cutNum FROM club_member WHERE clubId='.(int)$clubId.' AND uid='.(int)$uid.' AND status=1');
		//return ClubMember::getInfo('cutNum', $clubId, $uid);
	}

	//修改玩家总抽成
	// public static function editCutNum($cutNum,$inviteUid,$clubId) {

	// 	if(!$clubId || !$cutNum || !$inviteUid){
	// 		return;
	// 	}

	// 	$DB = useDB();
	// 	//更新状态
	// 	return $DB->update('club_member', array('cutNum'=>$cutNum), 'uid='.$inviteUid.' AND clubId='.$clubId);
	// }

	public static function cutNum( $clubId, $uid, $num, $add=true) {		
		
		$DB = useDB();
		
		if($add){
			$sql='cutNum+'.$num;
		}else{
			$sql='cutNum-'.$num;
		}
		
		$rs = $DB->exeSql('UPDATE club_member SET cutNum='.$sql.' WHERE clubId='.$clubId.' AND uid='.$uid.' AND status=1');
		if(!$rs) {
			return false;
		}
		
		$nowNum = (int)ClubMember::getInfo('cutNum', $clubId, $uid);
		
		return $nowNum;
	}


	//通过玩家uid获取推广员uid
	public static function getInviteUid($uid,$clubId){
		if(!$uid || !$clubId)return;
		
		$DB = useDB();
		return $DB->getValue('SELECT inviteUid FROM club_member WHERE clubId='.(int)$clubId.' AND uid='.(int)$uid.' AND status=1');
	}

	//获取俱乐部所有推广员列表
	public static function getAllTuiguang($clubId){
		if(!$clubId){
			return;
		}
		$DB = useDB();
		$list = $DB->getList('SELECT * FROM club_member WHERE clubId='.$clubId.' AND status=1 AND tuiguang=1 AND manage<2');
		return $list;
	}

	//获取推广员下级列表
	public static function getMytuiguang($uid,$clubId){
		if(!$clubId)return;
		$DB = useDB();
		$list = $DB->getList('SELECT * FROM club_member WHERE inviteUid='.$uid.' AND clubId='.$clubId);
		return $list;
	}

}