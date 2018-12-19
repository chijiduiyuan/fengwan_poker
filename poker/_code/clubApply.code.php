<?php
/**
 * 俱乐部申请列表/加入操作模块
 *
 * @author HJH
 * @version  2017-6-18
 */

if(!$_P){exit;}

switch ($_a) {
	

	//申请加入
	case 'join':
		
		$clubId = (int)$_P['clubId'];  //俱乐部id
		$inviteUid = (int)$_P['inviteUid'];	//邀请人uid
		//验证参数
		if( $clubId<=0  || !$inviteUid) {
			CMD(202);
		}

		//判断该俱乐部会员数是否超出上限
		$curMemberNum = (int)ClubMember::getMemberCount($clubId);       //当前俱乐部会员数
		$clubLevelInfo  = Club::getLevelInfo($clubId);  				//当前俱乐部可加入数上限
		if( $curMemberNum >= (int)$clubLevelInfo['memberLimit'] ) {
			CMD(220);
		}

		//判断是否已在俱乐部中
		$id = (int)ClubMember::getInfo('id', $clubId);
		if($id) {
			CMD(218);
		}

		$applyId = (int)ClubApply::getInfo('applyId', 'clubId='.$clubId.' AND uid='.UID.' AND status='.CLUB_MEMBER_ING);
		if(!$applyId) {
			ClubApply::add($clubId,$inviteUid);

			//加入到消息队列
			$uidAry = ClubMember::getUID($clubId);
			$data = array('clubId'=>$clubId);
			Fun::addNotify($uidAry, $data);
			
		}else{
			CMD(217);
		}
		
		break;



	//申请列表
	case 'list':

		$clubId = (int)$_P['clubId'];  //俱乐部id

		//验证参数
		if( $clubId<=0 ) {
			CMD(202);
		}

		//分页参数
		$applyId  = (int)$_P['applyId'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($applyId) {
			$pageWhere = ' AND applyId<'.$applyId;
		}

		//判断当前玩家是否有俱乐部申请同意/拒绝的权限
		ClubMember::isPurview(CLUB_PURVIEW_IN, $clubId);

		$list = ClubApply::getList('applyId,uid', 'clubId='.$clubId.' AND status='.CLUB_MEMBER_ING.$pageWhere.' ORDER BY applyId DESC LIMIT '.$pageSize);
		foreach($list as &$item) {

			$userInfo = User::get('nickname,avatar', 'uid='.$item['uid']);
			$item += $userInfo;
		}

		CMD(200, $list);

		break;



	//申请同意
	case 'agree':

		$applyId = (int)$_P['applyId'];  //申请id
		//验证参数
		if( $applyId<=0 ) {
			CMD(202);
		}

		//读取俱乐部id
		$applyInfo = ClubApply::getInfo('clubId,uid,inviteUid', 'applyId='.$applyId);

		//判断当前玩家是否有俱乐部申请同意/拒绝的权限
		ClubMember::isPurview(CLUB_PURVIEW_IN, $applyInfo['clubId']);

		//判断该俱乐部会员数是否超出上限
		$curMemberNum = (int)ClubMember::getMemberCount($applyInfo['clubId']);		//当前俱乐部会员数
		$clubLevelInfo  = Club::getLevelInfo($applyInfo['clubId']);					//当前俱乐部可加入数上限
		if( $curMemberNum >= (int)$clubLevelInfo['memberLimit'] ) {
			CMD(220);
		}

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		$rs1 = ClubApply::setStatus($applyId, CLUB_MEMBER_AGREE);
		$rs2 = ClubMember::add($applyInfo['clubId'], $applyInfo['uid'],$applyInfo['inviteUid']);
		if($rs1 && $rs2) {
			$DB->commit();    //提交

			//加入到消息队列
			$uidAry = array($applyInfo['uid']);
			$data = array('clubId'=>$applyInfo['clubId'],'title'=>Club::getInfo($applyInfo['clubId'],'title',false));
			Fun::addNotify($uidAry, $data);

		}else{
			$DB->rollBack();  //回滚
			CMD(210);
		}

		break;



	//申请拒绝
	case 'reject':

		$applyId = (int)$_P['applyId'];  //申请id

		//验证参数
		if( $applyId<=0 ) {
			CMD(202);
		}

		//读取俱乐部id
		$applyInfo = ClubApply::getInfo('clubId,uid', 'applyId='.$applyId);

		//判断当前玩家是否有俱乐部申请同意/拒绝的权限
		ClubMember::isPurview(CLUB_PURVIEW_IN, $applyInfo['clubId']);

		$rs = ClubApply::setStatus($applyId, CLUB_MEMBER_REJECT);
		if(!$rs) {
			CMD(210);
		}

		//加入到消息队列
		$uidAry = array($applyInfo['uid']);
		$data = array('clubId'=>$applyInfo['clubId'],'title'=>Club::getInfo($applyInfo['clubId'],'title',false));
		Fun::addNotify($uidAry, $data);

		break;
		
}