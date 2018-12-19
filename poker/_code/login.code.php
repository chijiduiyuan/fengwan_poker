<?php
/**
 * 登入模块
 *
 * @author HJH
 * @version  2017-6-6
 */

if(!$_P){exit;}


//第三方平台
$platform = $_P['platform'];
$platformId = trim($_P['platformId']);
$platnick = urldecode($_P['platnick']);
$platImage = $_P['platImage'];


//分模块处理
switch($platform) {


case 'wechat':   //微信登入
	
	if($_a=='agent') { //判断是否代理登录
		$userInfo = User::loginAgent($platformId);
	}else{
		//$userInfo = User::login($platformId, 3);
		$userInfo = User::login($platformId,3,0,$platnick,$platImage);
	}
	break;


default:
	
	break;
}

//判断token是否过期
if(!$userInfo) {
	CMD(209);
}

//判断帐号是否被锁定
if( (int)$userInfo['status']<1 ) {
	CMD(205);
}

$cache = getCache();

//缓存玩家信息
$cache->setArray('user_info_'.(int)$userInfo['uid'], $userInfo);

//判断是否已在线
if($_a!='agent') { //判断是否代理登录
	$sid = User::getOnline('one','sid','uid='.$userInfo['uid']);
	if( $sid && $cache->get($sid) ) {
		$cache->delete($sid);
	}
}

//登陆次数增加
$logNum = (int)$userInfo['login_num']+1;
User::edit(array('last_time'=>time(), 'last_ip'=>User::getIP(), 'login_num'=>$logNum), $userInfo['uid']);

//读取会员VIP卡权限
$userInfo = User::getVipInfo($userInfo['uid']) + $userInfo;

//读取游戏开关
$cfg = getCFG('data');
$userInfo['game_switch_dz']  = $cfg['gameSwitchDZ'];
$userInfo['game_switch_cow'] = $cfg['gameSwitchCOW'];
$userInfo['game_switch_ducal'] = $cfg['gameSwitchDUCAL'];
$userInfo['game_switch_cowcow'] = $cfg['gameSwitchCOWCOW'];

//删除不需要的字段
unset($userInfo['pid']);
unset($userInfo['username']);
unset($userInfo['create_time']);
unset($userInfo['create_ip']);
unset($userInfo['last_time']);
unset($userInfo['last_ip']);
unset($userInfo['vip_endtime']);
unset($userInfo['status']);
unset($userInfo['audio']);
unset($userInfo['pos']);
unset($userInfo['pos_x']);
unset($userInfo['pos_y']);
unset($userInfo['verify_date']);
unset($userInfo['verify_num']);

$userInfo['sid'] = session_id();

$_SESSION['uid'] = $userInfo['uid'];

//返回玩家信息给客户端
CMD(200, $userInfo);