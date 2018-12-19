<?php
/**
 * Mail - 邮件 相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
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
	 * @param string $curPage 当前页
	 * @param string $pageSize 每页条数
	 * @return array 邮件列表
	 * @access public
	 */
	public static function getList($_P) {
		$DB = useDB();
		
		$uid 		= $_P['uid'];
		$uidType 	= $_P['uidType'];
		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$whereArr = [];
		$whereArr[] = '1=1';

		if( !is_numeric($uidType) ){//全部邮件
			//echo '全部邮件';
		}else if( $uidType==0 ){//公共邮件
			//echo '公共邮件';
			$whereArr[] = 'uid=0';
		}else if($uidType==1){// uidType=1 个人邮件
			//echo '个人邮件';			
			if( is_numeric($uid) && (int)$uid>0){
				$whereArr[] = 'uid='.(int)$uid ;		
			}else{
				$whereArr[] = 'uid>0';		
			}				
		}


		$where = implode(' AND ',$whereArr);		
		$total = Mail::getCount($where);		
		$limitForm = ($curPage-1)*$pageSize;			

		$where .= ' ORDER BY id DESC LIMIT '.$limitForm.','.$pageSize;

		return [
			'list' 	=> Mail::getList($where),
			'total' => $total
		];

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

		$uid 		= (int)$_P['uid'];
		$title 		= $_P['title'];
		$content 	= $_P['content'];
		$rmb 		= (int)$_P['rmb'];		

		$rs = Mail::send([
				'uid' 		=> $uid,
				'title' 	=> $title,
				'content' 	=> $content,				
				'create_uid'=> 0,
				'create_nickname' => 'system',
				'rmb' 		=> $rmb
			]);

		//如果是发给单个玩家则通知客户端 刷新邮件列表
		// if($uid>0){
		// 	Fun::addNotify([$uid],['新邮件key']);
		// }

		if(!$rs){
			CMD('USER_NOT_FOUND');
		}
	}

	/**
	 * 删除邮件
	 * @param int array $ids 邮件id列表
	 * @return void
	 * @access public
	 */
	public static function delete($_P) {

		$ids 	= $_P['ids'];		

		$idArr = explode(',',$ids);
		//id必须是整形
		foreach ($idArr as $item) {
			if( !is_numeric( $item ) ){
				CMD('ILLEGAL_PARAM');
			}
		}

		$rs = Mail::delete($idArr);
		if(!$rs){
			CMD('RECORD_NOT_FOUND');
		}
	}


	
}