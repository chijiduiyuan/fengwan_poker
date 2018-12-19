<?php
/**
 * node公共操作模块
 *
 * @author HJH
 * @version  2017-7-19
 */

if(!$_P){exit;}



//验证模块
$modAry = array(
	'getMsg',  				//消息通知
	'nodeLogin',   			//登录验证
	'publicInRoomSiteDown',	//公共大厅一键坐下
	'gameProcessNum'		//定时提交游戏进程数
	
);
if( !in_array($op, $modAry) ) {
	CMD(202);
}



switch ($op) {


	//定时提交游戏进程数
	case 'gameProcessNum':

		$ary = array(
			'sid'  => $_P['sid'],
			'game' => $_P['game'],
			'num'  => $_P['num']
		);
		
		$cache = getCache();
		$cache->setArray('process_num_'.$_P['sid'], $ary);

		break;



	//消息通知
	case 'getMsg':
		CMD(200, Fun::getNotify());
		break;


	//登录验证
	case 'nodeLogin':

		$sessionId = $_P['sessionId'];

		if (!$sessionId) {
			CMD(202, '缺少sessionId');
		}

		$cache = getCache();

		$isLogin =  $cache->exists($sessionId);      
        
        if(!$isLogin){
        	CMD(201);
        }

		//判断uid
		if(!UID) {
			CMD(202, '缺少UID：'.UID, 'msg');
		}

		//token
		$token = $_P['token'];
		if(!$token) {
			CMD(202);
		}

		//读取玩家信息
		$userInfo = User::get('token,uid,nickname,avatar');
		if($token != $userInfo['token']) {
			CMD(205);
		}else{
			unset($userInfo['token']);
		}

		$ary = array('userInfo'=>$userInfo);

		//读取房间信息
		$rId = ClubRoom::getRoomId();
		if($rId) {
			$roomInfo = ClubRoom::getRoomCache($rId);
			if($roomInfo) {
				$ary['roomInfo'] = $roomInfo;
			}
		}

		CMD(200, $ary);

		break;


	//公共大厅一键坐下
	case 'publicInRoomSiteDown':

		//判断uid
		if(!UID) {
			CMD(202, '缺少UID：'.UID, 'msg');
		}
		
		//房间类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202);
		}

		$cache = getCache();

		//查找当前玩家是否已在房间中坐下
		if( count($cache->keys('players:*:'.UID))>0 ) {

			//判断房间是否过期
			$rId = ClubRoom::getRoomId(UID);
			if( ClubRoom::getRoomCache($rId) ) {
				CMD(210, '玩家已在座位上，玩家ID：'.UID, 'msg');
			}
		}

		//查找当前玩家是否已在房间中旁观
		if( count($cache->keys('upPlayers:*:'.UID))>0 ) {

			//判断房间是否过期
			$rId = ClubRoom::getRoomId(UID);
			if( ClubRoom::getRoomCache($rId) ) {
				CMD(210, '玩家已在房间旁观，玩家ID：'.UID, 'msg');
			}
		}

		//玩家数据
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

		$roomId = 0;
		$goldFlag = false;

		//查找公共俱乐部房间
		$keys = $cache->keys('roomInfo:-1:*');

		//排序
		sort($keys);
		foreach($keys as $item) {

			//读取房间信息
			$roomInfo = $cache->getArray($item);

			//判断房间类型
			if( $roomInfo['game']!=$game ) {
				continue;
			}

			//判断玩家金币是否小于房间要求下限,则不允许进入
			$gold = (int)User::get('gold');
			if( $gold<(int)$roomInfo['minBet'] ) {

				if(!$roomId || !$goldFlag) {
					$goldFlag = true;
					$roomId = (int)$roomInfo['roomId'];
				}

				continue;
			}

			//判断房间人数是否已满
			$playersKeyAry = $cache->keys('players:'.$roomInfo['roomId'].':*');
			if( count($playersKeyAry) >= (int)$roomInfo['playerNum'] ) {

				if(!$roomId) {
					$roomId = (int)$roomInfo['roomId'];
				}

				continue;
			}

			//房间未满取坐位号
			for($i=0; $i < (int)$roomInfo['playerNum']; $i++) {
				
				$flag = true;
				
				foreach ($playersKeyAry as $keyItem) {
					$playerInfo = $cache->getArray($keyItem);
					if((int)$playerInfo['site']==$i) {
						$flag = false;
						break;
					}
				}

				if($flag) {

					$roomId = (int)$roomInfo['roomId'];

					//买入筹码
					$gameType = $roomInfo['game']=='dzPoker' ? 1 : ($roomInfo['game']=='cowWater' ? 2 : ($roomInfo['game']=='thriDucal' ? 3 : ($roomInfo['game']=='cowcow' ? 4 : 0)));

					PublicRoom::createAddBet($gameType, (int)$roomInfo['minBet'], $roomInfo['roomId']);

					//找到坐位并坐下
					$userAry['bet']  = (int)$roomInfo['minBet'];
					$userAry['site'] = $i;

					$cache->setArray('players:'.$roomInfo['roomId'].':'.UID, $userAry, false);

					break;
				}
			}

			break;
		}

		//金币不足或无空位则进入房间旁观
		if(!$flag && $roomId) {
			$cache->setArray('upPlayers:'.$roomId.':'.UID, $userAry, false);
		}

		//读取房间信息
		if($roomId) {
			CMD(200, array('roomInfo'=>ClubRoom::getRoomCache($roomId)));
		}else{
			CMD(216);
		}

		break;

}