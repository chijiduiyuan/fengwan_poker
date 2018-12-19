<?php
/**
 * 俱乐部操作类
 *
 * @author HJH
 * @version  2017-6-10
 */


class  Club{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	

	/**
	 * 创建俱乐部
	 * @param int $cid 国家id
	 * @param string $title 俱乐部名称
	 * @param string $intro 俱乐部公告
	 * @return int 俱乐部id
	 * @access public
	 */
	public static function create($clubInfo) {

		$cfg = getCFG('data');

		$t = time();  //当前时间

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		//生成俱乐部数据
		$clubAry = array(
			'create_uid'	 => $clubInfo['create_uid'],
			'create_nickname'=> $clubInfo['create_nickname'],
			'cid'   		 => $clubInfo['cid'],
			'rb'   			 => (int)$cfg['clubInitRb'],
			'title' 		 => $clubInfo['title'],
			'intro' 		 => $clubInfo['intro'],
			'time'  		 => $t
		);

		//俱乐部起始id
		$autoId = $DB->getAutoIncrement('club');
		if($autoId < $cfg['clubAutoIncrement']) {
			$clubAry['clubId'] = $cfg['clubAutoIncrement'];
		}

		$clubId = $DB->insert('club', $clubAry);

		//加入到俱乐部会员
		$memAry = array(
			'clubId'=> $clubId,
			'uid'	=> UID,
			'manage'=> CLUB_MANAGE_CREATOR,
			'tuiguang'=>1,
			'inviteUid'=>UID,
			'time'	=> $t
		);
		$id = $DB->insert('club_member', $memAry);

		if($clubId && $id) {
			$DB->commit();    //提交
			return $clubId;
		}else{
			$DB->rollBack();  //回滚
			return false;
		}

	}

	//读取俱乐部推广员信息
	public static function getTgList($clubId,$inviteUid){
		if(!clubId){
			return;
		}
		$DB = useDB();
		$info = $DB->getValue('SELECT * FROM club_member WHERE clubId='.$clubId.' AND status=1 AND uid='.$inviteUid);
		return $info;
	}


	/**
	 * 读取俱乐部基础信息
	 * @param int $clubId 俱乐部id
	 * @param string $field 字段
	 * @param bool $ttod 是否转换时间
	 * @return array 俱乐部信息
	 * @access public
	 */
	public static function getInfo( $clubId, $field='*', $ttod=true, $statusCheck=true ) {

		if(!$clubId)return;
		
		if( strpos($field, 'level')!==false || strpos($field, 'subagentLimit')!==false || strpos($field, 'memberLimit')!==false || strpos($field, 'luckyFlag')!==false ) {
			if( strpos($field, 'expir')===false ) {
				$field .= ',expir';
			}
		}

		$swhere = '';
		if($statusCheck) {
			$swhere = ' AND status=1';
		}

		$DB = useDB();
		$info = $DB->getValue('SELECT '.$field.' FROM club WHERE clubId='.$clubId.$swhere);

		if( is_array($info) && array_key_exists('expir',$info) && (int)$info['expir']<=time()) {

			$cfg = getCFG('data');

			if(array_key_exists('level',$info)) {
				$info['level'] = 0;
			}

			if(array_key_exists('subagentLimit',$info)) {
				$info['subagentLimit'] = $cfg['clubSubagentLimit'];
			}
			
			if(array_key_exists('memberLimit',$info)) {
				$info['memberLimit']   = $cfg['clubMemberLimit'];
			}

			if(array_key_exists('luckyFlag',$info)) {
				$info['luckyFlag'] = 0;
			}
		}
		
		//转换过期时间
		if($ttod && is_array($info)) {
			Fun::ttod($info);
		}

		return $info;
	}


	/**
	 * 读取俱乐部创建者昵称
	 * @param int $clubId 俱乐部id
	 * @return string 创建者昵称
	 * @access public
	 */
	public static function getCreator( $clubId ) {

		if(!$clubId)return;
		
		$DB = useDB();

		$uid = (int)$DB->getValue('SELECT uid FROM club_member WHERE clubId='.$clubId.' AND status=1 AND manage='.CLUB_MANAGE_CREATOR);
		if(!$uid)return;
		
		return User::get('nickname', 'uid='.$uid);
	}


	/**
	 * 修改俱乐部资料
	 * @param array $param 修改的参数数组
	 * @param int $clubId 修改的俱乐部id
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function edit( $param, $clubId ) {
		$DB = useDB();		
	
		return $DB->update('club', $param, 'clubId='.$clubId);
	}


	/**
	 * 设置俱乐部状态为解散
	 * @param int $clubId 俱乐部id
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function delete( $clubId ) {

		if(!$clubId)return;

		//判断会员是否为俱乐部创建者(创建者才可解散俱乐部)
		ClubMember::isCreator($clubId);

		//更新状态
		return Club::edit(array('status'=>0), $clubId);
	}


	/**
	 * 增加俱乐部rb
	 * @param integer $clubId 俱乐部id
	 * @param integer $num 要增加的数量
	 * @param bool $add true增加,false减少
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function rb( $clubId, $num, $add=true ) {		
		
		$DB = useDB();	
		
		if($add){
			$sql='rb+'.$num;

		}else{
			$oldNum = (int)Club::getInfo($clubId, 'rb');
			if($oldNum<$num){
				return false;
			}
			$sql='rb-'.$num;
		}
		
		$rs = $DB->exeSql('UPDATE club SET rb='.$sql.' WHERE clubId='.$clubId);
		if(!$rs) {
			return false;
		}
		
		$nowNum = (int)Club::getInfo($clubId, 'rb');
		
		return $nowNum;	
	}


	/**
	 * 取当前玩家的俱乐部列表(含创建与加入的)
	 * @param int $manage 管理标识
	 * @param int $id 分页id
	 * @param int $pageSize 分页条数
	 * @return array 俱乐部列表
	 * @access public
	 */
	public static function getList($manage, $id, $pageSize) {
		
		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND ((cm.manage='.$manage.' AND cm.id<'.$id.') OR cm.manage<'.$manage.')';
		}

		$where = 'cm.uid='.UID.' AND cm.status=1'.$pageWhere.' AND c.status=1 ORDER BY cm.manage DESC, cm.id DESC LIMIT '.$pageSize;

		$DB = useDB();
		$field = 'cm.id,cm.clubId,cm.coin,cm.manage,cm.tuiguang,c.title,c.avatar,c.level,c.expir';
		$list = $DB->getList('SELECT '.$field.' FROM club_member cm LEFT JOIN club c ON cm.clubId=c.clubId WHERE '.$where);
		return $list;
	}


	/**
	 * 取当前玩家的俱乐部列表(只包含有管理权限的，不包含普通会员的，web代理端使用)
	 * @param int $uid 玩家uid
	 * @param int $id 分页id
	 * @param int $pageSize 分页条数
	 * @return array 俱乐部列表
	 * @access public
	 */
	public static function agentGetList($curPage, $pageSize, $uid=UID) {

		$limitForm = ($curPage-1)*$pageSize;
		
		$where = 'cm.uid='.$uid.' AND cm.status=1 AND manage>0 AND c.status=1 ORDER BY cm.manage DESC, cm.id DESC LIMIT '.$limitForm.','.$pageSize;

		$DB = useDB();
		$field = 'cm.id,cm.clubId,cm.manage,c.title,c.avatar,c.rb,c.level,c.expir,c.intro,c.create_nickname,c.time';
		$list = $DB->getList('SELECT '.$field.' FROM club_member cm LEFT JOIN club c ON cm.clubId=c.clubId WHERE '.$where);
		return $list;
	}


	/**
	 * 取俱乐部详情
	 * @param int $clubId 俱乐部id
	 * @return array 俱乐部详情
	 * @access public
	 */
	public static function getDetail($clubId) {

		if(!$clubId)return;

		//读取当前玩家的俱乐部权限
		$purview = ClubMember::getPurview($clubId);
		if(!$purview)return;

		$tuiguangview = ClubMember::getTuiguangView($clubId);
		//读取俱乐部基础信息
		$clubInfo = Club::getInfo($clubId, 'clubId,title,avatar,rb,level,expir,memberLimit,luckyFlag,intro,checkip');
		if(!$clubInfo)return;

		//非管理无权查看俱乐部币
		if( !(int)$purview['manage'] ) {
			$clubInfo['rb'] = 0;
		}

		$clubInfo['coin'] = (int)ClubMember::getInfo('coin', $clubId);

		//读取俱乐部现有成员数
		$clubInfo['memberCount'] = ClubMember::getMemberCount($clubId);
		
		$detail = $clubInfo + $purview + $tuiguangview;

		//读取创建者信息
		$detail['creator'] = Club::getCreator($clubId);

		//跟据国家读取兑换率
		$cid = (int)Club::getInfo($clubId, 'cid', false);
		if($cid<=0) {
			CMD(231);
		}
		//读取国家配置
		$country = Country::getValue('rmbToclubRb,clubRbLeast','cid='.$cid);
		if($country) {
			$detail['rmbToclubRb'] = $country['rmbToclubRb']; //每钻石可兑换的俱乐部币数量
			$detail['clubRbLeast'] = $country['clubRbLeast']; //俱乐部币兑换回钻石最少起兑数
		}else{
			$detail['rmbToclubRb'] = 0;
			$detail['clubRbLeast'] = 0;
		}
		

		if( (int)$purview['manage'] > 0 ) {

			//总开局数
			$detail['startTotal'] = ClubRoom::getRoomCount('clubId='.$clubId.' AND status=1');

			//服务费总计
			$detail['serviceChargeTotal'] = ClubRoom::getSum( 'scale', 'clubId='.$clubId);

			//总手牌数
			$detail['handTotal'] = ClubRoom::getHandCount($clubId, 'clubId='.$clubId);

			//申请通知数
			$detail['applyNum'] = (int)ClubApply::getInfo('count(applyId)', 'clubId='.$clubId.' AND status='.CLUB_MEMBER_ING);
		}
		
		return $detail;
	}


	/**
	 * 统计当前玩家创建的俱乐部数量
	 * @return int 统计值
	 * @access public
	 */
	public static function getTotal() {
		$DB = useDB();
		return (int)$DB->getValue('SELECT COUNT(cm.id) FROM club_member as cm LEFT JOIN club ON cm.clubId=club.clubId WHERE cm.uid='.UID.' AND cm.status=1 AND cm.manage='.CLUB_MANAGE_CREATOR.' AND club.status=1');
	}


	/**
	 * 读取俱乐部等级权限
	 * @param int $clubId 俱乐部id
	 * @return array 俱乐部等级权限信息
	 * @access public
	 */
	public static function getLevelInfo($clubId) {
		$DB = useDB();
		return Club::getInfo($clubId, 'level,subagentLimit,memberLimit,luckyFlag,expir');
	}

}