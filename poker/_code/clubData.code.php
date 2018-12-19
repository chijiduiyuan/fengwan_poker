<?php
/**
 * 俱乐部数据统计操作模块
 *
 * @author HJH
 * @version  2017-7-5
 */

if(!$_P){exit;}

switch ($_a) {

	//俱乐部详情-代理端
	case 'detail':
		
		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		if(!$gmSourceFlag) {
			ClubMember::isManage($clubId);
		}

		$info = Club::getLevelInfo($clubId);

		CMD(200, $info);

		break;


	//俱乐部数据
	case 'roomList':
		
		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		ClubMember::isManage($clubId);

		//桌子类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202);
		}

		//开始时间(时间戳)
		$startTime = (int)$_P['startTime'];
		if( $startTime<=0 ) {
			CMD(202);
		}

		//结束时间(时间戳)
		$endTime = (int)$_P['endTime'];
		if( $endTime<=0 ) {
			CMD(202);
		}

		//判断时间范围
		if(($endTime-$startTime) > 604800) {
			CMD(202);
		}

		//分页参数
		$roomId   = (int)$_P['roomId'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;
		$pageWhere = '';
		if($roomId) {
			$pageWhere = ' AND roomId<'.$roomId;
		}

		$where = 'clubId='.$clubId.' AND startTime>'.$startTime.' AND startTime<'.$endTime.' AND game=\''.$game.'\' AND status=1 '.$pageWhere.' ORDER BY roomId DESC LIMIT '.$pageSize;
		
		$list = ClubRoom::getRoomList('roomId,startTime,costScale,blindBet', $where);
		foreach($list as &$item) {
			$item['serviceCharge'] = ClubRoom::getSum( 'scale', 'roomId='.$item['roomId']);
		}

		//总开局数
		$startTotal = ClubRoom::getRoomCount('clubId='.$clubId.' AND startTime>'.$startTime.' AND startTime<'.$endTime.' AND game=\''.$game.'\' AND status=1');

		//服务费总计
		$DB = useDB();
		$serviceChargeTotal = (int)$DB->getValue('SELECT SUM(cru.scale) FROM club_room_user as cru LEFT JOIN club_room as cr ON cru.roomId=cr.roomId WHERE cru.clubId='.$clubId.' AND cr.startTime>'.$startTime.' AND cr.startTime<'.$endTime.' AND cr.game=\''.$game.'\' AND cr.status=1');

		//总手牌数
		$handTotal = (int)$DB->getValue('SELECT COUNT(crh.handId) FROM '.Fun::handHash($clubId).' as crh LEFT JOIN club_room as cr ON crh.roomId=cr.roomId WHERE crh.clubId='.$clubId.' AND cr.startTime>'.$startTime.' AND cr.startTime<'.$endTime.' AND cr.game=\''.$game.'\' AND cr.status=1');

		CMD(200, array('startTotal'=>$startTotal,'handTotal'=>$handTotal,'serviceChargeTotal'=>$serviceChargeTotal,'list'=>$list));

		break;



	//俱乐部数据-代理端
	case 'agentRoomList':
		
		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		if(!$gmSourceFlag) {
			ClubMember::isManage($clubId);
		}

		//开始时间(时间戳)
		$startTime = $_P['startTime'];
		if($startTime) {
			$startTime = strtotime($startTime);
		}else{
			$startTime = time()-3600*24*7;
		}

		//结束时间(时间戳)
		$endTime = $_P['endTime'];
		if($endTime) {
			$endTime = strtotime($endTime)+3600*24;
		}else{
			$endTime = time();
		}

		//判断时间范围
		// if(($endTime-$startTime) > 604800) {
		// 	CMD(202);
		// }

		//桌子类型 dzPoker=德州，cowWater=牛加水
		$whereGame = '';
		$game = $_P['game'];
		if($game) {
			$whereGame    = ' AND game=\''.$game.'\'';
			$whereGame_cr = ' AND cr.game=\''.$game.'\'';
		}

		//分页参数
		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?$_P['pageSize']:20;

		$limitForm = ($curPage-1)*$pageSize;

		$where = 'clubId='.$clubId.' AND startTime>'.$startTime.' AND startTime<'.$endTime.' AND status=1 '.$whereGame;
		
		$total = (int)ClubRoom::getRoomInfo('count(roomId)', $where);
		$list = ClubRoom::getRoomList('roomId,startTime,costScale,blindBet,game', $where.' ORDER BY roomId DESC LIMIT '.$limitForm.','.$pageSize);
		foreach($list as &$item) {
			$item['serviceCharge'] = ClubRoom::getSum( 'scale', 'roomId='.$item['roomId']);
		}

		//总开局数
		$startTotal = ClubRoom::getRoomCount('clubId='.$clubId.' AND startTime>'.$startTime.' AND startTime<'.$endTime.' AND status=1'.$whereGame);

		//服务费总计
		$DB = useDB();
		$serviceChargeTotal = (int)$DB->getValue('SELECT SUM(cru.scale) FROM club_room_user as cru LEFT JOIN club_room as cr ON cru.roomId=cr.roomId WHERE cru.clubId='.$clubId.' AND cr.startTime>'.$startTime.' AND cr.startTime<'.$endTime.' AND cr.status=1'.$whereGame_cr);

		//总手牌数
		$handTotal = (int)$DB->getValue('SELECT COUNT(crh.handId) FROM '.Fun::handHash($clubId).' as crh LEFT JOIN club_room as cr ON crh.roomId=cr.roomId WHERE crh.clubId='.$clubId.' AND cr.startTime>'.$startTime.' AND cr.startTime<'.$endTime.' AND cr.status=1'.$whereGame_cr);

		CMD(200, array('startTotal'=>$startTotal,'handTotal'=>$handTotal,'serviceChargeTotal'=>$serviceChargeTotal,'total'=>$total,'list'=>$list));

		break;



	//每局数据
	case 'roomInfo':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		ClubMember::isManage((int)ClubRoom::getRoomInfo('clubId','roomId='.$roomId));

		//分页参数
		$profitLoss = (int)$_P['profitLoss'];
		$id 		= (int)$_P['id'];
		$pageSize 	= (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND ((profitLoss='.$profitLoss.' AND id<'.$id.') OR profitLoss<'.$profitLoss.')';
		}

		$where = 'roomId='.$roomId.$pageWhere.' ORDER BY profitLoss DESC, id DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$list = ClubRoom::getBetList('id,uid,handNum,profitLoss', $where);
		foreach($list as &$item) {
			$item += User::getc(['nickname','avatar'], $item['uid']);
		}

		//总买入
		$buyBetTotal = ClubRoom::getSum('totalBuyBet', 'roomId='.$roomId);

		//服务费总计
		$serviceChargeTotal = ClubRoom::getSum('scale', 'roomId='.$roomId);

		//玩牌人数总计
		$userNumTotal = ClubRoom::getCount('roomId='.$roomId);

		//组织数据
		$cmdAry = array('buyBetTotal'=>$buyBetTotal,'serviceChargeTotal'=>$serviceChargeTotal,'userNumTotal'=>$userNumTotal,'list'=>$list);

		//读取房间信息
		$cmdAry += ClubRoom::getRoomInfo('blindBet,costScale,roomTime', 'roomId='.$roomId);

		CMD(200, $cmdAry);

		break;



	//每局数据-代理端用
	case 'agentRoomInfo':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		if(!$gmSourceFlag) {
			ClubMember::isManage((int)ClubRoom::getRoomInfo('clubId','roomId='.$roomId));
		}	

		//分页参数
		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?$_P['pageSize']:20;

		$limitForm = ($curPage-1)*$pageSize;

		$where = 'roomId='.$roomId;

		$total = (int)ClubRoom::getBetInfo('count(id)', $where);
		
		//读取牌局玩家列表
		$list = ClubRoom::getBetList('id,uid,handNum,profitLoss', $where.' ORDER BY profitLoss DESC, id DESC LIMIT '.$limitForm.','.$pageSize);
		foreach($list as &$item) {
			$item += User::getc(['nickname','avatar'], $item['uid']);
		}

		//总买入
		$buyBetTotal = ClubRoom::getSum('totalBuyBet', 'roomId='.$roomId);

		//服务费总计
		$serviceChargeTotal = ClubRoom::getSum('scale', 'roomId='.$roomId);

		//玩牌人数总计
		$userNumTotal = ClubRoom::getCount('roomId='.$roomId);

		//组织数据
		$cmdAry = array('buyBetTotal'=>$buyBetTotal,'serviceChargeTotal'=>$serviceChargeTotal,'userNumTotal'=>$userNumTotal,'total'=>$total,'list'=>$list);

		//读取房间信息
		$cmdAry += ClubRoom::getRoomInfo('blindBet,costScale,roomTime', 'roomId='.$roomId);

		CMD(200, $cmdAry);

		break;



	//玩家赢输
	case 'roomUser':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		ClubMember::isManage($clubId);

		//桌子类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202);
		}

		//开始时间(时间戳)
		$startTime = (int)$_P['startTime'];
		if( $startTime<=0 ) {
			CMD(202);
		}

		//结束时间(时间戳)
		$endTime = (int)$_P['endTime'];
		if( $endTime<=0 ) {
			CMD(202);
		}

		//判断时间范围
		if(($endTime-$startTime) >604800) {
			CMD(202);
		}

		//会员查找关键字
		$keyword = $_P['keyword'];
		$keywordWhere = '';
		if($keyword) {
			$keywordWhere = ' AND (user.uid="'.$keyword.'" OR user.nickname LIKE "%'.$keyword.'%")';
		}

		//分页参数
		$timeMin   = (int)$_P['timeMin'];
		$id 	   = (int)$_P['id'];
		$pageSize  = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;
		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND ((cr.startTime='.$timeMin.' AND cru.id<'.$id.') OR cr.startTime<'.$timeMin.')';
		}
		
		$where = 'cru.clubId='.$clubId.' AND cr.startTime>'.$startTime.' AND cr.startTime<'.$endTime.' AND cr.game=\''.$game.'\''.$pageWhere.$keywordWhere.' ORDER BY cr.startTime DESC, cru.id DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$field = 'cru.id,cru.uid,user.nickname,user.avatar,cr.game,cru.profitLoss,cr.startTime';
		$DB = useDB();
		$list = $DB->getList('SELECT '.$field.' FROM club_room_user AS cru LEFT JOIN club_room as cr ON cru.roomId = cr.roomId LEFT JOIN user ON cru.uid=user.uid WHERE '.$where);

		CMD(200, $list);

		break;



	//玩家赢输-代理端用
	case 'agentRoomUser':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		if(!$gmSourceFlag) {
			ClubMember::isManage($clubId);
		}

		//开始时间(时间戳)
		$startTime = $_P['startTime'];
		if($startTime) {
			$startTime = strtotime($startTime);
		}else{
			$startTime = time()-3600*24*7;
		}

		//结束时间(时间戳)
		$endTime = $_P['endTime'];
		if($endTime) {
			$endTime = strtotime($endTime)+3600*24;
		}else{
			$endTime = time();
		}

		//判断时间范围
		// if(($endTime-$startTime) >604800) {
		// 	CMD(202);
		// }

		$searchGame = '';

		//桌子类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if($game) {
			$searchGame .= ' AND cr.game=\''.$game.'\'';
		}

		//会员uid
		$uid = (int)$_P['uid'];
		if($uid) {
			$searchGame .= ' AND user.uid='.$uid;
		}

		//分页参数
		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?$_P['pageSize']:20;

		$limitForm = ($curPage-1)*$pageSize;
		
		$where = 'cru.clubId='.$clubId.' AND cr.startTime>='.$startTime.' AND cr.startTime<'.$endTime.$searchGame;


		$DB = useDB();

		$total = $DB->getValue('SELECT count(cru.id) FROM club_room_user AS cru LEFT JOIN club_room as cr ON cru.roomId = cr.roomId LEFT JOIN user ON cru.uid=user.uid WHERE '.$where);
		
		//读取牌局玩家列表
		$field = 'cru.id,cru.uid,user.nickname,user.avatar,cr.game,cru.profitLoss,cr.startTime';
		$list = $DB->getList('SELECT '.$field.' FROM club_room_user AS cru LEFT JOIN club_room as cr ON cru.roomId = cr.roomId LEFT JOIN user ON cru.uid=user.uid WHERE '.$where.' ORDER BY cr.startTime DESC, cru.id DESC LIMIT '.$limitForm.','.$pageSize);

		CMD(200, ['total'=>$total,'list'=>$list]);

		break;



	//幸运玩家
	case 'luckyUser':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		ClubMember::isManage($clubId);

		//判断俱乐部是否可查看幸运玩家
		$info = CLub::getLevelInfo($clubId);
		if( !(int)$info['luckyFlag'] ) {
			CMD(209);
		}

		//桌子类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202);
		}

		//开始时间(时间戳)
		$startTime = (int)$_P['startTime'];
		if( $startTime<=0 ) {
			CMD(202);
		}

		//结束时间(时间戳)
		$endTime = (int)$_P['endTime'];
		if( $endTime<=0 ) {
			CMD(202);
		}

		//判断时间范围
		if(($endTime-$startTime) >604800) {
			CMD(202);
		}

		//会员查找关键字
		$keyword = $_P['keyword'];
		$keywordWhere = '';
		if($keyword) {
			$keywordWhere = ' AND (user.uid="'.$keyword.'" OR user.nickname LIKE "%'.$keyword.'%")';
		}

		//分页参数
		$timeMin   = (int)$_P['timeMin'];
		$id 	   = (int)$_P['id'];
		$pageSize  = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;
		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND ((cr.startTime='.$timeMin.' AND crl.id<'.$id.') OR cr.startTime<'.$timeMin.')';
		}
		
		$where = 'crl.clubId='.$clubId.' AND cr.startTime>'.$startTime.' AND cr.startTime<'.$endTime.' AND cr.game=\''.$game.'\''.$pageWhere.$keywordWhere.' ORDER BY cr.startTime DESC, crl.id DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$field = 'crl.id,crl.uid,user.nickname,user.avatar,cr.game,cr.startTime,crl.cardList';
		$DB = useDB();
		$list = $DB->getList('SELECT '.$field.' FROM club_room_lucky AS crl LEFT JOIN club_room as cr ON crl.roomId = cr.roomId LEFT JOIN user ON crl.uid=user.uid WHERE '.$where);

		CMD(200, $list);

		break;



	//幸运玩家-代理端用
	case 'agentLuckyUser':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if( $clubId<=0 ) {
			CMD(202);
		}

		//判断是否为俱乐部管理者
		if(!$gmSourceFlag) {
			ClubMember::isManage($clubId);
		}

		//判断俱乐部是否可查看幸运玩家
		$info = Club::getLevelInfo($clubId);
		if( !(int)$info['luckyFlag'] ) {
			CMD(209);
		}

		//开始时间(时间戳)
		$startTime = $_P['startTime'];
		if($startTime) {
			$startTime = strtotime($startTime);
		}else{
			$startTime = time()-3600*24*7;
		}

		//结束时间(时间戳)
		$endTime = $_P['endTime'];
		if($endTime) {
			$endTime = strtotime($endTime)+3600*24;
		}else{
			$endTime = time();
		}

		//判断时间范围
		// if(($endTime-$startTime) >604800) {
		// 	CMD(202);
		// }

		$searchGame = '';

		//桌子类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if($game) {
			$searchGame .= ' AND cr.game=\''.$game.'\'';
		}

		//会员uid
		$uid = (int)$_P['uid'];
		if($uid) {
			$searchGame .= ' AND user.uid='.$uid;
		}

		//分页参数
		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?$_P['pageSize']:20;

		$limitForm = ($curPage-1)*$pageSize;
		
		$where = 'crl.clubId='.$clubId.' AND cr.startTime>'.$startTime.' AND cr.startTime<'.$endTime.$searchGame;

		$DB = useDB();

		$total = $DB->getValue('SELECT count(crl.id) FROM club_room_lucky AS crl LEFT JOIN club_room as cr ON crl.roomId = cr.roomId LEFT JOIN user ON crl.uid=user.uid WHERE '.$where);
		
		//读取牌局玩家列表
		$field = 'crl.id,crl.uid,user.nickname,user.avatar,cr.game,cr.startTime,crl.cardList';
		$list = $DB->getList('SELECT '.$field.' FROM club_room_lucky AS crl LEFT JOIN club_room as cr ON crl.roomId = cr.roomId LEFT JOIN user ON crl.uid=user.uid WHERE '.$where.' ORDER BY cr.startTime DESC, crl.id DESC LIMIT '.$limitForm.','.$pageSize);

		CMD(200, ['total'=>$total,'list'=>$list]);

		break;



	//牌局统计
	case 'roomEnd':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//分页参数
		$profitLoss = (int)$_P['profitLoss'];
		$id 		= (int)$_P['id'];
		$pageSize 	= (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND ((profitLoss='.$profitLoss.' AND id<'.$id.') OR profitLoss<'.$profitLoss.')';
		}

		$where = 'roomId='.$roomId.$pageWhere.' ORDER BY profitLoss DESC, id DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$list = ClubRoom::getBetList('id,uid,handNum,profitLoss,totalBuyBet', $where);
		foreach($list as &$item) {
			$item += User::getc(['nickname','avatar'], $item['uid']);
		}

		CMD(200, $list);

		break;



	//实时战绩
	case 'roomReal':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//分页参数
		$profitLoss = (int)$_P['profitLoss'];
		$id 		= (int)$_P['id'];
		$pageSize 	= (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND ((profitLoss='.$profitLoss.' AND id<'.$id.') OR profitLoss<'.$profitLoss.')';
		}

		$where = 'roomId='.$roomId.$pageWhere.' ORDER BY profitLoss DESC, id DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$list = ClubRoom::getBetList('id,uid,profitLoss,totalBuyBet', $where);
		foreach($list as &$item) {
			$item += User::getc(['nickname','avatar'], $item['uid']);
		}
		CMD(200, $list);

		break;



	//实时战绩(公共俱乐部房间)
	case 'roomRealPublic':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if(!$roomId) {
			CMD(202);
		}

		$cache = getCache();

		$list = array();
		$ary  = array();

		//查找坐下的玩家
		$playersKeyAry = $cache->keys('players:'.$roomId.':*');
		foreach($playersKeyAry as $item) {
			$keyAry = explode(':',$item);
			$uid = (int)$keyAry[2];

			//取买入数据
			$betInfo = $cache->getArray('clubRoomUser:'.$roomId.':'.$uid);

			$ary['nickname'] = User::getc('nickname', (int)$betInfo['uid']);
			$ary['totalBuyBet'] = (int)$betInfo['totalBuyBet'];
			$ary['profitLoss'] = (int)$betInfo['curBet']+(int)$betInfo['preAddBet']-(int)$betInfo['totalBuyBet'];

			$list[] = $ary;
		}

		CMD(200, $list);

		break;



	//牌局历史
	case 'handList':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//分页参数
		$handId   = (int)$_P['handId'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($handId) {
			$pageWhere = ' AND handId<'.$handId;
		}

		$where = 'roomId='.$roomId.$pageWhere.' AND JSON_CONTAINS(handJson,\'[{"uid":'.UID.'}]\') ORDER BY handId DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$clubId = ClubRoom::getRoomInfo('clubId', 'roomId='.$roomId);
		$list = ClubRoom::getHandList($clubId, 'handId,gameType,underCards,handJson', $where);
		foreach($list as &$item) {

			$item['underCards'] = json_decode($item['underCards'], true);

			$handJson = json_decode($item['handJson'], true);

			//查找当前玩家
			foreach($handJson as $curItem) {
				if($curItem['uid']==UID) {
					$item['cards'] = ($item['gameType']==1 ? $curItem['cards'] : array_merge($curItem['cards_SG'], $curItem['cards_COW']));
					$item['balance'] = $curItem['balance'];
					$item['nickname'] = User::getc('nickname');
				}else{
					$curItem['nickname'] = User::getc('nickname', (int)$curItem['uid']);
					unset( $curItem['uid'] );

					//德州输家隐牌
					if($item['gameType']==1) {
						if((int)$curItem['balance']<0 || (int)$curItem['compCardUserNum']==1) {
							$curItem['cards'] = array("0","0");
						}
					}

					//牛加水合并牌组
					if($item['gameType']==2) {
						$curItem['cards'] = array_merge($curItem['cards_SG'], $curItem['cards_COW']);
						unset( $curItem['cards_SG'] );
						unset( $curItem['cards_COW'] );
					}

					$item['otherList'][] = $curItem;
				}
			}
			
			unset( $item['handJson'] );
		}

		//统计总数
		$DB = useDB();
		$total = (int)$DB->getValue('SELECT COUNT(handId) FROM '.Fun::handHash($clubId).' WHERE roomId='.$roomId.' AND JSON_CONTAINS(handJson,\'[{"uid":'.UID.'}]\')');
		
		CMD(200, array('total'=>$total,'list'=>$list));

		break;



	//牌局历史(牛加水)
	case 'handListCow':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//分页参数
		$handId   = (int)$_P['handId'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($handId) {
			$pageWhere = ' AND handId<'.$handId;
		}

		$where = 'roomId='.$roomId.$pageWhere.' AND JSON_CONTAINS(handJson,\'{"players":{"'.UID.'":{"uid":'.UID.'}}}\') ORDER BY handId DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$clubId = ClubRoom::getRoomInfo('clubId', 'roomId='.$roomId);
		$list = ClubRoom::getHandList($clubId, 'handId,handJson', $where);
		foreach($list as &$item) {

			$handJson = json_decode($item['handJson'], true);

			//查找当前玩家
			foreach($handJson['players'] as &$curItem) {
				
				//玩家昵称
				$curItem['nickname'] = User::getc('nickname', (int)$curItem['uid']);
				
				//手牌
				$curItem['cards'] = array_merge($curItem['cards_SG'], $curItem['cards_COW']);
				unset( $curItem['cards_SG'] );
				unset( $curItem['cards_COW'] );
			}

			$item += $handJson;
			
			unset( $item['handJson'] );
		}

		//统计总数
		$DB = useDB();
		$total = (int)$DB->getValue('SELECT COUNT(handId) FROM '.Fun::handHash($clubId).' WHERE roomId='.$roomId.' AND JSON_CONTAINS(handJson,\'{"players":{"'.UID.'":{"uid":'.UID.'}}}\')');
		
		CMD(200, array('total'=>$total,'list'=>$list));

		break;

    //牌局历史(三公)
	case 'handListDucal':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//分页参数
		$handId   = (int)$_P['handId'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($handId) {
			$pageWhere = ' AND handId<'.$handId;
		}

		$where = 'roomId='.$roomId.$pageWhere.' AND JSON_CONTAINS(handJson,\'{"players":{"'.UID.'":{"uid":'.UID.'}}}\') ORDER BY handId DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$clubId = ClubRoom::getRoomInfo('clubId', 'roomId='.$roomId);
		$list = ClubRoom::getHandList($clubId, 'handId,handJson', $where);
		foreach($list as &$item) {

			$handJson = json_decode($item['handJson'], true);

			//查找当前玩家
			foreach($handJson['players'] as &$curItem) {
				
				//玩家昵称
				$curItem['nickname'] = User::getc('nickname', (int)$curItem['uid']);
				
				//手牌
				$curItem['cards'] = $curItem['cards_SG'];
				unset( $curItem['cards_SG'] );
			}

			$item += $handJson;
			
			unset( $item['handJson'] );
		}

		//统计总数
		$DB = useDB();
		$total = (int)$DB->getValue('SELECT COUNT(handId) FROM '.Fun::handHash($clubId).' WHERE roomId='.$roomId.' AND JSON_CONTAINS(handJson,\'{"players":{"'.UID.'":{"uid":'.UID.'}}}\')');
	
		CMD(200, array('total'=>$total,'list'=>$list));

		break;

	//牌局历史(牛加水)
	case 'handListCowcow':
		
		//房间id
		$roomId = (int)$_P['roomId'];
		if( $roomId<=0 ) {
			CMD(202);
		}

		//分页参数
		$handId   = (int)$_P['handId'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($handId) {
			$pageWhere = ' AND handId<'.$handId;
		}

		$where = 'roomId='.$roomId.$pageWhere.' AND JSON_CONTAINS(handJson,\'{"players":{"'.UID.'":{"uid":'.UID.'}}}\') ORDER BY handId DESC LIMIT '.$pageSize;
		
		//读取牌局玩家列表
		$clubId = ClubRoom::getRoomInfo('clubId', 'roomId='.$roomId);
		$list = ClubRoom::getHandList($clubId, 'handId,handJson', $where);
		foreach($list as &$item) {

			$handJson = json_decode($item['handJson'], true);

			//查找当前玩家
			foreach($handJson['players'] as &$curItem) {
				
				//玩家昵称
				$curItem['nickname'] = User::getc('nickname', (int)$curItem['uid']);
				
				//手牌
				$curItem['cards'] =  $curItem['cards_COW'];
				unset( $curItem['cards_COW'] );
			}

			$item += $handJson;
			
			unset( $item['handJson'] );
		}

		//统计总数
		$DB = useDB();
		$total = (int)$DB->getValue('SELECT COUNT(handId) FROM '.Fun::handHash($clubId).' WHERE roomId='.$roomId.' AND JSON_CONTAINS(handJson,\'{"players":{"'.UID.'":{"uid":'.UID.'}}}\')');
		
		CMD(200, array('total'=>$total,'list'=>$list));

		break;

	//生崖战绩
	case 'scoreInfo':

		//游戏类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202);
		}
		
		$gameType = $game=='dzPoker' ? 1 : ($game=='cowWater' ? 2 : ($game=='thriDucal' ? 3 : ($game=='cowcow' ? 4 : 0)));


		$data = array();

		//总牌局数
		$data['roomTotal'] = ClubRoom::getCount('uid='.UID.' AND gameType='.$gameType);

		//总手牌数
		$data['handTotal'] = ClubRoom::getSum( 'handNum', 'uid='.UID.' AND gameType='.$gameType);

		//胜率
		$handNumWin = ClubRoom::getSum( 'handNumWin', 'uid='.UID.' AND gameType='.$gameType);
		$data['winRate'] = $data['handTotal']>0 ? round($handNumWin / $data['handTotal'], 2) : 0;

		//入池率
		$handNumPool = ClubRoom::getSum( 'handNumPool', 'uid='.UID.' AND gameType='.$gameType);
		$data['poolRate'] = $data['handTotal']>0 ? round($handNumPool / $data['handTotal'], 2) : 0;
		
		CMD(200, $data);

		break;



	//生崖列表
	case 'scoreList':
		
		//游戏类型 dzPoker=德州，cowWater=牛加水
		$game = $_P['game'];
		if( $game!='dzPoker' && $game!='cowWater' && $game!='thriDucal' && $game!='cowcow') {
			CMD(202);
		}
		
		$gameType = $game=='dzPoker' ? 1 : ($game=='cowWater' ? 2 : ($game=='thriDucal' ? 3 : ($game=='cowcow' ? 4 : 0)));


		//分页参数
		$id = (int)$_P['id'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND id<'.$id;
		}


		$data = array();

		//总牌局数
		$data['roomTotal'] = ClubRoom::getCount('uid='.UID.' AND gameType='.$gameType);

		//总手牌数
		$data['handTotal'] = ClubRoom::getSum( 'handNum', 'uid='.UID.' AND gameType='.$gameType);

		//局列表
		$data['list'] = ClubRoom::getBetList('id,clubId,roomId,handNum,profitLoss', 'uid='.UID.' AND gameType='.$gameType.$pageWhere.' ORDER BY id DESC LIMIT '.$pageSize);
		foreach($data['list'] as &$item) {
			$item += ClubRoom::getRoomInfo('title as roomTitle,roomTime,blindBet,startTime', 'roomId='.$item['roomId']);
			$item += Club::getInfo($item['clubId'], 'title as clubTitle,avatar,time', false, false);

			unset( $item['clubId'] );
			unset( $item['roomId'] );
		}
		
		CMD(200, $data);

		break;

	
}