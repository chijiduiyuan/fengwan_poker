<?php
/**
 * 玩家操作模块
 *
 * @author HJH
 * @version  2017-6-6
 */

if(!$_P){exit;}

switch ($_a) {
	

	//修改玩家信息
	case 'edit':
		
		$nickname = trim($_P['nickname']);
		$avatar   = trim($_P['avatar']);

		//验证参数
		if(!$nickname && !$avatar) {
			CMD(202);
		}

		$ary = array();

		if($nickname) {

			//判断昵称是否已存在
			// $uid = (int)User::get('uid', 'nickname=\''.$nickname.'\'');
			// if($uid) {
			// 	CMD(211);
			// }

			$ary['nickname'] = $nickname;
		}

		if($avatar) {
			$ary['avatar'] = $avatar;
		}


		if($ary) {
			$rs = User::edit($ary);
			if($rs && $ary['nickname']){
				//如果修改了昵称,则更新俱乐部创建者昵称
				$DB = useDB();
				$DB->update('club', [
						'create_nickname' => $ary['nickname']
					], 'create_uid='.UID);

			}
		}

		break;


	//保存玩家语音
	case 'audio':
		
		$audio = trim($_P['audio']);

		//验证参数
		if(!$audio) {
			CMD(202);
		}

		User::edit(array('audio'=>$audio));

		//加入到消息队列
		$uidAry = ClubRoom::getUID(ClubRoom::getRoomId());
		$data = array('uid'=>UID, 'audio'=>$audio);
		Fun::addNotify($uidAry, $data);

		break;


	//保存玩家经纬度
	case 'pos':
		
		$x = $_P['x'];
		$y = $_P['y'];

		$DB = useDB();
		$DB->exeSql('update user set pos=ST_GEOMFROMTEXT("POINT('.$x.' '.$y.')") where uid='.UID);

		break;


	//玩家详情
	case 'info':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if(!$clubId) {
			CMD(202);
		}

		//玩家id
		$uid = (int)$_P['uid'];
		if( $uid<=0 ) {
			CMD(202);
		}
		
		$info = User::get('uid,nickname,avatar,audio', 'uid='.$uid);

		if($clubId>0) {
			
			//总开局数
			$info['roomTotal'] = ClubRoom::getRoomCount('clubId='.$clubId.' AND status=1');

			//总手牌数
			$info['handTotal'] = ClubRoom::getSum( 'handNum', 'clubId='.$clubId);

			//入池率
			$dzHandNum = ClubRoom::getSum( 'handNum', 'clubId='.$clubId.' AND uid='.$uid.' AND gameType=1');
			$dzHandNumPool = ClubRoom::getSum( 'handNumPool', 'clubId='.$clubId.' AND uid='.$uid.' AND gameType=1');
			$info['enterPool'] = $dzHandNum>0 ? round($dzHandNumPool / $dzHandNum) : 0;
		}

		CMD(200, $info);

		break;


	//使用表情
	case 'emoji':

		//玩家id
		$uid = (int)$_P['uid'];
		if( $uid<=0 ) {
			CMD(202);
		}

		//表情标识
		$emoji = $_P['emoji'];
		if(!$emoji) {
			CMD(202);
		}

		//读取房间id
		$roomId = ClubRoom::getRoomId();
		// if( $roomId<=0 ) {
		// 	CMD(210);
		// }
		
		$info = User::getVipInfo();
		if( (int)$info['card_emoji_num']>0 ) {
			User::edit(array('card_emoji_num'=>((int)$info['card_emoji_num']-1)));
		}else{
			$cfg = getCFG('data');

			if($uid==UID) {
				$rmb = $cfg['cardEmojiRmbOwn'];
			}else{
				$rmb = $cfg['cardEmojiRmbOth'];
			}

			$rs = User::rmb($rmb, false);
			if(!$rs) {
				CMD(206);
			}

			$modifyInfo = [];
			$modifyInfo['rmb'] = $rs;
			Fun::addNotify( [UID], $modifyInfo,'user_info' );
		}

		//加入到消息队列
		$uidAry = ClubRoom::getUID($roomId);
		$data = array('fromUID'=>UID,'toUID'=>$uid, 'emoji'=>$emoji);
		Fun::addNotify($uidAry, $data);

		break;
	case 'send':
		//玩家id
		$uid = (int)$_P['uid'];
		if( $uid<=0 ) {
			CMD(202);
		}
		// 玩家是否存在
		$toUid = (int)User::get('uid','uid='.$uid);
		if ($toUid <= 0) {
			CMD(236);
		}

		//赠送金币
		$gold = (int)$_P['gold'];
		if($gold<=0) {
			CMD(202);
		}

		//赠送人金币是否足够
		$fromInfo = User::get('nickname,gold');

		if(!$fromInfo || (int)$fromInfo['gold'] < $gold ) {
			CMD(224);
		}
		// 开启事务
		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		$toUserGold = User::gold($gold,true,$uid);

		if($toUserGold===false) {
			$DB->rollBack();  //回滚
			CMD(210, '赠送金币失败');
		}

		$fromUserGold = User::gold($gold,false);
		if($fromUserGold===false || $fromUserGold < 0) {
			$DB->rollBack();  //回滚
			CMD(210, '赠送金币失败');
		}

		$title 		= '赠送金币通知';
		$content 	= $fromInfo['nickname'].'('.UID.')赠送您'.$gold.'金币';
		$rmb 		= 0;		

		$rs = Mail::send([
				'uid' 		=> $uid,
				'title' 	=> $title,
				'content' 	=> $content,				
				'create_uid'=> UID,
				'create_nickname' => $fromInfo['nickname'],
				'rmb' 		=> $rmb
			]);


		if(!$rs){
			$DB->rollBack();  //回滚
			CMD(210, '赠送金币失败');
		}

		$DB->commit();

		// 通知被赠送的用户更新金币

		$modifyInfo = [];

		if($toUserGold !==0 ){
			$modifyInfo['gold'] = $toUserGold;
			Fun::addNotify( [$uid], $modifyInfo,'user_info' );
		}
			

		CMD(200, array('gold'=>$fromUserGold));
	break;

	case 'sendRmb':
		//玩家id
		$uid = (int)$_P['uid'];
		if( $uid<=0 ) {
			CMD(202);
		}
		// 玩家是否存在
		$toUid = (int)User::get('uid','uid='.$uid);
		if ($toUid <= 0) {
			CMD(236);
		}

		//赠送钻石
		$rmb = (int)$_P['rmb'];
		if($rmb<=0) {
			CMD(202);
		}

		//赠送人钻石是否足够
		$fromInfo = User::get('nickname,rmb,sendRmb');

		if(!$fromInfo || (int)$fromInfo['rmb'] < $rmb ) {
			CMD(224);
		}
		if($fromInfo['sendRmb'] == 0){
			CMD(225);
		}
		// 开启事务
		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		$toUserRmb = User::sendrmb($rmb,true,$uid);

		if($toUserRmb===false) {
			$DB->rollBack();  //回滚
			CMD(210, '赠送钻石失败');
		}

		$fromUserRmb = User::sendrmb($rmb,false);
		if($fromUserRmb===false || $fromUserRmb < 0) {
			$DB->rollBack();  //回滚
			CMD(210, '赠送钻石失败');
		}

		$title 		= '赠送钻石通知';
		$content 	= $fromInfo['nickname'].'('.UID.')赠送您'.$rmb.'钻石';
		$rmb 		= 0;		

		$rs = Mail::send([
				'uid' 		=> $uid,
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

		// 通知被赠送的用户更新钻石

		$modifyInfo = [];

		if($toUserRmb !==0 ){
			$modifyInfo['gold'] = $toUserRmb;
			Fun::addNotify( [$uid], $modifyInfo,'user_info' );
		}
			

		CMD(200, array('rmb'=>$fromUserRmb));
	break;

	case 'selfInfo':
		$info = User::get('uid,nickname,avatar,gold,rmb');
		CMD(200, $info);
	break;

}