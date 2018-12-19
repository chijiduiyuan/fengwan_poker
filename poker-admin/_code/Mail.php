<?php
/**
 * Mail - 邮件 相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Mail_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 取邮件列表
	 * @param int $uid 
	 * @param int $uidType 邮件类型 1=个人邮件 0=公共邮件
	 * @param string $curPage 当前页
	 * @param string $pageSize 每页条数
	 * @return array 邮件列表
	 * @access public
	 */
	public static function getList($_P) {

		$rs = postUrl([		
			'uid' 		=> $_P['uid'],
			'uidType'   => $_P['uidType'],

			'curPage' 	=> $_P['curPage']?(int) $_P['curPage']:1,
			'pageSize'  => $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE
		]);

		return $rs['data'];

	}

	/**
	 * 发送邮件
	 * @param int $uid 收件人uid
	 * @param string $title 邮件标题
	 * @param string $content 邮件内容
	 * @param int $rmb rmb货币
	 * @return void
	 * @access public
	 */
	public static function send($_P) {

		//判断是否有附加钻石的权限
		if( (int)$_P['rmb']>0 ) {
			$userPurview = explode(',', $_SESSION['purview'] );
			if( !in_array('A0201', $userPurview) ) {
				CMD('UNAUTHORIZED');
			}
		}

		$rs = postUrl([		
			'uid' 		=> (int)$_P['uid'],
			
			'title' 	=> $_P['title'],
			'content' 	=> $_P['content'],				
			'create_uid'=> 0,
			'create_nickname' => 'system',
			'rmb' 		=> (int)$_P['rmb']

		]);

		return $rs['data'];
	}

	/**
	 * 删除邮件
	 * @param int array $ids 邮件id列表
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
		}
		$ids 	= implode(',',$idArr);

		$rs = postUrl([		
			'ids' 		=> $ids
		]);

		return $rs['data'];
	}


	
}