<?php
/**
 * 房间操作模块
 *
 * @author HJH
 * @version  2017-6-22
 */

if(!$_P){exit;}



//验证模块
$modAry = array(
	'multiple',   		//盲注/底注 倍数
	'create',   		//创建房间
	'list',   			//房间列表
	'getRoomTime',   	//取房间剩余时间
	'setRoomTime'   	//设置间剩余时间
);
if( !in_array($_a, $modAry) ) {
	CMD(202);
}



switch ($_a) {


	//盲注/底注 倍数
	case 'multiple':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		$cid = (int)Club::getInfo($clubId, 'cid', false);
		if( $cid<=0 ) {
			CMD(231);
		}

		//读取国家配置
		$blindBet = Country::getValue('blindBet','cid='.$cid);
		$blindBet = json_decode($blindBet);

		$cfg = getCFG('data');
		CMD(200, array('blindBet'=>$blindBet,'multiple'=>$cfg['multiple'],'costScale'=>$cfg['costScale'],'roomTime'=>$cfg['roomTime']));

		break;
	

	//创建房间
	case 'create':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//标题
		$title = $_P['title'];
		if( !$title ) {
			CMD(202);
		}

		//桌子类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202);
		}

		//牌局时间 单位秒
		$roomTime = (int)$_P['roomTime'];
		if( $roomTime<=0 ) {
			CMD(202);
		}

		//操作时间 单位秒
		$opTimeout = (int)$_P['opTimeout'];
		if( $opTimeout<=0 ) {
			CMD(202);
		}

		//抽成比例
		$costScale = (int)$_P['costScale'];
		if( $costScale<=0 ) {
			CMD(202);
		}else{
			$costScale = $costScale/100;
		}

		//玩家数 5人房,7人房 牛加水专有
		$playerNum = (int)$_P['playerNum'];
		if( $game=='cowWater' && $playerNum!=5 && $playerNum!=7 ) {
			CMD(202);
		}

		//盲注/底注
		$blindBet = (int)$_P['blindBet'];
		if( $blindBet<=0 ) {
			CMD(202);

		//判断盲注/底注有效性
		}else{
			$cid = (int)Club::getInfo($clubId, 'cid', false);
			if( $cid<=0 ) {
				CMD(231);
			}
			//读取国家配置
			$blindBetJson = Country::getValue('blindBet','cid='.$cid);
			$blindBetAry = json_decode($blindBetJson);
			if( !in_array($blindBet, $blindBetAry) ) {
				if($game=='dzPoker') {
					CMD(232);
				}elseif($game=='cowWater') {
					CMD(233);
				}else{
					CMD(202);
				}
			}
		}

		//最少筹码
		$minBet = (int)$_P['minBet'];
		if( $minBet<=0 ) {
			CMD(202);
		}

		//最多筹码
		$maxBet = (int)$_P['maxBet'];
		if( $game=='dzPoker' && $maxBet<=0 ) {
			CMD(202);
		}

		//授权买入 0=不允许 1=允许
		$enableBuy = (int)$_P['enableBuy'];
		if( $enableBuy<0 ) {
			CMD(202);
		}

		//密码
		$pw = $_P['pw'];


		//判断当前玩家是否有管理俱乐部桌子权限
		ClubMember::isPurview(CLUB_PURVIEW_DESK, $clubId);


		/*
		 * 判断玩家VIP卡、俱乐部等级 是否过期
		 */
		$clubNum = Club::getTotal();    //取当前玩家创建的俱乐部数量
		$vipInfo = User::getVipInfo();  //读取当前玩家的VIP卡权限(可创建俱乐部数由VIP卡权限决定)
		if($clubNum>$vipInfo['card_club_num']) {
			CMD(221, array('num'=>$clubNum,'limit'=>$vipInfo['card_club_num']));
		}

		//判断该俱乐部副代理数是否超出上限
		$curSubagentNum = (int)ClubMember::getSubagentCount($clubId);   //当前俱乐部副代理数
		$clubLevelInfo  = Club::getLevelInfo($clubId);  				//当前俱乐部可设置数上限
		if( $curSubagentNum > (int)$clubLevelInfo['subagentLimit'] ) {
			CMD(222, array('num'=>$curSubagentNum,'limit'=>$clubLevelInfo['subagentLimit']));
		}

		//判断该俱乐部会员数是否超出上限
		$curMemberNum = (int)ClubMember::getMemberCount($clubId);       //当前俱乐部会员数
		$clubLevelInfo  = Club::getLevelInfo($clubId);  				//当前俱乐部可加入数上限
		if( $curMemberNum > (int)$clubLevelInfo['memberLimit'] ) {
			CMD(223, array('num'=>$curMemberNum,'limit'=>$clubLevelInfo['memberLimit']));
		}


		//组织数据
		$ary = array(
			'clubId'      => $clubId,
			'title'       => $title,
			'game'        => $game,
			'roomTime'    => $roomTime,
			'opTimeout'   => $opTimeout,
			'costScale'   => $costScale,
			'playerNum'   => $playerNum,
			'blindBet'    => $blindBet,
			'minBet'      => $minBet,
			'maxBet'      => $maxBet,
			'enableBuy'   => $enableBuy,
			'createTime'  => time()
		);
		
		if($pw) {
			$ary['pw'] = $pw;
		}

		//写入数据库
		$roomId = (int)ClubRoom::createRoom($ary);
		if(!$roomId) {
			CMD(208);
		}
		
		//补充字段
		$ary['status'] = 0;
		$ary['playTimeout'] = 0;
		$ary['roomId'] = $roomId;
		$ary['public'] = 0;
		$ary['bankerSite'] = -1;

		//牛加水处理
		$ary['baseBet'] = $ary['blindBet'];
		$ary['siteNum'] = $ary['playerNum'];

		//读取配置的超时时间
		$cfg = getCFG('data');
		$t = (int)$cfg['roomTimeOUt'];
		
		//写入缓存
		$cache = getCache();
		$cache->setArray('roomInfo:'.$clubId.':'.$roomId, $ary, $t);
		
		break;


	//房间列表
	case 'list':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if(!$clubId) {
			CMD(202);
		}

		//房间类型
		$game = $_P['game'];
		if( !$game ) {
			CMD(202);
		}

		//查找本俱乐部房间key
		$cache = getCache();
		$keys = $cache->keys('roomInfo:'.$clubId.':*');

		$list = array();
		sort($keys);
		foreach($keys as $item) {

			//读取房间信息
			$roomInfo = $cache->getArray($item, ['roomId','pw','title','game','blindBet','minBet','roomTime','playerNum','status']);

			//判断类型
			if( $roomInfo['game']!=$game ) {
				continue;
			}

			//获取房间剩余缓存过期时间
			if($clubId>0) {
				$timeFlag = (int)$roomInfo['status'] ? 3600 : 0;
				$roomttl = $cache->ttl($item);
				if($roomttl<=$timeFlag) {
					continue;
				}
			}
			
			//是否有密码
			if($roomInfo['pw']) {
				$roomInfo['pw'] = 1;
			}else{
				$roomInfo['pw'] = 0;
			}

			//查找房间中的人数
			$roomInfo['roomPlayers'] = count($cache->keys('players:'.$roomInfo['roomId'].':*'));

			//合并房间信息到列表
			$list[] = $roomInfo;
		}

		CMD(200, $list);

		break;


	//取房间剩余时间
	case 'getRoomTime':

		//房间id
		$roomId = (int)$_P['roomId'];
		if(!$roomId) {
			CMD(202);
		}

		$roomInfo = ClubRoom::getRoomCache($roomId);

		CMD(200, ['time'=>(int)$roomInfo['roomTime']]);

		break;


	//设置间剩余时间
	case 'setRoomTime':

		//房间id
		$roomId = (int)$_P['roomId'];
		if(!$roomId) {
			CMD(202);
		}

		//取俱乐部id
		$clubId = ClubRoom::getClubId($roomId);
		if($clubId<=0) {
			CMD(216);
		}

		//判断当前玩家是否有管理俱乐部桌子权限
		ClubMember::isPurview(CLUB_PURVIEW_DESK, $clubId);

		//房间key
		$roomKey = 'roomInfo:'.$clubId.':'.$roomId;

		$cache = getCache();

		//读取房间信息
		$roomInfo = $cache->getArray($roomKey);
		if(!$roomInfo) {
			CMD(216);
		}

		//缓存过期时间
		if( (int)$roomInfo['status'] ) {
			$t = 3600;
		}else{
			$t = 0;
		}
		

		//消息通知
		$uidAry = ClubRoom::getUID($roomId);
		$data = array('roomId'=>$roomId);

		if($t>0) {
			$cache->expire($roomKey, $t);
		}else{
			ClubRoom::delRoomCache($roomId);
		}

		Fun::addNotify($uidAry, $data);

		break;

}