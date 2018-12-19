<?php
/**
 * 俱乐部操作模块
 *
 * @author HJH
 * @version  2017-6-10
 */

if(!$_P){exit;}

switch ($_a) {



	//俱乐部列表(代理端用)
	case 'agentList':

		//分页参数
		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?$_P['pageSize']:20;

		$list = Club::agentGetList($curPage, $pageSize);
		foreach($list as &$info) {
			if( (int)$info['expir']<=time()) {
				$info['level'] = 0;
			}
			
			//转换过期时间
			Fun::ttod($info);
		}

		CMD(200, $list);

		break;
	


	//俱乐部列表
	case 'list':

		//分页参数
		$manage   = (int)$_P['manage'];
		$id 	  = (int)$_P['id'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$list = Club::getList($manage, $id, $pageSize);
		foreach($list as &$info) {
			if( (int)$info['expir']<=time()) {
				$info['level'] = 0;
			}
			
			//转换过期时间
			Fun::ttod($info);
		}

		CMD(200, $list);

		break;



	//创建俱乐部
	case 'create':

		//取当前玩家创建的俱乐部数量
		$clubNum = Club::getTotal();

		//读取当前玩家的VIP卡权限(可创建俱乐部数由VIP卡权限决定)
		$vipInfo = User::getVipInfo();

		//判断是否超出上限
		if((int)$clubNum>=(int)$vipInfo['card_club_num']) {
			CMD(207);
		}

		//获取参数
		$cid   = (int)$_P['cid'];
		$title = trim($_P['title']);
		$intro = trim($_P['intro']);
		
		//验证参数
		if(!$cid || !$title || !$intro) {
			CMD(202);
		}


		//生成俱乐部数据
		$clubInfo = array(
			'create_uid'	 => UID,
			'create_nickname'=> User::get('nickname','uid='.UID),
			'cid'   	=> $cid,
			'title' 	=> $title,
			'intro' 	=> $intro			
		);

		$clubId = Club::create($clubInfo);
		if(!$clubId) {
			CMD(208);
		}

		CMD(200, array('clubId'=>$clubId));

		break;



	//查找俱乐部
	case 'search':
		
		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if($clubId<=0) {
			CMD(202);
		}

		//读取俱乐部基础信息
		$clubInfo = Club::getInfo($clubId, 'clubId,title,avatar,level,intro,expir,memberLimit');
		if(!$clubInfo) {
			CMD(210);
		}

		//读取俱乐部现有成员数
		$clubInfo['memberCount'] = ClubMember::getMemberCount($clubId);

		//读取俱乐部创建者信息
		$clubInfo['creator'] = Club::getCreator($clubId);

		unset( $clubInfo['expir'] );
		
		CMD(200, $clubInfo);

		break;



	//俱乐部详情
	case 'detail':

		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if($clubId<=0) {
			CMD(202);
		}

		//checkip 非管理员
		if( !ClubMember::isManage($clubId, true) ) {
			$info = Club::getInfo($clubId, 'cid,checkip', false);
			if(!$info) {
				CMD(231);
			}
			if( (int)$info['checkip'] ) {
				//读取国家配置
				$clubCountry = Country::getValue('country','cid='.(int)$info['cid']);
				$DB = useDB();
				$sql = 'SELECT c.country FROM ip2nationcountries c, ip2nation i WHERE i.ip<INET_ATON("'.$_SERVER['REMOTE_ADDR'].'") AND c.code = i.country ORDER BY i.ip DESC';
				$country = $DB->getValue($sql);
				if( $clubCountry!=$country ) {
					CMD(234);
				}
			}
		}
        $detailInfo = Club::getDetail($clubId);
        if ($detailInfo) {
        	$cfg = getCFG('data');
        	$detailInfo['recycleRbScale'] = $cfg['recycleRbScale'];
        	$detailInfo['sendRbScale'] = $cfg['sendRbScale'];
        }

		CMD(200, $detailInfo);

		break;



	//编辑俱乐部
	case 'edit':

		$clubId = (int)$_P['clubId'];   //俱乐部id
		$title  = $_P['title'];         //俱乐部名称
		$intro  = $_P['intro'];         //俱乐部公告
		$avatar = $_P['avatar'];        //俱乐部头像


		//验证参数
		if( !$clubId || (!$avatar && (!$title || !$intro)) ) {
			CMD(202);
		}

		//判断当前玩家是否有编辑该俱乐部的权限
		ClubMember::isPurview(CLUB_PURVIEW_EDIT, $clubId);

		$ary = array();

		if($title && $intro) {
			$ary['title'] = $title;
			$ary['intro'] = $intro;
		}

		if($avatar) {
			$ary['avatar'] = $avatar;
		}

		if($ary) {
			Club::edit($ary, $clubId);
		}

		break;



	//解散俱乐部
	case 'delete':
		
		$clubId = (int)$_P['clubId'];  //俱乐部id

		//验证参数
		if( !$clubId ) {
			CMD(202);
		}
		
		//解散俱乐部
		if( !Club::delete($clubId) ) {
			CMD(210);
		}

		break;



	//钻石兑换俱乐部币
	case 'torb':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$rmb_num = (int)$_P['rmb'];     //要兑换的钻石数量

		//验证参数
		if( $clubId<=0 || $rmb_num<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否有兑换俱乐部币的权限
		ClubMember::isPurview(CLUB_PURVIEW_BUYRB, $clubId);

		$ary = array();

		$rmb = User::rmb($rmb_num, false);
		if($rmb!==false) {
			
			$cid = (int)Club::getInfo($clubId, 'cid', false);
			if($cid<=0) {
				CMD(231);
			}
			//读取国家配置
			$rmbToclubRb = (int)Country::getValue('rmbToclubRb','cid='.$cid);
			$rb_num = $rmb_num * $rmbToclubRb;
			$rb  = Club::rb($clubId, $rb_num);

			$ary['rmb'] = $rmb;
			$ary['rb']  = $rb;

			CMD(200, $ary);

		}else{
			CMD(206);
		}



	//俱乐部币兑换钻石
	case 'tormb':

		$clubId  = (int)$_P['clubId'];  //俱乐部id
		$rb_num  = (int)$_P['rb'];      //要兑换的俱乐部(R币)数量

		//验证参数
		if( $clubId<=0 || $rb_num<=0 ) {
			CMD(202);
		}

		//判断当前玩家是否有兑换俱乐部币的权限
		ClubMember::isPurview(CLUB_PURVIEW_BUYRB, $clubId);

		$ary = array();

		$rb = Club::rb($clubId, $rb_num, false);
		if($rb!==false) {
			
			$cid = (int)Club::getInfo($clubId, 'cid', false);
			if($cid<=0) {
				CMD(231);
			}
			//读取国家配置
			$rmbToclubRb = (int)Country::getValue('rmbToclubRb','cid='.$cid);
			if($rmbToclubRb<=0) {
				CMD(231);
			}

			$rmb_num = floor($rb_num / $rmbToclubRb);
			$rmb  = User::rmb($rmb_num);

			$ary['rmb'] = $rmb;
			$ary['rb']  = $rb;

			CMD(200, $ary);

		}else{
			CMD(210);
		}



	//checkip开关
	case 'checkip':
		
		//俱乐部id
		$clubId = (int)$_P['clubId'];
		if($clubId<=0) {
			CMD(202);
		}

		//俱乐部id
		$checkip = $_P['checkip'];
		if($checkip=='') {
			CMD(202);
		}
		
		Club::edit( array('checkip'=>(int)$checkip), $clubId );

		break;
		
}