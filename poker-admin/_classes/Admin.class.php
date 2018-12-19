<?php
/**
 * 管理员操作类
 *
 * @author HJH
 * @version  2017-7-28
 */


class  Admin{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 登录
	 * @access public
	 */
	public static function login($username, $password) {

		//读取管理员信息
		$userInfo = Admin::get('*', 'username=\''.$username.'\'');

		//判断帐号是否存在
		if(!$userInfo) {
			CMD('ACCOUNT_ERROR');
		}

		//判断帐号是否锁定
		if( $userInfo['status'] ==0 ) {
			CMD('ACCOUNT_LOCK');
		}

		//判断密码是否正确
		$pwd = Fun::pwdEncrypt($password, $userInfo['encrypt']);
		if($userInfo['password']!=$pwd) {
			CMD('ACCOUNT_ERROR');
		}

		return $userInfo;
	}


	/**
	 * 添加管理员
	 * @param integer $param 修改的参数数组
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function add($param) {
		$DBGM = useDBGM();
		return $DBGM->insert('admin', $param);
	}

	
	/**
	 * 取管理员信息
	 * @param string $param 字段
	 * @param string $where 条件
	 * @return array 信息
	 * @access public
	 */
	public static function get( $param='*', $where='' ) {
		
		$DBGM = useDBGM();
		
		if(!$where){
			return;
		}

		return $DBGM->getValue('SELECT '.$param.' FROM admin WHERE '.$where);
	}


	/**
	 * 取管理员列表
	 * @param int $pageNum 页码
	 * @param int $pageSize 条数
	 * @param string $where 条件
	 * @return array 列表
	 * @access public
	 */
	public static function getList($pageNum, $pageSize, $where) {
		
		$DBGM = useDBGM();

		$offset = ($pageNum-1)*$pageSize;

		//读取管理员列表
		$field = 'aid,username,nickname,avatar,status';
		$list = $DBGM->getList('SELECT '.$field.' FROM admin WHERE '.$where.' ORDER BY aid DESC LIMIT '.$offset.','.$pageSize);

		//统计条数
		$total = (int)$DBGM->getValue('SELECT count(aid) FROM admin WHERE '.$where);

		return array('total'=>$total, 'list'=>$list);
	}


	/**
	 * 修改资料
	 * @param integer $param 修改的参数数组
	 * @param integer $aid 修改的用户id
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function edit( $param, $aid ) {
		$DBGM = useDBGM();		
		return $DBGM->update('admin', $param, 'aid='.$aid);
	}

	/**
	 * 修改资料
	 * @param integer $param 修改的参数数组
	 * @param integer $aid 修改的用户id
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function delete( $aid ) {
		$DBGM = useDBGM();	
		return $DBGM->delete('admin','aid='.$aid);
	}
	
}