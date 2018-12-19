<?php
/**
 * 俱乐部推广员抽成记录操作类
 *
 * @author HJH
 * @version  2017-6-10
 */


class  ClubInviteCut{
    /**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
    }
    
    //添加记录
    public static function add($clubId, $uid, $actUid,$num,$status,$roomId=0) {
		if(!$clubId || !$uid || !$actUid || !num || !$status){
			return;
		}
		//加入到俱乐部抽成记录
		$memAry = array(
			'uid'   =>$uid,
			'actUid'=>$actUid,
			'roomId'=>$roomId,
			'clubId'=> $clubId,
			'cutInfo'=>$num,
            'status'=>$status,
			'time'	=> time()
		);
		$DB = useDB();
		$id = $DB->insert('club_invite_cut', $memAry);

		return $id;

	}

	//读取俱乐部推广员抽成回收列表
	public static function getOutCutList($clubId,$inviteUid){
		if(!clubId || !$inviteUid){
			return;
		}
		$DB = useDB();
		$list = $DB->getList('SELECT * FROM club_invite_cut WHERE clubId='.$clubId.' AND uid='.$inviteUid.' AND status=2');
		return $list;
	}

	//读取俱乐部推广员抽成进账列表
	// public static function getInCutList($clubId,$inviteUid){
	// 	if(!clubId || !$inviteUid){
	// 		return;
	// 	}
	// 	$DB = useDB();
	// 	$list = $DB->getList('SELECT * FROM club_invite_cut WHERE clubId='.$clubId.' AND actuid='.$inviteUid.' AND status=1');
	// 	return $list;
	// }
}