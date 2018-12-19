<?php
/**
 * 俱乐部申请操作类
 *
 * @author HJH
 * @version  2017-6-18
 */


class  ClubApply{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}



	/**
	 * 取申请列表
	 * @param string $param 字段
	 * @param string $where 条件
	 * @return array 申请列表
	 * @access public
	 */
	public static function getList( $param='*', $where='' ) {
		
		$DB = useDB();

		if(!$where)return;
		
		return $DB->getList('SELECT '.$param.' FROM club_apply WHERE '.$where);
	}


	/**
	 * 取申请信息
	 * @param string $param 字段
	 * @param string $where 条件
	 * @return array 申请信息
	 * @access public
	 */
	public static function getInfo( $param='*', $where='' ) {				
		
		$DB = useDB();

		if(!$where)return;
		
		return $DB->getValue('SELECT '.$param.' FROM club_apply WHERE '.$where);
	}


	/**
	 * 写入申请信息
	 * @param int $clubId 俱乐部id
	 * @return int applyId
	 * @access public
	 */
	public static function add($clubId,$inviteUid) {				
		
		$DB = useDB();

		if(!$clubId)return;

		$ary = array(
			'clubId' => $clubId,
			'uid'    => UID,
			'time'   => time(),
			'inviteUid' => $inviteUid
		);
		
		return $DB->insert('club_apply', $ary);
	}


	/**
	 * 修改申请状态
	 * @param int $applyId 申请id
	 * @param int $status 状态码
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function setStatus( $applyId, $status ) {		
		$DB = useDB();		
		return $DB->update('club_apply', array('status'=>$status), 'applyId='.$applyId.' AND status='.CLUB_MEMBER_ING);
	}


}