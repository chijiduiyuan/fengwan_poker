<?php
/**
 * 推广模块
 *
 * @author HJH
 * @version  2017-6-10
 */

if(!$_P){exit;}

switch ($_a){
    //所有推广员
    case 'list':
        $clubId = (int)$_P['clubId'];       //俱乐部id
        if(!$clubId){
            CMD(202);
        }
        $list = ClubMember::getAllTuiguang($clubId);
        foreach($list as &$item) {
			$userInfo = User::get('nickname,avatar', 'uid='.$item['uid']);
			$item += $userInfo;
		}
        CMD(200,$list);
        break;
    //推广员下级列表
    case 'tuiguanglist':
        $clubId = (int)$_P['clubId'];       //俱乐部id
        $uid    = (int)$_P['uid'];          //推广员uid
        if(!$clubId || !$uid){
            CMD(202);
        }
        $cut = ClubMember::getCut($uid,$clubId);            //抽成率
        $cutNum = ClubMember::getCutNum($uid,$clubId);      //总抽成
        $DB = useDB();
		$list = $DB->getList('SELECT * FROM club_member WHERE inviteUid='.$uid.' AND clubId='.$clubId);
        foreach($list as &$item) {
			$userInfo = User::get('nickname,avatar', 'uid='.$item['uid']);
			$item += $userInfo;
		}
        CMD(200,array('cut'=>$cut,'cutNum'=>$cutNum,'list'=>$list));
        break;
    case 'tuiguangInfo':
        $clubId = (int)$_P['clubId'];           //俱乐部id
        $uid = (int)$_P['uid'];                 //下级uid
        $inviteUid = (int)$_P['inviteUid'];     //推广员uid
        $list = ClubCounter::getMemberInfo($clubId,$inviteUid,$uid);
        foreach($list as &$item) {
			$userInfo = User::get('nickname,avatar', 'uid='.$item['uid_target']);
			$item += $userInfo;
		}
        CMD(200,$list);
        break;
    //管理员回收推广员抽成
    case 'tixian':
        $clubId = (int)$_P['clubId'];           //俱乐部uid
        $uid = (int)$_P['uid'];                 //推广员uid
        $actUid = (int)$_P['actUid'];           //操作人uid
        $num = (int)$_P['num'];               //提现数量
        //推广员扣除抽成 俱乐部币增加
        //俱乐部总币增加
        Club::rb($clubId,$num);
        //推广员减推广费
        $DB = useDB();
        $oldcutNum = ClubMember::getCutNum($uid,$clubId);
        $sql = 'cutNum-'.$num;
		$DB->exeSql('UPDATE club_member SET cutNum='.$sql.' WHERE clubId='.$clubId.' AND uid='.$uid.' AND status=1');
        //提现记录
        ClubCounter::add($clubId, $uid, $num, 0, 3);
        $nowcutNum = ClubMember::getCutNum($uid,$clubId);
        CMD(200,array('cutNum'=>$nowcutNum));
        break;
    
    //抽成进账回收记录
    case 'cutInfo':
        $clubId = (int)$_P['clubId'];
        $inviteUid = (int)$_P['inviteUid'];      //推广员uid
        if(!$clubId || !$inviteUid){
            CMD(202);
        }
        //推广员抽成进账明细
        $Inlist = ClubCounter::getInList($clubId,$inviteUid);
        //转换昵称
		foreach($Inlist as &$item) {
			$item['operator'] = User::getc('nickname', $item['uid_operator']);
			$item['nickname'] = User::getc('nickname', $item['uid_target']);

			unset($item['uid_operator']);
			unset($item['uid_target']);
		}
        //抽成被回收的列表
        $Outlist = ClubCounter::getOutList($clubId,$inviteUid);
        foreach($Outlist as &$vv) {
			$vv['operator'] = User::getc('nickname', $vv['uid_operator']);
			$vv['nickname'] = User::getc('nickname', $vv['uid_target']);

			unset($vv['uid_operator']);
			unset($vv['uid_target']);
		}
        CMD(200,array('Inlist'=>$Inlist,'Outlist'=>$Outlist));
        break;
    //管理员查看回收抽成记录
    case 'manageCutInfo':
        $clubId = (int)$_P['clubId'];
        $manageUid = (int)$_P['manageUid'];
        if(!$clubId || !$manageUid){
            CMD(202);
        }
        $list = ClubCounter::getList('clubId='.$clubId.' AND type=3 ORDER BY id DESC');
        foreach($list as &$vv) {
			$vv['operator'] = User::getc('nickname', $vv['uid_operator']);
			$vv['nickname'] = User::getc('nickname', $vv['uid_target']);

			unset($vv['uid_operator']);
			unset($vv['uid_target']);
		};
        CMD(200,$list);
}