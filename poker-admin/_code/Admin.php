<?php
/**
 * Admin - gm管理员管理 相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Admin_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}
	/**
	 * 登录 
	 * @param  string username 账号
	 * @param  string password 密码
	 * @return array 管理员列表
	 * @access public
	 */
	public static function login($_P) {

		$username = trim($_P['username']);
		$password = trim($_P['password']);

		//验证参数
		if(!$username || !$password) {
			CMD('ILLEGAL_PARAM');
		}

		//登入
		$userInfo = Admin::login($username, $password);

		$_SESSION['aid']     = $userInfo['aid'];
		$_SESSION['purview'] = $userInfo['purview'];

		$retAry = array();
		$retAry['aid'] 			= $userInfo['aid'];
		$retAry['nickname'] 	= $userInfo['nickname'];
		$retAry['avatar'] 		= $userInfo['avatar'];
		$retAry['purview'] 		= explode(',', $userInfo['purview']);

		//返回玩家信息给客户端
		return $retAry;		
	}

	/**
	 * 登出
	 * @return array 管理员列表
	 * @access public
	 */
	public static function logout($_P) {

		session_unset();
		session_destroy();
	}	

	/**
	 * 取管理员列表 
	 * @param  array 搜索条件
	 * @return array 管理员列表
	 * @access public
	 */
	public static function getList($_P) {

		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE;

		$where = 'aid>0';

		//aid搜索
		if((int)$_P['aid']) {
			$where .= ' AND aid='.(int)$_P['aid'];
		}

		//昵称搜索
		if($_P['nickname']) {
			$where .= ' AND nickname LIKE \''.$_P['nickname'].'%\'';
		}

		//帐号搜索
		if($_P['username']) {
			$where .= ' AND username LIKE \''.$_P['username'].'%\'';
		}

		//状态搜索
		if( $_P['status']!='' ) {
			$where .= ' AND status='.(int)$_P['status'];
		}

		return Admin::getList($curPage, $pageSize, $where);
	}//getList

	/**
	 * 添加管理员
	 * @param array 管理员详情
	 * @return void
	 * @access public
	 */
	public static function add($_P) {
				
		//用户名
		$username = $_P['username'];
		if(!$username) {
			CMD('ILLEGAL_PARAM');
		}

		//密码
		$password = $_P['password'];
		if(!$password) {
			CMD('ILLEGAL_PARAM');
		}

		//昵称
		$nickname = $_P['nickname'];
		if(!$nickname) {
			CMD('ILLEGAL_PARAM');
		}

		//判断用户名重复
		$aid = Admin::get('aid', 'username=\''.$username.'\'');
		if( (int)$aid ) {
			CMD('ACCOUNT_DUPLICATE');
		}

		//加密串
		$encrypt = mt_rand();

		$ary = array(
			'username'	=> $username,
			'password'	=> Fun::pwdEncrypt($password,$encrypt),
			'encrypt'	=> $encrypt,
			'nickname'	=> $nickname,
			'avatar'	=> $avatar
		);

		$rs = Admin::add($ary);
		if(!$rs){
			CMD('FAILE');
		}
		
	}//add

	/**
	 * 修改管理员
	 * @param int $aid 管理员id
	 * @param array 管理员详情
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
				
		//管理员id
		$aid = (int)$_P['aid'];
		if($aid<=0) {
			CMD('ILLEGAL_PARAM');
		}

		//昵称
		$nickname = $_P['nickname'];
		if(!$nickname) {
			CMD('ILLEGAL_PARAM');
		}

		//判断用户名重复
		$rs = Admin::get('aid', 'aid<>'.$aid.' AND username=\''.$username.'\'');
		if( (int)$rs ) {
			CMD('ACCOUNT_DUPLICATE');
		}

		$rs = Admin::edit( array('nickname'=>$nickname), $aid);

		if(!$rs){
			CMD('FAILE');
		}
		
	}

	/**
	 * 管理员修改密码
	 * @param int $aid 管理员id
	 * @param string password 密码
	 * @return void
	 * @access public
	 */
	public static function repwd($_P) {
				
		//管理员id
		$aid = (int)$_P['aid'];
		if($aid<=0) {
			CMD('ILLEGAL_PARAM');
		}

		//密码
		$password = $_P['password'];
		if(!$password) {
			CMD('ILLEGAL_PARAM');
		}

		//加密串
		$encrypt = mt_rand();

		$ary = array(
			'password'	=> Fun::pwdEncrypt($password,$encrypt),
			'encrypt'	=> $encrypt
		);

		$rs = Admin::edit($ary, $aid);

		if(!$rs){
			CMD('FAILE');
		}		
	}

	/**
	 * 锁定/解锁 管理员
	 * @param int $aid 管理员id
	 * @param int $status 状态 1=正常 0=冻结
	 * @return void
	 * @access public
	 */
	public static function lock($_P) {	
		//玩家id
		$aid = (int)$_P['aid'];
		if($aid<=0) {
			CMD('ILLEGAL_PARAM');
		}

		$status = (int)$_P['status'] ? 1 : 0;

		//锁定
		$rs = Admin::edit(array('status'=>$status), $aid);
		if(!$rs){
			CMD('FAILE');
		}	
	}

	/**
	 * 删除管理员
	 * @param int array $ids 管理员uid列表
	 * @return void
	 * @access public
	 */
	public static function delete($_P) {	

		$idArr  = $_P['ids'];
		if(!is_array($idArr)) {
			CMD('ILLEGAL_PARAM');
		}
		//id必须是整形
		foreach ($idArr as $item) {
			if( !is_numeric( $item ) ){
				CMD('ILLEGAL_PARAM');
			}

			Admin::delete( $item);		
		}		
		
	}

	/**
	 * 获取权限
	 * @param int $aid 管理员uid	 
	 * @return array 权限列表
	 * @access public
	 */
	public static function getPurview($_P) {
		
		//管理员id
		$aid = (int)$_P['aid'];
		if($aid<=0) {
			CMD('ILLEGAL_PARAM');
		}

		$purview = Admin::get('purview', 'aid='.$aid);

		return explode(',', $purview);
	}

	/**
	 * 保存权限
	 * @param int $aid 管理员uid	 
	 * @return array 权限列表
	 * @access public
	 */
	public static function setPurview($_P) {
		
		//管理员id
		$aid = (int)$_P['aid'];
		if($aid<=0) {
			CMD('ILLEGAL_PARAM');
		}		
		//权限
		$purview = $_P['purview'];
		if(!is_array($purview)) {
			CMD('ILLEGAL_PARAM');
		}

		$rs = Admin::edit( array('purview'=>implode(',', $purview)), $aid);
		if(!$rs){
			CMD('FAILE');
		}
	}
}