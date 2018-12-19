<?php
/**
 * node公共俱乐部操作模块
 *
 * @author HJH
 * @version  2017-7-18
 */

if(!$_P){exit;}



//验证模块
$modAry = array(
	'getRoom',   		//取房间信息
	'play',				//开始游戏
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

		$cache = getCache();

		//查找当前玩家是否已在房间中坐下
		if( count($cache->keys('players:*:'.UID))>0 ) {

			//判断房间是否过期
			$rId = ClubRoom::getRoomId();
			$rInfo = ClubRoom::getRoomCache($rId);
			if($rInfo) {
				if($rId==$roomId) {
					CMD(225, array('roomInfo'=>$rInfo));
				}else{
					CMD(210, '玩家已在其它房间座位上，玩家ID：'.UID, 'msg');
				}
			}
		}

		//查找当前玩家是否已在房间中旁观
		if( count($cache->keys('upPlayers:*:'.UID))>0 ) {

			//判断房间是否过期
			$rId = ClubRoom::getRoomId();
			$rInfo = ClubRoom::getRoomCache($rId);
			if($rInfo) {
				if($rId==$roomId) {
					CMD(225, array('roomInfo'=>$rInfo));
				}else{
					CMD(210, '玩家已在其它房间旁观中，玩家ID：'.UID, 'msg');
				}
			}
		}

		//查找房间key
		$keys = $cache->keys('roomInfo:*:'.$roomId);

		//判断房间是否存在
		if( count($keys)<=0 ) {
			CMD(216, '房间不存在，房间ID：'.$roomId, 'msg');
		}
		foreach($keys as $item) {

			//读取房间信息
			$roomInfo = $cache->getArray($item);

			//判断房间类型
			if($roomInfo['game']!=$game) {
				CMD(210, '房间类型不匹配，房间ID：'.$roomId.'，'.$roomInfo['game'].'====='.$game, 'msg');
			}

			//判断玩家金币是否小于房间要求下限,则不允许进入
			$gold = (int)User::get('gold');
			if( $gold<(int)$roomInfo['minBet'] ) {
				CMD(224);
			}

			//加入房间
			$info = User::get('nickname,avatar,pos') + User::getVipInfo();
			$userAry = array(
				'uid'			=> UID,
				'nickname'		=> $info['nickname'],
				'avatar'		=> $info['avatar'],
				'pos_x'			=> $info['pos_x'],
				'pos_y'			=> $info['pos_y'],
				'delayNum'		=> $info['card_delay_num'],
				'bet'			=> 0,
				'startEnable'	=> 0
			);
			$cache->setArray('upPlayers:'.$roomId.':'.UID, $userAry, false);

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
			if(!$roomId) {
				CMD(210, '房间ID为空，Redis player Key：'.$upItem, 'msg');
			}

			$uidArr = ClubRoom::getUID($roomId);
			
			//删除缓存中旁观的玩家
			$cache->delete($upItem);

			//删除玩家买入缓存数据
			$cache->delete('clubRoomUser:'.$roomId.':'.UID);

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
			if(!$roomId) {
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
			$betData = PublicRoom::getBetData($roomId);
			if((int)$betData['totalBuyBet']<=0) {
				CMD(214);

			//合并预买入筹码
			}elseif((int)$betData['preAddBet']>0) {
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];
				$rs = PublicRoom::plusPreBet($curBet, $roomId);
				if(!$rs) {
					CMD(210, '合并预买入筹码失败，roomId：'.$roomId, 'msg');
				}

			}else{
				$curBet = (int)$betData['curBet'];
			}


			//查找房间key
			$keys = $cache->keys('roomInfo:*:'.$roomId);
			if( count($keys)<=0 ) {
				CMD(210, '房间不存在，房间ID：'.$roomId, 'msg');
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
			if(!$roomId) {
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

		//判断玩家是否在房间中
		$roomId = ClubRoom::getRoomId();
		if(!$roomId) {
			CMD(210, '玩家不在房间中，玩家UID：'.UID, 'msg');
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

		//读取玩家当前筹码
		$betData = PublicRoom::getBetData($roomId);
		$totalBet = $bet + (int)$betData['curBet'];
		
		//判断筹码是否在范围内
		if( $totalBet<(int)$roomInfo['minBet'] ) {
			CMD(210, '买入后筹码小于下限，买入筹码：'.$bet.'，原有筹码：'.$betData['curBet'].'，筹码下限：'.$roomInfo['minBet'], 'msg');
		}
		if( (int)$roomInfo['maxBet']>0 && $totalBet>(int)$roomInfo['maxBet'] ) {
			CMD(210, '买入后筹码大于上限，买入筹码：'.$bet.'，原有筹码：'.$betData['curBet'].'，筹码上限：'.$roomInfo['maxBet'], 'msg');
		}

		//判断筹码是否有超出玩家金币
		$gold = (int)User::get('gold');
		if( $totalBet>$gold ) {
			CMD(224);
		}

		if( (int)$betData['totalBuyBet']>0 ) {
			$preAddBet = (int)$betData['preAddBet'] + $bet;
			$totalBuyBet = (int)$betData['totalBuyBet'] + $bet;
			$rs = PublicRoom::preAddBet($preAddBet, $totalBuyBet, $roomId);

		}else{
			$gameType = $roomInfo['game']=='dzPoker' ? 1 : ($roomInfo['game']=='cowWater' ? 2 : ($roomInfo['game']=='thriDucal' ? 3 : ($roomInfo['game']=='cowcow' ? 4 : 0)));
			$rs = PublicRoom::createAddBet($gameType, $bet, $roomId);
		}

		if(!$rs) {
			CMD(210, '预买入失败，买入数：'.$bet.'，房间ID :'.$roomId, 'msg');
		}

		$uidArr = ClubRoom::getUID($roomId);
		
		CMD(200, array('uidArr'=>$uidArr));

		break;


	//德州结算
	case 'checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

		//被站起的人员
		$standUpPlayers = array();

		$cache = getCache();

		$cfg = getCFG('data');

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		foreach($result as $key => $item) {

			$uid = (int)$key;
			$bet = (int)$item['balance'];
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
			if(!$clubId) {
				CMD(210, '俱乐部ID为空，Redis Key：'.$roomKeys[0], 'msg');
			}

			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);
			}

			if($bet) {

				//服务费
				$scale = 0;

				if($add) {

					//读取房间信息
					$costScale = $cache->getArray($roomKeys[0], 'costScale');
					$scale = floor(($bet+$costBet) * (float)$costScale);
				}

				//判断服务费是否超过赢的钱
				$tbet = $bet-$scale;
				if($tbet<0) {
					$add = false;
					$tbet = abs($tbet);
				}

				if($tbet>0) {
					
					//结算金币
					$rs1 = User::gold($tbet, $add, $uid, true);
					if($rs1===false) {
						$DB->rollBack();  //回滚
						CMD(210, '钱包结算失败，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
					}

					//结算筹码
					$rs2 = PublicRoom::bet($roomId, $uid, $tbet, $add, true);
					if($rs2===false) {
						$DB->rollBack();  //回滚
						CMD(210, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
					}
				}
			}

			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = PublicRoom::getBetData($roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];
				$rs3 = PublicRoom::plusPreBet($curBet, $roomId, $uid);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(210, '合并预买入筹码失败，curBet：'.$curBet.'，roomId：'.$roomId.'，uid：'.$uid, 'msg');
				}

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

		CMD(200, array('players'=>$players, 'standUpPlayers'=>$standUpPlayers));

		break;



	//牛加水结算
	case 'cow_checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

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

			$uid = (int)$key;
			$bet = (int)$item['balance'];
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
			if(!$clubId) {
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

			$bet -= $scale;

			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);
			}

			if($bet) {

				//结算金币
				$rs1 = User::gold($bet, $add, $uid, true);
				if($rs1===false) {
					$DB->rollBack();  //回滚
					CMD(210, '钱包结算失败，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
				}

				//结算筹码
				$rs2 = PublicRoom::bet($roomId, $uid, $bet, $add, true);
				if($rs2===false) {
					$DB->rollBack();  //回滚
					CMD(210, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
				}
			}

			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = PublicRoom::getBetData($roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];
				$rs3 = PublicRoom::plusPreBet($curBet, $roomId, $uid);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(210, '合并预买入筹码失败，curBet：'.$curBet.'，roomId：'.$roomId.'，uid：'.$uid, 'msg');
				}

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

		CMD(200, array('players'=>$players, 'standUpPlayers'=>$standUpPlayers));

		break;

	//三公结算
	case 'thri_checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

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

			$uid = (int)$key;
			$bet = (int)$item['balance'];
			$costBet = (int)$item['costBet'];

			$balance_SG  = (int)$item['balance_SG'];

			//查找当前玩家缓存key
			$keys = $cache->keys('players:*:'.$uid);

			//通过房间id取俱乐部id
			$roomKeys = $cache->keys('roomInfo:*:'.$roomId);
			//取俱乐部id
			$roomKeyAry = explode(':',$roomKeys[0]);
			$clubId = (int)$roomKeyAry[1];
			if(!$clubId) {
				CMD(210, '俱乐部ID为空，Redis Key：'.$roomKeys[0], 'msg');
			}


			//读取房间抽水比例
			$costScale = $cache->getArray($roomKeys[0], 'costScale');


			//三公服务费
			$sg_scale = 0;
			if( ($uid==$bankerUid_SG) && ($balance_SG>0) ) {
				$sg_scale = floor($balance_SG * (float)$costScale);
			}


			//总服务费
			$scale = $sg_scale;

			$bet -= $scale;

			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);
			}

			if($bet) {

				//结算金币
				$rs1 = User::gold($bet, $add, $uid, true);
				if($rs1===false) {
					$DB->rollBack();  //回滚
					CMD(210, '钱包结算失败，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
				}

				//结算筹码
				$rs2 = PublicRoom::bet($roomId, $uid, $bet, $add, true);
				if($rs2===false) {
					$DB->rollBack();  //回滚
					CMD(210, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
				}
			}

			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = PublicRoom::getBetData($roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];
				$rs3 = PublicRoom::plusPreBet($curBet, $roomId, $uid);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(210, '合并预买入筹码失败，curBet：'.$curBet.'，roomId：'.$roomId.'，uid：'.$uid, 'msg');
				}

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

		CMD(200, array('players'=>$players, 'standUpPlayers'=>$standUpPlayers));

	break;


	//牛牛结算
	case 'cowcow_checkout':

		//获取参数
		$result = $_P['result'];
		if(!$result || !is_array($result) || count($result)<=0) {
			CMD(202, 'result参数非法:'.$result, 'msg');
		}

		//庄家参数
		$bankerSite = (int)$_P['bankerSite'];

		//庄家
		$bankerUid_COW = (int)$_P['bankerUid_COW'];

		//被站起的人员
		$standUpPlayers = array();

		$cache = getCache();

		$cfg = getCFG('data');

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		foreach($result as $key => $item) {

			$uid = (int)$key;
			$bet = (int)$item['balance'];
			$costBet = (int)$item['costBet'];

			$balance_COW = (int)$item['balance_COW'];

			//查找当前玩家缓存key
			$keys = $cache->keys('players:*:'.$uid);

			//通过房间id取俱乐部id
			$roomKeys = $cache->keys('roomInfo:*:'.$roomId);
			//取俱乐部id
			$roomKeyAry = explode(':',$roomKeys[0]);
			$clubId = (int)$roomKeyAry[1];
			if(!$clubId) {
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
			$scale =  $cow_scale;

			$bet -= $scale;

			$add = true;
			if($bet<0) {
				$add = false;
				$bet = abs($bet);
			}

			if($bet) {

				//结算金币
				$rs1 = User::gold($bet, $add, $uid, true);
				if($rs1===false) {
					$DB->rollBack();  //回滚
					CMD(210, '钱包结算失败，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
				}

				//结算筹码
				$rs2 = PublicRoom::bet($roomId, $uid, $bet, $add, true);
				if($rs2===false) {
					$DB->rollBack();  //回滚
					CMD(210, '筹码结算失败，房间ID：'.$roomId.'，UID：'.$uid.'，结算筹码：'.$bet.'，服务费：'.$scale, 'msg');
				}
			}

			$playersCacheAry = array();
			
			//读取玩家当前筹码
			$betData = PublicRoom::getBetData($roomId, $uid);
			if( (int)$betData['preAddBet']>0 ) {
				$curBet = (int)$betData['curBet'] + (int)$betData['preAddBet'];
				$rs3 = PublicRoom::plusPreBet($curBet, $roomId, $uid);
				if(!$rs3) {
					$DB->rollBack();  //回滚
					CMD(210, '合并预买入筹码失败，curBet：'.$curBet.'，roomId：'.$roomId.'，uid：'.$uid, 'msg');
				}

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

		CMD(200, array('players'=>$players, 'standUpPlayers'=>$standUpPlayers));

	break;

}