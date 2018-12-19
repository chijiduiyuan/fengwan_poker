<?php
/**
 * node操作模块
 *
 * @author HJH
 * @version  2017-6-22
 */

if(!$_P){exit;}



//验证模块
$modAry = array(
	'getRoom',   		//取房间信息
	'startGame',   		//房间开局
	'play',   			//开始游戏
	'inRoom',   		//进入房间
	'outRoom',   		//退出房间
	'siteDown',   		//房间内坐下位置
	'standUp',   		//房间内位置站起
	'preAddBet',   		//预约增加筹码
	'checkout',   		//德州游戏结算
	'cow_checkout',   	//牛加水游戏结算
	'thri_checkout',    //三公游戏结算
	'cowcow_checkout'   //牛牛游戏结算
	
);
if( !in_array($op, $modAry) ) {
	CMD(202);
}



switch ($op) {


	//取房间信息
	case 'getRoom':

		//读取房间信息
		$roomInfo = ClubRoom::getRoomCache($roomId);
		if(!$roomInfo) {
			CMD(216);
		}

		//判断房间类型
		if($roomInfo['game']!=$game) {
			CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
		}

		CMD(200, array('roomInfo'=>$roomInfo));

		break;

	
	//房间开局
	case 'startGame':

		//读取房间信息
		$roomInfo = ClubRoom::getRoomCache($roomId);
		if(!$roomInfo) {
			CMD(216);
		}

		//判断房间类型
		if($roomInfo['game']!=$game) {
			CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
		}

		//取俱乐部id
		$clubId = ClubRoom::getClubId($roomId);
		if($clubId<=0) {
			CMD(210, '房间key错误，房间ID：'.$roomId, 'msg');
		}

		//判断当前玩家是否有管理俱乐部桌子权限
		ClubMember::isPurview(CLUB_PURVIEW_DESK, $clubId);

		//更新房间开局状态
		ClubRoom::setStart($roomId);


		//房间key
		$roomKey = 'roomInfo:'.$clubId.':'.$roomId;

		$cache = getCache();

		//修改房间状态
		$cache->setArray($roomKey, array('status'=>1), false);

		//读取房间信息
		$roomInfo = $cache->getArray($roomKey);

		//缓存过期时间更新(房间、房间中的玩家)
		$t = (int)$roomInfo['roomTime'] + 3600;
		$cache->expire($roomKey, $t);

		//坐下及旁观者
		$roomInfo['players']   = array();
		$roomInfo['upPlayers'] = array();

		//查找房间中坐下的人数
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			
			$cache->expire($pItem, $t);

			//读取玩家信息
			$userInfo = $cache->getArray($pItem);

			//合并玩家信息到房间信息
			$roomInfo['players'][$userInfo['uid']] = $userInfo;
		}

		//查找房间中旁观的人数
		$upKeys = $cache->keys('upPlayers:'.$roomId.':*');
		foreach($upKeys as $upItem) {
			
			$cache->expire($upItem, $t);

			//读取玩家信息
			$userInfo = $cache->getArray($upItem);

			//合并玩家信息到房间信息
			$roomInfo['upPlayers'][$userInfo['uid']] = $userInfo;
		}

		CMD(200, array('roomInfo'=>$roomInfo));

		break;


	//开始游戏
	case 'play':

		//读取房间信息
		$roomInfo = ClubRoom::getRoomCache($roomId);
		if(!$roomInfo) {
			CMD(216);
		}

		//判断房间类型
		if($roomInfo['game']!=$game) {
			CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
		}

		//判断房间是否开局
		if( !(int)$roomInfo['status'] ) {
			CMD(229, '房间未开局，房间ID：'.$roomId, 'msg');
		}

		$cache = getCache();

		//判断房间人数是否达2人及以上
		$pKeys = $cache->keys('players:'.$roomId.':*');
		if( count($pKeys)<2 ) {
			CMD(230, '房间人数不足，无法开始游戏，房间ID：'.$roomId, 'msg');
		}

		//查找房间key
		$keys = $cache->keys('roomInfo:*:'.$roomId);
		if( count($keys)<=0 ) {
			CMD(210, '房间不存在，房间ID：'.$roomId, 'msg');
		}

		//修改房间状态
		$playTimeout = time()+60*10;
		$cache->setArray($keys[0], array('playTimeout'=>$playTimeout), false);

		CMD(200, array('roomInfo'=>$roomInfo));

		break;


	//进入房间
	case 'inRoom':

		//密码
		$pw = $_P['pw'];

		$cache = getCache();

		//查找当前玩家是否已在房间中坐下
		if( count($cache->keys('players:*:'.UID))>0 ) {

			//判断房间是否过期
			$rId = ClubRoom::getRoomId(UID);
			$rInfo = ClubRoom::getRoomCache($rId);
			if($rInfo) {
				if($rId==$roomId) {
					CMD(225, array('roomInfo'=>$rInfo));
				}else{
					CMD(210, '玩家已在其它房间座位上，玩家ID：'.UID, 'msg');
				}
			}else{
				$cache->delete('players:'.$rId.':'.UID);
			}
		}

		//查找当前玩家是否已在房间中旁观
		if( count($cache->keys('upPlayers:*:'.UID))>0 ) {

			//判断房间是否过期
			$rId = ClubRoom::getRoomId(UID);
			$rInfo = ClubRoom::getRoomCache($rId);
			if($rInfo) {
				if($rId==$roomId) {
					CMD(225, array('roomInfo'=>$rInfo));
				}else{
					CMD(210, '玩家已在其它房间旁观中，玩家ID：'.UID, 'msg');
				}
			}else{
				$cache->delete('upPlayers:'.$rId.':'.UID);
			}
		}

		//查找房间key
		$keys = $cache->keys('roomInfo:*:'.$roomId);

		//判断房间是否存在
		if( count($keys)<=0 ) {
			CMD(216, '房间不存在，房间ID：'.$roomId, 'msg');
		}
		foreach($keys as $item) {

			//取俱乐部id
			$keyAry = explode(':',$item);
			$clubId = (int)$keyAry[1];
			if( $clubId<=0 ) {
				CMD(210, '俱乐部ID为空，Redis Room ID：'.$item, 'msg');
			}

			//判断当前玩家是否已在俱乐部中(非俱乐部成员不能进入俱乐部房间)
			$purview = ClubMember::getPurview($clubId);
			if(!$purview) {
				CMD(210, '玩家不是俱乐部成员，俱乐部ID：'.$clubId.'，玩家UID：'.UID, 'msg');
			}

			//读取房间信息
			$roomInfo = $cache->getArray($item);

			//判断房间密码
			if( $roomInfo['pw'] && $roomInfo['pw']!=$pw ) {
				CMD(212);
			}

			//判断房间类型
			if($roomInfo['game']!=$game) {
				CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
			}

			//读取当前玩家的该房间买入数据，有买入过则可直接进入房间(输光也可旁观)
			$betData = ClubRoom::getBetData('id,curBet', $roomId);
			$curBet = 0;
			if(!(int)$betData['id']) {

				//读取会员在该俱乐部的俱乐部币
				$coin = (int)ClubMember::getInfo('coin', $clubId);

				//判断携带筹码是否小于房间要求下限,则不允许进入
				if( $coin<(int)$roomInfo['minBet'] ) {
					CMD(213);
				}
			}else{
				$curBet = (int)$betData['curBet'];
			}
			
			//获取房间剩余缓存过期时间
			$timeFlag = (int)$roomInfo['status'] ? 3600 : 0;
			$roomttl = $cache->ttl($item);
			if($roomttl<=$timeFlag) {

				//如果游戏正在进行中，则不判断房间超时
				if( (int)$roomInfo['playTimeout']<=time() ) {
					CMD(216, '房间已过期，Redis Room Key：'.$item, 'msg');
				}
			}

			//判断当前玩家是否有管理俱乐部桌子权限
			$isManage = (int)ClubMember::isPurview(CLUB_PURVIEW_DESK, $clubId, true);

			//加入房间
			$info = User::get('nickname,avatar,pos') + User::getVipInfo();
			$userAry = array(
				'uid'			=> UID,
				'nickname'		=> $info['nickname'],
				'avatar'		=> $info['avatar'],
				'pos_x'			=> $info['pos_x'],
				'pos_y'			=> $info['pos_y'],
				'delayNum'		=> $info['card_delay_num'],
				'bet'			=> $curBet,
				'startEnable'	=> $isManage
			);
			$cache->setArray('upPlayers:'.$roomId.':'.UID, $userAry, $roomttl);

			//坐下及旁观者
			$roomInfo['players']   = array();
			$roomInfo['upPlayers'] = array();

			//查找房间中坐下的人数
			$pKeys = $cache->keys('players:'.$roomId.':*');
			foreach($pKeys as $pItem) {
				//读取玩家信息
				$userInfo = $cache->getArray($pItem);

				//合并玩家信息到房间信息
				$roomInfo['players'][$userInfo['uid']] = $userInfo;
			}

			//查找房间中旁观的人数
			$upKeys = $cache->keys('upPlayers:'.$roomId.':*');
			foreach($upKeys as $upItem) {
				//读取玩家信息
				$userInfo = $cache->getArray($upItem);

				//合并玩家信息到房间信息
				$roomInfo['upPlayers'][$userInfo['uid']] = $userInfo;
			}

			//牌局剩余时间换算
			$sytime = $roomttl-$timeFlag;
			$roomInfo['roomTime'] = ($sytime>0) ? $sytime : 0 ;

			break;
		}

		CMD(200, array('roomInfo'=>$roomInfo));

		break;


	//退出房间
	case 'outRoom':

		//读取房间信息
		$roomInfo = ClubRoom::getRoomCache($roomId);
		if(!$roomInfo) {
			CMD(216);
		}

		//判断房间类型
		if($roomInfo['game']!=$game) {
			CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
		}

		$cache = getCache();

		//查找当前玩家是否已在房间中坐下
		if( count($cache->keys('players:*:'.UID))>0 ) {
			CMD(210, '玩家还在座位上，玩家ID：'.UID, 'msg');
		}

		//查找玩家key
		$upKeys = $cache->keys('upPlayers:*:'.UID);
		if( count($upKeys)<=0 ) {
			CMD(210, '玩家不在房间旁观，玩家ID：'.UID, 'msg');
		}
		foreach($upKeys as $upItem) {

			//取房间id
			$upKeyAry = explode(':',$upItem);
			$roomId = (int)$upKeyAry[1];
			if( $roomId<=0 ) {
				CMD(210, '房间ID为空，Redis player Key：'.$upItem, 'msg');
			}

			$uidArr = ClubRoom::getUID($roomId);
			
			//删除缓存中旁观的玩家
			$cache->delete($upItem);

			break;
		}

		CMD(200, array('uidArr'=>$uidArr));

		break;


	//房间内坐下位置
	case 'siteDown':

		//座位号
		$site = (int)$_P['site'];
		if( $site<0 ) {
			CMD(202);
		}


		//读取房间信息
		$roomInfo = ClubRoom::getRoomCache($roomId);
		if(!$roomInfo) {
			CMD(216);
		}

		//判断房间类型
		if($roomInfo['game']!=$game) {
			CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
		}

		$cache = getCache();

		//查找当前玩家是否已在房间中坐下
		$playerKeys = $cache->keys('players:*:'.UID);
		if( count($playerKeys)>0 ) {
			$playerKeyAry = explode(':',$playerKeys[0]);
			$rId = (int)$playerKeyAry[1];
			if($rId!=$roomId) {
				CMD(210, '玩家已在其它房间座位上，玩家ID：'.UID, 'msg');
			}else{
				$playerInfo = $cache->getArray($playerKeys[0]);
				if($playerInfo['site']!=$site) {
					CMD(210, '玩家已在其它座位上，玩家ID：'.UID, 'msg');
				}
			}
			CMD(228, '玩家已在座位上，玩家ID：'.UID, 'msg');
		}

		//查找当前玩家是否在房间中旁观(只有旁观者才能坐下)
		$upKeys = $cache->keys('upPlayers:*:'.UID);
		if( count($upKeys)<=0 ) {
			CMD(210, '玩家不在旁观中，玩家ID：'.UID, 'msg');
		}
		foreach($upKeys as $upItem) {

			//取房间id
			$upKeyAry = explode(':',$upItem);
			$roomId = (int)$upKeyAry[1];
			if( $roomId<=0 ) {
				CMD(210, '房间ID为空，Redis player Key：'.$upItem, 'msg');
			}


			//判断位置是否已被占用
			$pKeys = $cache->keys('players:'.$roomId.':*');
			foreach($pKeys as $pItem) {
				//读取玩家信息
				$psite = (int)$cache->getArray($pItem,'site');

				if($psite==$site) {
					CMD(215);
				}
			}

			//读取玩家当前筹码
			$betData = ClubRoom::getBetData('id,curBet,preAddBet', $roomId);
			if(!(int)$betData['id']) {
				CMD(214);

			//合并预买入筹码
			}elseif((int)$betData['preAddBet']>0) {
				$rs = ClubRoom::plusPreBet($betData['id']);
				if(!$rs) {
					CMD(210, '合并预买入筹码失败，记录ID：'.$betData['id'], 'msg');
				}
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];

			}else{
				$curBet = (int)$betData['curBet'];
			}


			//查找房间key
			$keys = $cache->keys('roomInfo:*:'.$roomId);
			if( count($keys)<=0 ) {
				CMD(210, '房间不存在，房间ID：'.$roomId, 'msg');
			}

			//取俱乐部id
			$keyAry = explode(':',$keys[0]);
			$clubId = (int)$keyAry[1];
			if( $clubId<=0 ) {
				CMD(210, '俱乐部ID为空，Redis Room Key：'.$keys[0], 'msg');
			}

			//读取会员在该俱乐部的俱乐部币
			$coin = (int)ClubMember::getInfo('coin', $clubId);

			//判断会员俱乐部币是否大等于当前筹码
			if( $coin<$curBet ) {
				CMD(213);
			}

			//读取房间信息
			$blindBet = $cache->getArray($keys[0], 'blindBet');

			//判断当前筹码是否大等于房间盲注/底注
			if( $curBet<(int)$blindBet ) {
				CMD(214);
			}

			//修改key为坐下玩家
			$key = 'players:'.$roomId.':'.UID;
			$cache->rename($upItem, $key);
			$cache->setArray($key, array('site'=>$site,'bet'=>$curBet), false);

			$playerInfo = $cache->getArray($key);
			$uidArr = ClubRoom::getUID($roomId);

			break;
		}

		CMD(200, array('roomId'=>$roomId,'playerInfo'=>$playerInfo, 'uidArr'=>$uidArr));

		break;


	//房间内位置站起
	case 'standUp':

		//读取房间信息
		$roomInfo = ClubRoom::getRoomCache($roomId);
		if(!$roomInfo) {
			CMD(216);
		}

		//判断房间类型
		if($roomInfo['game']!=$game) {
			CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
		}

		$cache = getCache();

		//查找当前玩家是否已在房间中旁观
		if( count($cache->keys('upPlayers:*:'.UID))>0 ) {
			CMD(227, '玩家已在房间旁观中，玩家UID：'.UID, 'msg');
		}

		//查找当前玩家是否在房间中坐下(只有坐下者才能站起)
		$Keys = $cache->keys('players:*:'.UID);
		if( count($Keys)<=0 ) {
			CMD(210, '玩家不在座位上，玩家UID：'.UID, 'msg');
		}
		foreach($Keys as $item) {

			//取房间id
			$keyAry = explode(':',$item);
			$roomId = (int)$keyAry[1];
			if( $roomId<=0 ) {
				CMD(210, '房间ID为空，Redis Player Key：'.$item, 'msg');
			}

			//修改key为旁观玩家
			$upKey = 'upPlayers:'.$roomId.':'.UID;
			$cache->rename($item, $upKey);
			$cache->hdel($upKey, 'site');

			$uidArr = ClubRoom::getUID($roomId);

			break;
		}

		CMD(200, array('uidArr'=>$uidArr));

		break;


	//买入筹码
	case 'preAddBet':

		//买入数量
		$bet = (int)$_P['bet'];
		if( $bet<=0 ) {
			CMD(202);
		}

		$cache = getCache();

		//判断玩家是否在房间中
		$roomId = ClubRoom::getRoomId();
		if(!$roomId) {
			CMD(210, '玩家不在房间中，玩家UID：'.UID, 'msg');
		}

		$clubId = ClubRoom::getClubId($roomId);
		if(!$clubId) {
			CMD(210, '俱乐部ID为空，roomId：'.$roomId, 'msg');
		}

		//读取房间信息
		$roomInfo = $cache->getArray('roomInfo:'.$clubId.':'.$roomId);

		//判断房间类型
		if($roomInfo['game']!=$game) {
			CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
		}

		//读取玩家当前筹码
		$betData = ClubRoom::getBetData('id,curBet', $roomId);
		$totalBet = $bet + (int)$betData['curBet'];
		
		//判断筹码是否在范围内
		if( $totalBet<(int)$roomInfo['minBet'] ) {
			CMD(210, '买入后筹码小于下限，买入筹码：'.$bet.'，原有筹码：'.$betData['curBet'].'，筹码下限：'.$roomInfo['minBet'], 'msg');
		}
		if( (int)$roomInfo['maxBet']>0 && $totalBet>(int)$roomInfo['maxBet'] ) {
			CMD(237, '买入后筹码大于上限，买入筹码：'.$bet.'，原有筹码：'.$betData['curBet'].'，筹码上限：'.$roomInfo['maxBet'], 'msg');
		}

		//读取会员在该俱乐部的俱乐部币
		$coin = (int)ClubMember::getInfo('coin', $clubId);

		//判断筹码是否有超出会员俱乐部币
		if( $totalBet>$coin ) {
			CMD(213);
		}

		if( (int)$betData['id']>0 ) {
			$rs = ClubRoom::preAddBet($bet, $betData['id']);

		}else{
			$gameType = $roomInfo['game']=='dzPoker' ? 1 : ($roomInfo['game']=='cowWater' ? 2 : ($roomInfo['game']=='thriDucal' ? 3 : ($roomInfo['game']=='cowcow' ? 4 : 0)));
			$rs = ClubRoom::createAddBet($gameType, $bet, $clubId, $roomId);
		}

		if(!$rs) {
			CMD(210, '预买入失败，买入数：'.$bet.'，记录ID：'.$betData['id'].'，房间ID :'.$roomId, 'msg');
		}

		$uidArr = ClubRoom::getUID($roomId);
		
		CMD(200, array('uidArr'=>$uidArr));

		break;


	//游戏结算-德州
	case 'checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

		//底牌张数
		$underCardsIndex = (int)$_P['underCardsIndex'];
		$underCards = $_P['underCards'];
		if( !is_array($underCards) ) {
			CMD(210, '底牌参数格式错误，不是数组，underCards：'.$underCards, 'msg');
		}

		//手牌记录
		$handJson = array();

		//被站起的人员
		$standUpPlayers = array();

		$cache = getCache();

		$cfg = getCFG('data');

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		foreach($result as $key => $item) {

			$uid = (int)$key;
			$baseBet = (int)$item['balance'];
			$bet = (int)$item['balance'];
			$costBet = (int)$item['costBet'];

			//查找当前玩家缓存key
			$keys = $cache->keys('players:*:'.$uid);

			//通过房间id取俱乐部id
			$roomKeys = $cache->keys('roomInfo:*:'.$roomId);
			//取俱乐部id
			$roomKeyAry = explode(':',$roomKeys[0]);
			$clubId = (int)$roomKeyAry[1];
			if( $clubId<=0 ) {
				CMD(201, '俱乐部ID为空，Redis Key：'.$roomKeys[0], 'msg');
			}

			
			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);		//绝对值
			}

			//服务费
			$scale = 0;
			if($bet){
				if($add){
					//读取房间信息
					$costScale = $cache->getArray($roomKeys[0], 'costScale');
					$scale = floor(($bet+$costBet) * (float)$costScale);

					//俱乐部加rb
					if($scale>0) {
						//按比例分给推广员
						$inviteUid = $DB->getValue('SELECT inviteUid FROM club_member WHERE clubId='.$clubId.' AND uid='.$uid.' AND status=1');
						//$inviteUid = ClubMember::getInviteUid($uid,$clubId);			//当前玩家的邀请人uid
						$cut = $DB->getValue('SELECT cut FROM club_member WHERE clubId='.$clubId.' AND uid='.$inviteUid);
						$oldcutNum = ClubMember::getCutNum($inviteUid,$clubId);
						//(float)$cut = ClubMember::getCut($inviteUid,$clubId);			//该邀请人的抽成比例
						(int)$cutNum = round($scale*$cut);								//应得抽成
						
						if($cutNum > 0){
							$newScale = floor($scale - $cutNum);						//扣除抽成剩余的服务费

							//推广员加推广费
							$sql = 'cutNum+'.$cutNum;
							$DB->exeSql('UPDATE club_member SET cutNum='.$sql.' WHERE clubId='.$clubId.' AND uid='.$inviteUid);

							//记录抽成进账
							$rel = ClubCounter::addCut($clubId,$roomId,$inviteUid,$uid,$cutNum);
							if($rel == false){
								$DB->rollBack();  //回滚
								CMD(210, '俱乐部抽成进账记录失败'.$clubId.'，服务费：'.$newScale, 'msg');
							}
						}else{
							$newScale = $scale;
						}
						if($newScale >0){
							$rb = Club::rb($clubId, $newScale);
							if($rb===false) {
								$DB->rollBack();  //回滚
								CMD(202, '俱乐部抽水失败，俱乐部ID：'.$clubId.'，服务费：'.$newScale, 'msg');
							}

							//记录服务费
							$rsc = ClubRoom::addScale($roomId, $uid, $newScale);
							if(!$rsc) {
								$DB->rollBack();  //回滚
								CMD(203, '统计服务费失败，房间ID：'.$roomId.'，UID：'.$uid.'，服务费：'.$newScale, 'msg');
							}
						}
						
						
					}
				}
				//判断服务费是否超过赢的钱
				$tbet = $bet-$scale;
				if($tbet<0) {
					$add = false;
					$tbet = abs($tbet);
				}

				if($tbet>0) {
					//结算钱包
					$rs1 = ClubMember::coin($clubId, $uid, $tbet, $add, true);
					if($rs1===false) {
						$DB->rollBack();  //回滚
						CMD(204, '钱包结算失败，俱乐部ID：'.$clubId.'，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
					}

					//结算筹码
					$rs2 = ClubRoom::bet($roomId, $uid, $tbet, $add, true);
					if($rs2===false) {
						$DB->rollBack();  //回滚
						CMD(205, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
					}
				}
			}

			//玩家手牌计数
			$handRs = ClubRoom::addHandNum($roomId, $uid, ($baseBet-$scale), $item['enterPool']);
			if(!$handRs) {
				$DB->rollBack();  //回滚
				CMD(206, '手牌累计失败，roomId：'.$roomId.'，UID：'.$uid, 'msg');
			}

			//记录手牌信息
			$handJson[] = array('uid'=>$uid,'cards'=>$item['cards'],'balance'=>($baseBet-$scale),'compCardUserNum'=>(int)$_P['compCardUserNum']);
			
			//记录幸运牌玩家
			if( in_array($item['cardType'], $cfg['clubLuckyTypeDZ']) ) {
				$luckyRs = ClubRoom::addLuckyData($clubId, $roomId, $uid, $item['cardType'], $item['cardList']);
				if(!$luckyRs) {
					$DB->rollBack();  //回滚
					CMD(207, '幸运牌记录失败，clubId：'.$clubId.'，roomId：'.$roomId.'，UID：'.$uid.'，cardType：'.$item['cardType'].'，cardList：'.$item['cardList'], 'msg');
				}
			}


			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = ClubRoom::getBetData('id,curBet,preAddBet', $roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$rs3 = ClubRoom::plusPreBet($betData['id']);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(208, '合并预买入筹码失败，记录ID：'.$betData['id'], 'msg');
				}
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];

			}else{
				$curBet = (int)$betData['curBet'];
			}
			$playersCacheAry['bet'] = $curBet;
			$playersCacheAry['balance'] = (int)$item['balance']-$scale;

			//更新玩家延时次数
			if( (int)$item['delayUseNum']>0 ) {
				$curDelay = (int)User::get('card_delay_num', 'uid='.$uid);
				$curDelay = $curDelay - (int)$item['delayUseNum'];
				$curDelay = $curDelay>0 ? $curDelay : 0;

				User::edit(array('card_delay_num'=>$curDelay), $uid);
				$playersCacheAry['delayNum'] = $curDelay;
			}

			//更新玩家缓存
			if( count($keys)>0 ) {
				$cache->setArray($keys[0], $playersCacheAry, false);
			}

			//修改房间信息
			$editAry = array(
				'bankerSite'=>$bankerSite, //设置庄家
				'playTimeout'=>0 //本手游戏结束
			);
			$cache->setArray($roomKeys[0], $editAry, false);


			//==============================判断是否需要自动站起==========================//

			//读取房间信息
			$blindBet = $cache->getArray($roomKeys[0], 'blindBet');

			//判断当前筹码是否大等于房间盲注/底注
			if( count($keys)>0 && (($curBet<(int)$blindBet) || (int)$item['timeout']) ) {
				
				//修改key为站起玩家
				$newKey = 'upPlayers:'.$roomId.':'.$uid;
				$cache->rename($keys[0], $newKey);

				$standUpPlayers[$uid] = $cache->getArray($newKey);
			}

			//判断是否退出房间
			if( (int)$item['outRoom'] ) {
				$keysup = $cache->keys('upPlayers:*:'.$uid);
				if( count($keysup)>0 ) {
					if( !$standUpPlayers[$uid] ) {
						$standUpPlayers[$uid] = $cache->getArray($keysup[0]);
					}
					$cache->delete($keysup[0]);
				}
			}
			
		}

		//写入手牌数据
		if($handJson) {
			$hrs = ClubRoom::addHandData($clubId, $roomId, 1, array_slice($underCards, 0, $underCardsIndex), $handJson);
			if(!$hrs) {
				$DB->rollBack();
				CMD(209, '手牌记录写入失败，clubId：'.$clubId.'，roomId：'.$roomId.'，underCards：'.$underCards.'，underCardsIndex：'.$underCardsIndex.'，内容：'.$handJson, 'msg');
			}
		}

		
		$DB->commit();    //提交


		//坐下者列表
		$players = array();

		//查找房间中坐下的人数
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			//读取玩家信息
			$userInfo = $cache->getArray($pItem);

			$players[$userInfo['uid']] = $userInfo;
		}

		//判断房间是否过期
		$roomOver = ClubRoom::getRoomCache($roomId);

		$chkData = array('players'=>$players, 'standUpPlayers'=>$standUpPlayers);
		if(!$roomOver) {
			$chkData['roomOver'] = 1;
		}

		CMD(200, $chkData);

		break;


	//游戏结算-牛加水
	case 'cow_checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

		//手牌记录
		$handJson = array();
		$handJson['bankerUid_SG'] 			= $_P['bankerUid_SG'];
		$handJson['bankerUid_COW'] 			= $_P['bankerUid_COW'];
		$handJson['maxCallBankerOdds_SG'] 	= $_P['maxCallBankerOdds_SG'];
		$handJson['maxCallBankerOdds_COW'] 	= $_P['maxCallBankerOdds_COW'];

		//庄家
		$bankerUid_SG  = (int)$_P['bankerUid_SG'];
		$bankerUid_COW = (int)$_P['bankerUid_COW'];

		//被站起的人员
		$standUpPlayers = array();

		$cache = getCache();

		$cfg = getCFG('data');

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		foreach($result as $key => $item) {

			$uid     = (int)$key;
			$baseBet = (int)$item['balance'];
			$bet     = (int)$item['balance'];
			$costBet = (int)$item['costBet'];

			$balance_SG  = (int)$item['balance_SG'];
			$balance_COW = (int)$item['balance_COW'];

			//查找当前玩家缓存key
			$keys = $cache->keys('players:*:'.$uid);

			//通过房间id取俱乐部id
			$roomKeys = $cache->keys('roomInfo:*:'.$roomId);
			//取俱乐部id
			$roomKeyAry = explode(':',$roomKeys[0]);
			$clubId = (int)$roomKeyAry[1];
			if( $clubId<=0 ) {
				CMD(210, '俱乐部ID为空，Redis Key：'.$roomKeys[0], 'msg');
			}


			//读取房间抽水比例
			$costScale = $cache->getArray($roomKeys[0], 'costScale');


			//三公服务费
			$sg_scale = 0;
			if( ($uid==$bankerUid_SG) && ($balance_SG>0) ) {
				$sg_scale = floor($balance_SG * (float)$costScale);
			}

			//牛牛服务费
			$cow_scale = 0;
			if( ($uid==$bankerUid_COW) && ($balance_COW>0) ) {
				$cow_scale = floor($balance_COW * (float)$costScale);
			}

			//总服务费
			$scale = $sg_scale + $cow_scale;
			if($scale>0) {
				
				//俱乐部加R币

				//按比例分给推广员
				$inviteUid = $DB->getValue('SELECT inviteUid FROM club_member WHERE clubId='.$clubId.' AND uid='.$uid.' AND status=1');
				//$inviteUid = ClubMember::getInviteUid($uid,$clubId);			//当前玩家的邀请人uid
				$cut = $DB->getValue('SELECT cut FROM club_member WHERE clubId='.$clubId.' AND uid='.$inviteUid);
				$oldcutNum = ClubMember::getCutNum($inviteUid,$clubId);
				//(float)$cut = ClubMember::getCut($inviteUid,$clubId);			//该邀请人的抽成比例
				(int)$cutNum = round($scale*$cut);								//应得抽成
				
				if($cutNum > 0){
					$newScale = floor($scale - $cutNum);						//扣除抽成剩余的服务费

					//推广员加推广费
					$sql = 'cutNum+'.$cutNum;
					$DB->exeSql('UPDATE club_member SET cutNum='.$sql.' WHERE clubId='.$clubId.' AND uid='.$inviteUid);

					//记录抽成进账
					ClubCounter::addCut($clubId,$roomId,$inviteUid,$uid,$cutNum);
				}else{
					$newScale = $scale;
				}

				if($newScale >0){
					$rb = Club::rb($clubId, $newScale);
					if($rb===false) {
						$DB->rollBack();  //回滚
						CMD(210, '俱乐部抽水失败，俱乐部ID：'.$clubId.'，服务费：'.$newScale, 'msg');
					}

					//记录服务费
					$rsc = ClubRoom::addScale($roomId, $uid, $newScale);
					if(!$rsc) {
						$DB->rollBack();  //回滚
						CMD(210, '统计服务费失败，房间ID：'.$roomId.'，UID：'.$uid.'，服务费：'.$newScale, 'msg');
					}
				}
				
			}


			$bet -= $scale;

			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);
			}

			if($bet) {

				//结算钱包
				$rs1 = ClubMember::coin($clubId, $uid, $bet, $add, true);
				if($rs1===false) {
					$DB->rollBack();  //回滚
					CMD(210, '钱包结算失败，俱乐部ID：'.$clubId.'，UID：'.$uid.'，结算筹码：'.$baseBet.'，服务费：'.$scale, 'msg');
				}

				//结算筹码
				$rs2 = ClubRoom::bet($roomId, $uid, $bet, $add, true);
				if($rs2===false) {
					$DB->rollBack();  //回滚
					CMD(210, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$baseBet.'，服务费：'.$scale, 'msg');
				}
			}

			//玩家手牌计数
			$handRs = ClubRoom::addHandNum($roomId, $uid, ($baseBet-$scale), $item['enterPool']);
			if(!$handRs) {
				$DB->rollBack();  //回滚
				CMD(210, '手牌累计失败，roomId：'.$roomId.'，UID：'.$uid, 'msg');
			}

			//记录手牌信息
			$handJson['players'][$uid] = array('uid'=>$uid,'cards_SG'=>$item['cards_SG'],'cards_COW'=>$item['cards_COW'],'balance'=>($baseBet-$scale),'cardType_SG'=>$item['cardType_SG'],'cardType_COW'=>$item['cardType_COW'],'cardTypePoint_SG'=>0,'cardTypePoint_COW'=>$item['cardTypePoint_COW'],'balance_SG'=>($balance_SG-$sg_scale),'balance_COW'=>($balance_COW-$cow_scale));
			
			//记录幸运牌玩家 - 三公
			if( in_array($item['cardType_SG'], $cfg['clubLuckyTypeSG']) ) {
				$luckyRs = ClubRoom::addLuckyData($clubId, $roomId, $uid, $item['cardType_SG'], $item['cards_SG']);
				if(!$luckyRs) {
					$DB->rollBack();  //回滚
					CMD(210, '幸运牌记录失败，clubId：'.$clubId.'，roomId：'.$roomId.'，UID：'.$uid.'，cardType_SG'.$item['cardType_SG'].'，cards_SG'.$item['cards_SG'], 'msg');
				}
			}
			//记录幸运牌玩家 - 牛牛
			if( in_array($item['cardType_COW'], $cfg['clubLuckyTypeCOW']) ) {
				$luckyRs = ClubRoom::addLuckyData($clubId, $roomId, $uid, $item['cardType_COW'], $item['cards_COW']);
				if(!$luckyRs) {
					$DB->rollBack();  //回滚
					CMD(210, '幸运牌记录失败，clubId：'.$clubId.'，roomId：'.$roomId.'，UID：'.$uid.'，cardType_COW'.$item['cardType_COW'].'，cards_COW'.$item['cards_COW'], 'msg');
				}
			}

			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = ClubRoom::getBetData('id,curBet,preAddBet', $roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$rs3 = ClubRoom::plusPreBet($betData['id']);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(210, '合并预买入筹码失败，记录ID：'.$betData['id'], 'msg');
				}
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];

			}else{
				$curBet = (int)$betData['curBet'];
			}
			$playersCacheAry['bet'] = $curBet;
			$playersCacheAry['balance'] = (int)$item['balance']-$scale;

			//更新玩家延时次数
			if( (int)$item['delayUseNum']>0 ) {
				$curDelay = (int)User::get('card_delay_num', 'uid='.$uid);
				$curDelay = $curDelay - (int)$item['delayUseNum'];
				$curDelay = $curDelay>0 ? $curDelay : 0;

				User::edit(array('card_delay_num'=>$curDelay), $uid);
				$playersCacheAry['delayNum'] = $curDelay;
			}

			//更新玩家缓存
			if( count($keys)>0 ) {
				$cache->setArray($keys[0], $playersCacheAry, false);
			}

			//修改房间信息
			$editAry = array(
				'bankerSite'=>$bankerSite, //设置庄家
				'playTimeout'=>0 //本手游戏结束
			);
			$cache->setArray($roomKeys[0], $editAry, false);


			//==============================判断是否需要自动站起==========================//

			//读取房间信息
			$blindBet = $cache->getArray($roomKeys[0], 'blindBet');

			//判断当前筹码是否大等于房间盲注/底注
			if( count($keys)>0 && (($curBet<(int)$blindBet) || (int)$item['timeout']) ) {
				
				//修改key为站起玩家
				$newKey = 'upPlayers:'.$roomId.':'.$uid;
				$cache->rename($keys[0], $newKey);

				$standUpPlayers[$uid] = $cache->getArray($newKey);
			}

			//判断是否退出房间
			if( (int)$item['outRoom'] ) {
				$keysup = $cache->keys('upPlayers:*:'.$uid);
				if( count($keysup)>0 ) {
					if( !$standUpPlayers[$uid] ) {
						$standUpPlayers[$uid] = $cache->getArray($keysup[0]);
					}
					$cache->delete($keysup[0]);
				}
			}
			
		}

		//写入手牌数据
		if($handJson) {
			$hrs = ClubRoom::addHandData($clubId, $roomId, 2, array(), $handJson);
			if(!$hrs) {
				$DB->rollBack();  //回滚
				CMD(210, '手牌记录写入失败，clubId：'.$clubId.'，roomId：'.$roomId.'，内容：'.$handJson, 'msg');
			}
		}

		
		$DB->commit();    //提交


		//坐下者列表
		$players = array();

		//查找房间中坐下的人数
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			//读取玩家信息
			$userInfo = $cache->getArray($pItem);

			$players[$userInfo['uid']] = $userInfo;
		}

		//判断房间是否过期
		$roomOver = ClubRoom::getRoomCache($roomId);

		$chkData = array('players'=>$players, 'standUpPlayers'=>$standUpPlayers);
		if(!$roomOver) {
			$chkData['roomOver'] = 1;
		}

		CMD(200, $chkData);

		break;

	//游戏结算-牛加水
	case 'thri_checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

		//手牌记录
		$handJson = array();
		$handJson['bankerUid_SG'] 			= $_P['bankerUid_SG'];
		// 修改 $handJson['bankerUid_COW'] 			= $_P['bankerUid_COW'];
		$handJson['maxCallBankerOdds_SG'] 	= $_P['maxCallBankerOdds_SG'];
		// 修改 $handJson['maxCallBankerOdds_COW'] 	= $_P['maxCallBankerOdds_COW'];

		//庄家
		$bankerUid_SG  = (int)$_P['bankerUid_SG'];
		// 修改 $bankerUid_COW = (int)$_P['bankerUid_COW'];

		//被站起的人员
		$standUpPlayers = array();

		$cache = getCache();

		$cfg = getCFG('data');

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		foreach($result as $key => $item) {

			$uid     = (int)$key;
			$baseBet = (int)$item['balance'];
			$bet     = (int)$item['balance'];
			$costBet = (int)$item['costBet'];

			$balance_SG  = (int)$item['balance_SG'];
			// 修改 $balance_COW = (int)$item['balance_COW'];

			//查找当前玩家缓存key
			$keys = $cache->keys('players:*:'.$uid);

			//通过房间id取俱乐部id
			$roomKeys = $cache->keys('roomInfo:*:'.$roomId);
			//取俱乐部id
			$roomKeyAry = explode(':',$roomKeys[0]);
			$clubId = (int)$roomKeyAry[1];
			if( $clubId<=0 ) {
				CMD(210, '俱乐部ID为空，Redis Key：'.$roomKeys[0], 'msg');
			}


			//读取房间抽水比例
			$costScale = $cache->getArray($roomKeys[0], 'costScale');


			//三公服务费
			$sg_scale = 0;
			if( ($uid==$bankerUid_SG) && ($balance_SG>0) ) {
				$sg_scale = floor($balance_SG * (float)$costScale);
			}

			//牛牛服务费
			// $cow_scale = 0;
			// if( ($uid==$bankerUid_COW) && ($balance_COW>0) ) {
			// 	$cow_scale = floor($balance_COW * (float)$costScale);
			// }

			//总服务费
			$scale = $sg_scale;
			if($scale>0) {
				
				//俱乐部加R币

				//按比例分给推广员
				$inviteUid = $DB->getValue('SELECT inviteUid FROM club_member WHERE clubId='.$clubId.' AND uid='.$uid.' AND status=1');
				//$inviteUid = ClubMember::getInviteUid($uid,$clubId);			//当前玩家的邀请人uid
				$cut = $DB->getValue('SELECT cut FROM club_member WHERE clubId='.$clubId.' AND uid='.$inviteUid);
				$oldcutNum = ClubMember::getCutNum($inviteUid,$clubId);
				//(float)$cut = ClubMember::getCut($inviteUid,$clubId);			//该邀请人的抽成比例
				(int)$cutNum = round($scale*$cut);								//应得抽成
				
				if($cutNum > 0){
					$newScale = floor($scale - $cutNum);						//扣除抽成剩余的服务费

					//推广员加推广费
					$sql = 'cutNum+'.$cutNum;
					$DB->exeSql('UPDATE club_member SET cutNum='.$sql.' WHERE clubId='.$clubId.' AND uid='.$inviteUid);

					//记录抽成进账
					ClubCounter::addCut($clubId,$roomId,$inviteUid,$uid,$cutNum);
				}else{
					$newScale = $scale;
				}
				if($newScale >0){
					$rb = Club::rb($clubId, $newScale);
					if($rb===false) {
						$DB->rollBack();  //回滚
						CMD(210, '俱乐部抽水失败，俱乐部ID：'.$clubId.'，服务费：'.$newScale, 'msg');
					}

					//记录服务费
					$rsc = ClubRoom::addScale($roomId, $uid, $newScale);
					if(!$rsc) {
						$DB->rollBack();  //回滚
						CMD(210, '统计服务费失败，房间ID：'.$roomId.'，UID：'.$uid.'，服务费：'.$newScale, 'msg');
					}
				}
				
			}


			$bet -= $scale;

			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);
			}

			if($bet) {

				//结算钱包
				$rs1 = ClubMember::coin($clubId, $uid, $bet, $add, true);
				if($rs1===false) {
					$DB->rollBack();  //回滚
					CMD(210, '钱包结算失败，俱乐部ID：'.$clubId.'，UID：'.$uid.'，结算筹码：'.$baseBet.'，服务费：'.$scale, 'msg');
				}

				//结算筹码
				$rs2 = ClubRoom::bet($roomId, $uid, $bet, $add, true);
				if($rs2===false) {
					$DB->rollBack();  //回滚
					CMD(210, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$baseBet.'，服务费：'.$scale, 'msg');
				}
			}

			//玩家手牌计数
			$handRs = ClubRoom::addHandNum($roomId, $uid, ($baseBet-$scale), $item['enterPool']);
			if(!$handRs) {
				$DB->rollBack();  //回滚
				CMD(210, '手牌累计失败，roomId：'.$roomId.'，UID：'.$uid, 'msg');
			}

			//记录手牌信息
			$handJson['players'][$uid] = array('uid'=>$uid,'cards_SG'=>$item['cards_SG'],'balance'=>($baseBet-$scale),'cardType_SG'=>$item['cardType_SG'],
				'cardTypePoint_SG'=>0,'balance_SG'=>($balance_SG-$sg_scale),);
			
			//记录幸运牌玩家 - 三公
			if( in_array($item['cardType_SG'], $cfg['clubLuckyTypeSG']) ) {
				$luckyRs = ClubRoom::addLuckyData($clubId, $roomId, $uid, $item['cardType_SG'], $item['cards_SG']);
				if(!$luckyRs) {
					$DB->rollBack();  //回滚
					CMD(210, '幸运牌记录失败，clubId：'.$clubId.'，roomId：'.$roomId.'，UID：'.$uid.'，cardType_SG'.$item['cardType_SG'].'，cards_SG'.$item['cards_SG'], 'msg');
				}
			}
			//记录幸运牌玩家 - 牛牛
			// if( in_array($item['cardType_COW'], $cfg['clubLuckyTypeCOW']) ) {
			// 	$luckyRs = ClubRoom::addLuckyData($clubId, $roomId, $uid, $item['cardType_COW'], $item['cards_COW']);
			// 	if(!$luckyRs) {
			// 		$DB->rollBack();  //回滚
			// 		CMD(210, '幸运牌记录失败，clubId：'.$clubId.'，roomId：'.$roomId.'，UID：'.$uid.'，cardType_COW'.$item['cardType_COW'].'，cards_COW'.$item['cards_COW'], 'msg');
			// 	}
			// }

			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = ClubRoom::getBetData('id,curBet,preAddBet', $roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$rs3 = ClubRoom::plusPreBet($betData['id']);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(210, '合并预买入筹码失败，记录ID：'.$betData['id'], 'msg');
				}
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];

			}else{
				$curBet = (int)$betData['curBet'];
			}
			$playersCacheAry['bet'] = $curBet;
			$playersCacheAry['balance'] = (int)$item['balance']-$scale;

			//更新玩家延时次数
			if( (int)$item['delayUseNum']>0 ) {
				$curDelay = (int)User::get('card_delay_num', 'uid='.$uid);
				$curDelay = $curDelay - (int)$item['delayUseNum'];
				$curDelay = $curDelay>0 ? $curDelay : 0;

				User::edit(array('card_delay_num'=>$curDelay), $uid);
				$playersCacheAry['delayNum'] = $curDelay;
			}

			//更新玩家缓存
			if( count($keys)>0 ) {
				$cache->setArray($keys[0], $playersCacheAry, false);
			}

			//修改房间信息
			$editAry = array(
				'bankerSite'=>$bankerSite, //设置庄家
				'playTimeout'=>0 //本手游戏结束
			);
			$cache->setArray($roomKeys[0], $editAry, false);


			//==============================判断是否需要自动站起==========================//

			//读取房间信息
			$blindBet = $cache->getArray($roomKeys[0], 'blindBet');

			//判断当前筹码是否大等于房间盲注/底注
			if( count($keys)>0 && (($curBet<(int)$blindBet) || (int)$item['timeout']) ) {
				
				//修改key为站起玩家
				$newKey = 'upPlayers:'.$roomId.':'.$uid;
				$cache->rename($keys[0], $newKey);

				$standUpPlayers[$uid] = $cache->getArray($newKey);
			}

			//判断是否退出房间
			if( (int)$item['outRoom'] ) {
				$keysup = $cache->keys('upPlayers:*:'.$uid);
				if( count($keysup)>0 ) {
					if( !$standUpPlayers[$uid] ) {
						$standUpPlayers[$uid] = $cache->getArray($keysup[0]);
					}
					$cache->delete($keysup[0]);
				}
			}
			
		}

		//写入手牌数据
		if($handJson) {
			$hrs = ClubRoom::addHandData($clubId, $roomId, 3, array(), $handJson);
			if(!$hrs) {
				$DB->rollBack();  //回滚
				CMD(210, '手牌记录写入失败，clubId：'.$clubId.'，roomId：'.$roomId.'，内容：'.$handJson, 'msg');
			}
		}

		
		$DB->commit();    //提交


		//坐下者列表
		$players = array();

		//查找房间中坐下的人数
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			//读取玩家信息
			$userInfo = $cache->getArray($pItem);

			$players[$userInfo['uid']] = $userInfo;
		}

		//判断房间是否过期
		$roomOver = ClubRoom::getRoomCache($roomId);

		$chkData = array('players'=>$players, 'standUpPlayers'=>$standUpPlayers);
		if(!$roomOver) {
			$chkData['roomOver'] = 1;
		}

		CMD(200, $chkData);

	break;

	//游戏结算-牛牛
	case 'cowcow_checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

		//手牌记录
		$handJson = array();
		$handJson['bankerUid_COW'] 			= $_P['bankerUid_COW'];
		$handJson['maxCallBankerOdds_COW'] 	= $_P['maxCallBankerOdds_COW'];

		//庄家
		$bankerUid_COW = (int)$_P['bankerUid_COW'];

		//被站起的人员
		$standUpPlayers = array();

		$cache = getCache();

		$cfg = getCFG('data');

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		foreach($result as $key => $item) {

			$uid     = (int)$key;
			$baseBet = (int)$item['balance'];
			$bet     = (int)$item['balance'];
			$costBet = (int)$item['costBet'];

			$balance_COW = (int)$item['balance_COW'];

			//查找当前玩家缓存key
			$keys = $cache->keys('players:*:'.$uid);

			//通过房间id取俱乐部id
			$roomKeys = $cache->keys('roomInfo:*:'.$roomId);
			//取俱乐部id
			$roomKeyAry = explode(':',$roomKeys[0]);
			$clubId = (int)$roomKeyAry[1];
			if( $clubId<=0 ) {
				CMD(210, '俱乐部ID为空，Redis Key：'.$roomKeys[0], 'msg');
			}


			//读取房间抽水比例
			$costScale = $cache->getArray($roomKeys[0], 'costScale');


			//牛牛服务费
			$cow_scale = 0;
			if( ($uid==$bankerUid_COW) && ($balance_COW>0) ) {
				$cow_scale = floor($balance_COW * (float)$costScale);
			}

			//总服务费
			$scale = $cow_scale;
			if($scale>0) {
				
				//俱乐部加R币

				//按比例分给推广员
				$inviteUid = $DB->getValue('SELECT inviteUid FROM club_member WHERE clubId='.$clubId.' AND uid='.$uid.' AND status=1');
				//$inviteUid = ClubMember::getInviteUid($uid,$clubId);			//当前玩家的邀请人uid
				$cut = $DB->getValue('SELECT cut FROM club_member WHERE clubId='.$clubId.' AND uid='.$inviteUid);
				$oldcutNum = ClubMember::getCutNum($inviteUid,$clubId);
				//(float)$cut = ClubMember::getCut($inviteUid,$clubId);			//该邀请人的抽成比例
				(int)$cutNum = round($scale*$cut);								//应得抽成
				
				if($cutNum > 0){
					$newScale = floor($scale - $cutNum);						//扣除抽成剩余的服务费

					//推广员加推广费
					$sql = 'cutNum+'.$cutNum;
					$DB->exeSql('UPDATE club_member SET cutNum='.$sql.' WHERE clubId='.$clubId.' AND uid='.$inviteUid);

					//记录抽成进账
					ClubCounter::addCut($clubId,$roomId,$inviteUid,$uid,$cutNum);
				}else{
					$newScale = $scale;
				}
				if($newScale >0){
					$rb = Club::rb($clubId, $newScale);
					if($rb===false) {
						$DB->rollBack();  //回滚
						CMD(210, '俱乐部抽水失败，俱乐部ID：'.$clubId.'，服务费：'.$newScale, 'msg');
					}

					//记录服务费
					$rsc = ClubRoom::addScale($roomId, $uid, $newScale);
					if(!$rsc) {
						$DB->rollBack();  //回滚
						CMD(210, '统计服务费失败，房间ID：'.$roomId.'，UID：'.$uid.'，服务费：'.$newScale, 'msg');
					}
				}
				
			}


			$bet -= $scale;

			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);
			}

			if($bet) {

				//结算钱包
				$rs1 = ClubMember::coin($clubId, $uid, $bet, $add, true);
				if($rs1===false) {
					$DB->rollBack();  //回滚
					CMD(210, '钱包结算失败，俱乐部ID：'.$clubId.'，UID：'.$uid.'，结算筹码：'.$baseBet.'，服务费：'.$scale, 'msg');
				}

				//结算筹码
				$rs2 = ClubRoom::bet($roomId, $uid, $bet, $add, true);
				if($rs2===false) {
					$DB->rollBack();  //回滚
					CMD(210, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$baseBet.'，服务费：'.$scale, 'msg');
				}
			}

			//玩家手牌计数
			$handRs = ClubRoom::addHandNum($roomId, $uid, ($baseBet-$scale), $item['enterPool']);
			if(!$handRs) {
				$DB->rollBack();  //回滚
				CMD(210, '手牌累计失败，roomId：'.$roomId.'，UID：'.$uid, 'msg');
			}

			//记录手牌信息
			$handJson['players'][$uid] = array('uid'=>$uid,'cards_COW'=>$item['cards_COW'],'balance'=>($baseBet-$scale),'cardType_COW'=>$item['cardType_COW'],'cardTypePoint_COW'=>$item['cardTypePoint_COW'],'balance_COW'=>($balance_COW-$cow_scale));
	
			//记录幸运牌玩家 - 牛牛
			if( in_array($item['cardType_COW'], $cfg['clubLuckyTypeCOW']) ) {
				$luckyRs = ClubRoom::addLuckyData($clubId, $roomId, $uid, $item['cardType_COW'], $item['cards_COW']);
				if(!$luckyRs) {
					$DB->rollBack();  //回滚
					CMD(210, '幸运牌记录失败，clubId：'.$clubId.'，roomId：'.$roomId.'，UID：'.$uid.'，cardType_COW'.$item['cardType_COW'].'，cards_COW'.$item['cards_COW'], 'msg');
				}
			}

			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = ClubRoom::getBetData('id,curBet,preAddBet', $roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$rs3 = ClubRoom::plusPreBet($betData['id']);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(210, '合并预买入筹码失败，记录ID：'.$betData['id'], 'msg');
				}
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];

			}else{
				$curBet = (int)$betData['curBet'];
			}
			$playersCacheAry['bet'] = $curBet;
			$playersCacheAry['balance'] = (int)$item['balance']-$scale;

			//更新玩家延时次数
			if( (int)$item['delayUseNum']>0 ) {
				$curDelay = (int)User::get('card_delay_num', 'uid='.$uid);
				$curDelay = $curDelay - (int)$item['delayUseNum'];
				$curDelay = $curDelay>0 ? $curDelay : 0;

				User::edit(array('card_delay_num'=>$curDelay), $uid);
				$playersCacheAry['delayNum'] = $curDelay;
			}

			//更新玩家缓存
			if( count($keys)>0 ) {
				$cache->setArray($keys[0], $playersCacheAry, false);
			}

			//修改房间信息
			$editAry = array(
				'bankerSite'=>$bankerSite, //设置庄家
				'playTimeout'=>0 //本手游戏结束
			);
			$cache->setArray($roomKeys[0], $editAry, false);


			//==============================判断是否需要自动站起==========================//

			//读取房间信息
			$blindBet = $cache->getArray($roomKeys[0], 'blindBet');

			//判断当前筹码是否大等于房间盲注/底注
			if( count($keys)>0 && (($curBet<(int)$blindBet) || (int)$item['timeout']) ) {
				
				//修改key为站起玩家
				$newKey = 'upPlayers:'.$roomId.':'.$uid;
				$cache->rename($keys[0], $newKey);

				$standUpPlayers[$uid] = $cache->getArray($newKey);
			}

			//判断是否退出房间
			if( (int)$item['outRoom'] ) {
				$keysup = $cache->keys('upPlayers:*:'.$uid);
				if( count($keysup)>0 ) {
					if( !$standUpPlayers[$uid] ) {
						$standUpPlayers[$uid] = $cache->getArray($keysup[0]);
					}
					$cache->delete($keysup[0]);
				}
			}
			
		}

		//写入手牌数据
		if($handJson) {
			$hrs = ClubRoom::addHandData($clubId, $roomId, 4, array(), $handJson);
			if(!$hrs) {
				$DB->rollBack();  //回滚
				CMD(210, '手牌记录写入失败，clubId：'.$clubId.'，roomId：'.$roomId.'，内容：'.$handJson, 'msg');
			}
		}

		
		$DB->commit();    //提交


		//坐下者列表
		$players = array();

		//查找房间中坐下的人数
		$pKeys = $cache->keys('players:'.$roomId.':*');
		foreach($pKeys as $pItem) {
			//读取玩家信息
			$userInfo = $cache->getArray($pItem);

			$players[$userInfo['uid']] = $userInfo;
		}

		//判断房间是否过期
		$roomOver = ClubRoom::getRoomCache($roomId);

		$chkData = array('players'=>$players, 'standUpPlayers'=>$standUpPlayers);
		if(!$roomOver) {
			$chkData['roomOver'] = 1;
		}

		CMD(200, $chkData);

	break;

}