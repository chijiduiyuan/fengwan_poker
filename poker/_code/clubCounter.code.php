<?php
/**
 * 俱乐部柜台操作模块
 *
 * @author HJH
 * @version  2017-6-18
 */

if(!$_P){exit;}

switch ($_a) {
	

	//发放筹码
	case 'send':
		
		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id
		$coin    = (int)$_P['coin'];    //筹码

		//验证参数
		if( $clubId<=0 || $uid<=0 || $coin<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否有筹码发放/回收的权限
		ClubMember::isPurview(CLUB_PURVIEW_SEND, $clubId);

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		$cfg = getCFG('data');

		//服务费
		$scale = round($coin * $cfg['sendRbScale']);

		//俱乐部减R币
		$rb = Club::rb($clubId, ($coin+$scale), false);
		//会员加筹码
		$cm = ClubMember::coin($clubId, $uid, $coin);
		//生成收发记录
		$rc = ClubCounter::add($clubId, $uid, $coin, $scale, 1);

		if($rb!==false && $cm!==false && (int)$rc) {
			$DB->commit();    //提交

			//加入到消息队列
			if($uid!=UID) {
				$uidAry = array($uid);
				$data = array('clubId'=>$clubId,'coin'=>$cm);
				Fun::addNotify($uidAry, $data);
			}

			CMD(200, array('rb'=>$rb));
			
		}else{
			$DB->rollBack();  //回滚
			CMD(214);
		}
		
		break;



	//回收筹码
	case 'recycle':
		
		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$uid     = (int)$_P['uid'];     //会员id
		$coin    = (int)$_P['coin'];    //筹码

		//验证参数
		if( $clubId<=0 || $uid<=0 || $coin<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否有筹码发放/回收的权限
		ClubMember::isPurview(CLUB_PURVIEW_SEND, $clubId);

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		$cfg = getCFG('data');

		//服务费
		$scale = round($coin * $cfg['recycleRbScale']);

		//会员减筹码
		$cm = ClubMember::coin($clubId, $uid, $coin, false);
		//俱乐部加R币
		$rb = Club::rb($clubId, ($coin-$scale));
		//生成收发记录
		$rc = ClubCounter::add($clubId, $uid, $coin, $scale, 2);
		
		if( $cm!==false && $rb!==false && (int)$rc) {
			$DB->commit();    //提交

			//加入到消息队列
			if($uid!=UID) {
				$uidAry = array($uid);
				$data = array('clubId'=>$clubId,'coin'=>$cm);
				Fun::addNotify($uidAry, $data);
			}
			
			CMD(200, array('rb'=>$rb));
			
		}else{
			$DB->rollBack();  //回滚
			CMD(214);
		}
		
		break;



	//收发记录
	case 'coinRecord':
		
		//俱乐部id
		$clubId  = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//分页参数
		$id       = (int)$_P['id'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND id<'.$id;
		}

		//判断当前会员在该俱乐部的权限(普通会员只能查看自己的收发记录)
		$uidWhere = '';
		$info = ClubMember::getPurview($clubId);
		if( !(int)$info['manage'] ) {
			$uidWhere = ' AND uid_target='.UID;
		}

		$list = ClubCounter::getList('clubId='.$clubId.$uidWhere.$pageWhere.' ORDER BY id DESC LIMIT '.$pageSize);

		//转换昵称
		foreach($list as &$item) {
			$item['operator'] = User::getc('nickname', $item['uid_operator']);
			$item['nickname'] = User::getc('nickname', $item['uid_target']);

			unset($item['uid_operator']);
			unset($item['uid_target']);
		}

		CMD(200, $list);
		
		
		break;
	

}