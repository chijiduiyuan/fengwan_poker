<?php
/**
 * 玩家操作类
 *
 * @author HJH
 * @version  2017-6-6
 */


class  User{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 
	 * @access public
	 */
	public static function login($username, $pid, $cid=0, $nick,$avatar) {

		$userInfo = User::get('*', 'username=\''.$username.'\' AND cid='.$cid);

		//生成token
		$token = md5(time().mt_rand());
		$nickname =	$nick; 
		
		//判断是否新玩家
		if(!$userInfo) {

			//读取玩家默认配置数据
			$cfg = getCFG('data');

			//组织玩家基础数据
			$userInfo = array(
				'pid'			=> $pid,
				'token'			=> $token,
				'cid'			=> $cid,
				'username'		=> $username,
				'nickname'		=> $nickname,
				'avatar'		=> $avatar,
				'rmb'			=> $cfg['userInitRmb'],
				'gold'			=> $cfg['userInitGold'],
				'verify_date'	=> date('Y-d-m'),
				'verify_num'	=> 1,
				'create_time'	=> time(),
				'create_ip'		=> User::getIP(),
			);

			$DB = useDB();

			//玩家起始id
			$autoId = $DB->getAutoIncrement('user');
			if($autoId < $cfg['userAutoIncrement']) {
				$userInfo['uid'] = $cfg['userAutoIncrement'];
			}
			
			$userInfo['uid'] = $DB->insert('user',$userInfo);
			if(!$userInfo['uid']) {
				return false;

			}elseif(!$nick) {
				//更新昵称
				//$nick = 'rt'.$userInfo['uid'].mt_rand(1,9);
				User::edit(array('nickname'=>$nickname,"avatar"=>$avatar), $userInfo['uid']);
				$userInfo['nickname'] = $nick;
				$userInfo['avatar'] = $avatar;
			}

			$userInfo['vip_card']       = 0;
			$userInfo['vip_endtime']    = 0;
			$userInfo['card_club_num']  = 0;
			$userInfo['card_emoji_num'] = 0;
			$userInfo['card_delay_num'] = 0;
			$userInfo['status'] 		= 1;
			$userInfo['phone']			= '';
			$userInfo['sendRmb']		=0;
		}else{
			
			User::edit(array('token'=>$token,'nickname'=>$nickname,"avatar"=>$avatar), $userInfo['uid']);
			$userInfo['token'] = $token;
		}

		return $userInfo;
	}

	//查询所有手机号
	public static function allPhone(){
		$DB = useDB();
		return $DB->getList('SELECT uid,phone FROM user WHERE status=1');
	}

	//绑定手机 送10个钻石
	public static function bind($phone,$uid){
		$userInfo = User::get('*','uid=\''.$uid.'\'');
		User::edit(array('phone'=>$phone,'rmb'=>(int)$userInfo['rmb']+10),$uid);
		
		return $phone;
	}

	/**
	 * 登录(Token)
	 * @access public
	 */
	public static function loginToken($token) {
		
		$userInfo = User::get('*', 'token=\''.$token.'\'');
		if(!$userInfo) {
			return false;
		}
		
		return $userInfo;
	}


	/**
	 * 登录(agent)
	 * @access public
	 */
	public static function loginAgent($username, $cid=0) {
		
		$userInfo = User::get('*', 'username=\''.$username.'\' AND cid='.$cid);
		if(!$userInfo) {
			return false;
		}

		//判断是否有创建的俱乐部或加入的且有管理权限的俱乐部
		$list = Club::agentGetList(1, 20, (int)$userInfo['uid']);
		if(!$list || !is_array($list) || count($list)<=0 ) {
			return false;
		}
		
		return $userInfo;
	}

	
	/**
	 * 取玩家信息
	 * @param string $param 字段
	 * @param string $where 条件
	 * @return array 玩家信息
	 * @access public
	 */
	public static function get( $param='*', $where='' ) {
		
		$DB = useDB();
		
		if(!$where){
			$where = 'uid='.UID;
		}

		if($param=='*') {
			$Field = array();
			$list = $DB->getList('SHOW FULL COLUMNS FROM user');
			foreach ($list as $item) {
				if($item['Field']=='pos') {
					$item['Field'] = 'ASTEXT(pos) as pos, X(pos) as pos_x, Y(pos) as pos_y';
				}
				$Field[] = $item['Field'];
			}
			$param = implode(',', $Field);

		}elseif(strpos($param, 'pos')!==false) {
			$param = str_replace('pos', 'ASTEXT(pos) as pos, X(pos) as pos_x, Y(pos) as pos_y', $param);
		}

		$info = $DB->getValue('SELECT '.$param.' FROM user WHERE '.$where);

		//经纬度特殊处理
		if( is_array($info) && array_key_exists('pos', $info) && !$info['pos'] ) {
			$info['pos']   = 0;
			$info['pos_x'] = 0;
			$info['pos_y'] = 0;
		}

		return $info;
	}


	/**
	 * 取玩家信息(缓存)
	 * @param string $param 字段
	 * @param int $uid 玩家uid
	 * @return array 玩家信息
	 * @access public
	 */
	public static function getc( $param='', $uid=UID ) {
		$cache = getCache();

		$info = false;
		if ($cache->exists('user_info_'.(int)$uid)) {
			$info = $cache->getArray('user_info_'.(int)$uid, $param);
		}
		
		if(!$info) {
			$userInfo = User::get('*', 'uid='.(int)$uid);
			if($userInfo) {
				$cache->setArray('user_info_'.(int)$userInfo['uid'], $userInfo);
				$info = $cache->getArray('user_info_'.(int)$uid, $param);
			}
		}		
		
		return $info;
	}


	/**
	 * 修改会员资料
	 * @param integer $param 修改的参数数组
	 * @param integer $uid 修改的用户uid
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function edit( $param, $uid=UID) {
		$DB = useDB();		
		return $DB->update('user', $param, 'uid='.$uid);
	}
	
	
	/**
	 * 增加rmb
	 * @param integer $num 要增加的数量
	 * @param bool $add true增加,false减少
	 * @param integer $uid 用户id
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function rmb( $num, $add=true, $uid=UID ) {
		
		$DB = useDB();	
		
		if($add){
			$sql='rmb+'.$num;

		}else{		
			$oldNum = User::get('rmb','uid='.$uid);
			if($oldNum<$num){
				return false;
			}
			$sql='rmb-'.$num;
		}
		
		$rs = $DB->exeSql('UPDATE user SET rmb='.$sql.' WHERE uid='.(int)$uid);
		if(!$rs) {
			return false;
		}
		
		$nowNum = User::get('rmb','uid='.$uid);
		
		return $nowNum;	
	}


	/**
	 * 增加gold
	 * @param int $num 要增加的数量
	 * @param bool $add true增加,false减少
	 * @param int $uid 会员id
	 * @param bool $flag true可以减到0,false不足提示失败
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function gold( $num, $add=true, $uid=UID, $flag=false ) {		
		
		$DB = useDB();
		
		if($add){
			$sql='gold+'.$num;

		}else{
			$oldNum = (int)User::get('gold','uid='.$uid);
			if($oldNum<$num){
				if($flag) {
					$num = $oldNum;
				}else{
					return false;
				}
			}
			$sql='gold-'.$num;
		}
		
		$rs = $DB->exeSql('UPDATE user SET gold='.$sql.' WHERE uid='.(int)$uid);
		if(!$rs) {
			return false;
		}
		
		$nowNum = User::get('gold','uid='.$uid);
		
		return $nowNum;	
	}

	public static function sendrmb( $num, $add=true, $uid=UID, $flag=false ) {		
		
		$DB = useDB();
		
		if($add){
			$sql='rmb+'.$num;

		}else{
			$oldNum = (int)User::get('rmb','uid='.$uid);
			if($oldNum<$num){
				if($flag) {
					$num = $oldNum;
				}else{
					return false;
				}
			}
			$sql='rmb-'.$num;
		}
		
		$rs = $DB->exeSql('UPDATE user SET rmb='.$sql.' WHERE uid='.(int)$uid);
		if(!$rs) {
			return false;
		}
		
		$nowNum = User::get('rmb','uid='.$uid);
		
		return $nowNum;	
	}
	

	/**
	 * 读取玩家VIP卡权限
	 * @param int $uid 玩家uid
	 * @return array 玩家VIP卡权限信息
	 * @access public
	 */
	public static function getVipInfo($uid=UID) {
		$DB = useDB();

		$info = User::get('vip_card,vip_endtime,card_club_num,is_look_undercard,card_emoji_num,card_delay_num','uid='.(int)$uid);
		if(!$info)return;

		$info['expir'] = $info['vip_endtime'];

		if((int)$info['vip_endtime']<=time()) {

			$cfg = getCFG('data');

			$info['vip_card'] = 0;
			
			$info['card_club_num']  	= $cfg['clubCreateLimit'];
			$info['is_look_undercard'] 	= 0;
			$info['card_emoji_num'] 	= 0;
			$info['card_delay_num'] 	= 0;

			unset( $info['vip_endtime'] );

		}

		//转换过期时间
		Fun::ttod($info);

		return $info;
	}
	

	/**
	 * 更新用户在线情况
	 * @return viod
	 * @access public
	 */
	public static function upOnline() {
		
		if(!UID){
			return false;
		}
		
		$_SESSION['online']	 = time();
		
		$DB = useDB();
		$id = (int)$DB->getValue('SELECT id FROM online WHERE uid='.UID);
		$upInfo = array(
			'sid' 		  => session_id(),
			'uid' 		  => UID,
			'nickname' 	  => $_SESSION['nickname']?$_SESSION['nickname']:'',
			'create_time' => time()
		);

		if($id){
			$DB->update('online',$upInfo,'id='.$id);

		}else{
			$DB->insert('online',$upInfo);
		}
	}
	

	/**
	 * 取在线用户数
	 * @return viod
	 * @access public
	 */
	public static function getOnlineNum() {
		return User::getOnline('one','COUNT(id)');
	}
	

	/**
	 * 取在线用户列表
	 * @return viod
	 * @access public
	 */
	public static function getOnline($type, $fields='*', $w='') {
	
		$DB = useDB();
		$upTime = time()-333;//多33秒  5分钟内
		$where 	= 'create_time>'.$upTime;
		if($w){
			$where .= ' AND '.$w;
		}
		$sql = 'SELECT '.$fields.' FROM online WHERE '.$where;
		
		if($type == 'list'){
			$rs = $DB->getList($sql);
		}else{
			$rs = $DB->getValue($sql);
		}
		return $rs;
	}	
	
	/**
	 * 在线列表中删除用户
	 * @param integer $uid 玩家id
	 * @return viod
	 * @access public
	 */
	public static function delOnline($uid) {
	
		$DB = useDB();
		return $DB->delete('online','uid='.(int)$uid); //删除玩家表数据
	}	

	/**
	 * 删除账户
	 * @param integer $uid 玩家id
	 * @access public
	 */
	public static function delAccount( $uid=UID ) {
		
		$DB = useDB();
		$DB->beginTransaction();

		$DB->delete('user','uid='.(int)$uid); //删除玩家表数据
		//删除其它相关表数据...
		User::delOnline($uid);
		
		$DB->commit();				

	}


	/**
	 * 取当前ip
	 */
	public static function getIP() {
		
		global  $_SERVER;  
		
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$realip  =  $_SERVER["HTTP_X_FORWARDED_FOR"];  

		}elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$realip  =  $_SERVER["HTTP_CLIENT_IP"];

		}else{
			$realip  =  $_SERVER["REMOTE_ADDR"];  
		}

		return  $realip;
	}

	
}