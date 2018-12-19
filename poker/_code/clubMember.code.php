<?php
/**
 * 俱乐部会员操作模块
 *
 * @author HJH
 * @version  2017-6-18
 */

if(!$_P){exit;}

switch ($_a) {
	


	//会员列表
	case 'list':

		//俱乐部id
		$clubId  = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否已在俱乐部中(非俱乐部成员不能查看俱乐部会员列表)
		if(!ClubMember::getPurview($clubId)) {
			CMD(209);
		}

		//分页参数
		$manage   = (int)$_P['manage'];
		$id 	  = (int)$_P['id'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND ((cm.manage='.$manage.' AND cm.id<'.$id.') OR cm.manage<'.$manage.')';
		}

		//会员查找关键字
		$keyword = $_P['keyword'];
		$keywordWhere = '';
		if($keyword) {
			$keywordWhere = ' AND (user.uid="'.$keyword.'" OR user.nickname LIKE "%'.$keyword.'%")';
		}

		$where = 'cm.clubId='.$clubId.' AND cm.status=1'.$pageWhere.$keywordWhere.' ORDER BY cm.manage DESC, cm.id DESC LIMIT '.$pageSize;

		//获取会员列表
		$DB = useDB();
		$list = $DB->getList('SELECT cm.id,cm.uid,cm.coin,cm.manage,cm.tuiguang,cm.inviteUid,user.nickname,user.avatar FROM club_member as cm LEFT JOIN user ON cm.uid=user.uid WHERE '.$where);
		foreach($list as &$item) {

			//服务费
			$item['serviceFee'] = ClubRoom::getSum('scale', 'clubId='.$clubId.' AND uid='.(int)$item['uid']);

			//盈亏
			$item['profit'] = ClubRoom::getSum('profitLoss', 'clubId='.$clubId.' AND uid='.(int)$item['uid']);
		}

		CMD(200, $list);
		
		break;


	//会员列表
	case 'simplelist':

		//俱乐部id
		$clubId  = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否已在俱乐部中(非俱乐部成员不能查看俱乐部会员列表)
		// if(!ClubMember::getPurview($clubId)) {
		// 	CMD(209);
		// }

		//分页参数
		// $manage   = (int)$_P['manage'];
		// $id 	  = (int)$_P['id'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;
		$page = (int)$_P['page'] > 0 ? (int)$_P['page'] : 1;
		$skip = ($page-1) * $pageSize;

		$pageWhere = '';
		// if($id) {
		// 	$pageWhere = ' AND ((cm.manage='.$manage.' AND cm.id<'.$id.') OR cm.manage<'.$manage.')';
		// }

		//会员查找关键字
		$keyword = $_P['keyword'];
		$keywordWhere = '';
		if($keyword) {
			$keywordWhere = ' AND (user.uid="'.$keyword.'" OR user.nickname LIKE "%'.$keyword.'%")';
		}

		$where = 'cm.clubId='.$clubId.' AND cm.status=1'.$pageWhere.$keywordWhere;

		$order =' ORDER BY cm.manage DESC, cm.id DESC LIMIT '.$skip.','.$pageSize;

		//获取会员列表
		$DB = useDB();
		$list = $DB->getList('SELECT cm.id,cm.uid,cm.coin,cm.manage,cm.tuiguang,cm.inviteUid,user.rmb,user.nickname,user.avatar FROM club_member as cm LEFT JOIN user ON cm.uid=user.uid WHERE '.$where.$order);

		$total = $DB->getCount('club_member as cm LEFT JOIN user ON cm.uid=user.uid',$where);
		// foreach($list as &$item) {

		// 	//服务费
		// 	$item['serviceFee'] = ClubRoom::getSum('scale', 'clubId='.$clubId.' AND uid='.(int)$item['uid']);

		// 	//盈亏
		// 	$item['profit'] = ClubRoom::getSum('profitLoss', 'clubId='.$clubId.' AND uid='.(int)$item['uid']);
		// }

		CMD(200, array(
			 'list' => $list,
			 'total' => $total
			 )
	       );
		
		break;
	
	case 'sendRmb':

		$toUid = (int)$_P['toUid'];
		$rmbNum = (int)$_P['rmb'];     //要赠送rmb数量

		if (UID == $toUid) {
			CMD(202,"不能赠送钻石给自己");
		}

		// rmb数量校验
		if($rmbNum<=0 ) {
			CMD(202,"赠送钻石应大于0");
		}

		//赠送人钻石是否足够
		$fromInfo = User::get('nickname,rmb');

		// 发送者 rmb校验
		if(!$fromInfo || (int)$fromInfo['rmb'] < $rmbNum ) {
			CMD(206,"赠送钻石不足");
		}

		// 送出人存在
		if ($toUid <= 0) {
			CMD(236, "目标不存在");
		}

		$toUid = (int)User::get('uid','uid='.$toUid);
		if ($toUid <= 0) {
			CMD(236, "目标不存在");
		}

		// 事务操作
		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		$fromRmb = User::rmb($rmbNum, false);

		if($fromRmb===false) {
			$DB->rollBack();  //回滚
			CMD(210, "赠送钻石失败");
		}
		
		$toRmb = User::rmb($rmbNum, true, $toUid);
		if($toRmb===false) {		
			$DB->rollBack();  //回滚
			CMD(210, "赠送钻石失败");
		}
		
		$title 		= '赠送钻石通知';
		$content 	= $fromInfo['nickname'].'('.UID.')赠送您'.$rmbNum.'钻石';
		$rmb 		= 0;		

		$rs = Mail::send([
				'uid' 		=> $toUid,
				'title' 	=> $title,
				'content' 	=> $content,				
				'create_uid'=> UID,
				'create_nickname' => $fromInfo['nickname'],
				'rmb' 		=> $rmb
			]);


		if(!$rs){
			$DB->rollBack();  //回滚
			CMD(210, '赠送钻石失败');
		}

		$DB->commit();

		// 通知赠送的用户更新钻石

		$fromInfoUpdate = [];		
		$fromInfoUpdate['rmb'] = $fromRmb;
		Fun::addNotify( [UID], $fromInfoUpdate,'user_info' );
		
		// 通知被赠送的用户更新钻石
		$toInfoUpdate = [];		
		$toInfoUpdate['rmb'] = $toRmb;
		Fun::addNotify( [$toUid], $toInfoUpdate,'user_info' );			

		CMD(200, array('rmb'=>$fromRmb));		

	    break;

	//会员详情
	case 'info':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id

		//验证参数
		if( $clubId<=0 || $uid<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否已在俱乐部中(非俱乐部成员不能查看俱乐部会员详情)
		$purview = ClubMember::getPurview($clubId);
		if(!$purview) {
			CMD(209);
		}
		
		//获取会员信息
		$info = ClubMember::getInfo('uid,note,manage,tuiguang,inviteUid', $clubId, $uid);
		if(!$info) {
			CMD(210);
		}
		
		//读取玩家信息
		$user = User::get('nickname,avatar', 'uid='.$info['uid']);
		$info += $user;

		//服务费
		$info['serviceFee'] = ClubRoom::getSum('scale', 'clubId='.$clubId.' AND uid='.$uid);

		//手牌数
		$info['handNum'] = ClubRoom::getSum('handNum', 'clubId='.$clubId.' AND uid='.$uid);

		//盈亏
		$info['profit'] = ClubRoom::getSum('profitLoss', 'clubId='.$clubId.' AND uid='.$uid);

		//买入
		$info['buyIn'] = ClubRoom::getSum('totalBuyBet', 'clubId='.$clubId.' AND uid='.$uid);
		
		//退码数
		$info['returnClips'] = ClubCounter::getSum('coin', 'clubId='.$clubId.' AND uid_target='.$uid.' AND type=2');

		CMD(200, $info);
		
		break;



	//会员删除
	case 'delete':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id

		//验证参数
		if( $clubId<=0 || $uid<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否有俱乐部会员批准进入/踢出的权限
		ClubMember::isPurview(CLUB_PURVIEW_IN, $clubId);

		//踢出会员
		ClubMember::delete($clubId, $uid);

		//加入到消息队列
		$uidAry = array($uid);
		$data = array('clubId'=>$clubId,'title'=>Club::getInfo($clubId,'title',false));
		Fun::addNotify($uidAry, $data);
		
		break;



	//设置副代理
	case 'setSubagent':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id

		$purview = $_P['purview'];      //权限 [1,2,3,4,5]

		//验证参数
		if( $clubId<=0 || $uid<=0 || !$purview ) {
			CMD(202);
		}

		//判断当前玩家是否为俱乐部创建者(创建者才可设置副代理)
		ClubMember::isCreator($clubId);

		//判断该俱乐部副代理数是否超出上限
		$curSubagentNum = (int)ClubMember::getSubagentCount($clubId);   //当前俱乐部副代理数
		$clubLevelInfo  = Club::getLevelInfo($clubId);  				//当前俱乐部可设置数上限
		if( $curSubagentNum >= (int)$clubLevelInfo['subagentLimit'] ) {
			CMD(219);
		}
		
		//更新会员权限为副代理
		ClubMember::edit(array('manage'=>CLUB_MANAGE_SUBAGENT,'purview'=>$purview), $clubId, $uid, 'manage<>'.CLUB_MANAGE_CREATOR);

		//加入到消息队列
		$uidAry = array($uid);
		$data = array('clubId'=>$clubId);
		Fun::addNotify($uidAry, $data);
		
		break;



	//取消副代理
	case 'unsetSubagent':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id

		//验证参数
		if( $clubId<=0 || $uid<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否为俱乐部创建者(创建者才可取消副代理)
		ClubMember::isCreator($clubId);

		//更新会员权限为普通会员
		ClubMember::edit(array('manage'=>0,'purview'=>'[]'), $clubId, $uid, 'manage<>'.CLUB_MANAGE_CREATOR);

		//加入到消息队列
		$uidAry = array($uid);
		$data = array('clubId'=>$clubId);
		Fun::addNotify($uidAry, $data);
		
		break;

		//设置推广员
	case 'setTuiguang':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id

		$tuiguangview = $_P['tuiguangview'];      //权限 [6]
		$cut = (int)$_P['cut'];						//推广员抽成比例

		if( $cut<=0 ) {
			CMD(202);
		}else{
			$cut = $cut/100;
		}

		//验证参数
		if( $clubId<=0 || $uid<=0 || !$tuiguangview || !$cut) {
			CMD(202);
		}

		//判断当前玩家是否为俱乐部创建者(创建者才可设置推广员)
		ClubMember::isCreator($clubId);

		
		//更新会员权限为推广员
		ClubMember::edit(array('tuiguang'=>1,'tuiguangview'=>$tuiguangview,'cut'=>$cut), $clubId, $uid,'manage<>'.CLUB_MANAGE_CREATOR);

		//加入到消息队列
		$uidAry = array($uid);
		$data = array('clubId'=>$clubId);
		Fun::addNotify($uidAry, $data);
		
		break;



	//取消推广员
	case 'unsetTuiguang':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id

		//验证参数
		if( $clubId<=0 || $uid<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否为俱乐部创建者(创建者才可取消推广员)
		ClubMember::isCreator($clubId);

		//更新会员权限为普通会员
		ClubMember::edit(array('tuiguang'=>0,'tuiguangview'=>'[]'), $clubId, $uid, 'manage<>'.CLUB_MANAGE_CREATOR);

		//加入到消息队列
		$uidAry = array($uid);
		$data = array('clubId'=>$clubId);
		Fun::addNotify($uidAry, $data);
		
		break;



	//会员备注
	case 'note':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id

		$note    = $_P['note'];         //备注

		//验证参数
		if( $clubId<=0 || $uid<=0 || !$note ) {
			CMD(202);
		}

		//判断当前玩家是否为俱乐部管理者(管理者才可备注)
		ClubMember::isManage($clubId);

		//备注会员资料
		ClubMember::note($clubId, $uid, $note);
		
		break;



	//退出俱乐部
	case 'quit':

		$clubId  = (int)$_P['clubId'];  //俱乐部id

		//验证参数
		if( $clubId<=0 ) {
			CMD(202);
		}

		//踢出会员
		ClubMember::delete($clubId, UID);
		
		break;



}