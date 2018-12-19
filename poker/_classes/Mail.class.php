<?php
/**
 * 邮件操作类
 *
 * @author HJH
 * @version  2017-7-19
 */


class  Mail{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 取邮件列表
	 * @param string $where 条件
	 * @return array 邮件列表
	 * @access public
	 */
	public static function getList($fields,$where=false) {
		$DB = useDB();
		if(!$where){
			$where = $fields;
			$fields = '*';
		}
		
		$sql = 'SELECT '.$fields.' FROM mail WHERE '.$where;
		return $DB->getList($sql);
	}


	/**
	 * 取邮件信息
	 * @param int $id 邮件id
	 * @return array 邮件信息
	 * @access public
	 */
	public static function getValue($id,$uid=UID) {
		$DB = useDB();
		//id,rmb,title,content,createTime
		return $DB->getValue('SELECT * FROM mail WHERE id='.$id.' AND (uid=0 OR uid='.$uid.')');
	}

	/**
	 * 取邮件总条数
	 * @param string $where sql条件
	 * @return int 邮件条数
	 * @access public
	 */
	public static function getCount($where) {
		$DB = useDB();
		
		return $DB->getCount('mail',$where);
	}

	/**
	 * 阅读
	 * @param int $mail_id 邮件id
	 * @return array true
	 * @access public
	 */
	public static function read($mail_id,$uid=UID) {
		$DB = useDB();
	
		$mailInfo = $DB->getValue('SELECT id,uid,is_read FROM mail WHERE id='
				.$mail_id.' AND (uid=0 OR uid='.$uid.')');
	
		//邮件不存在
		if(!$mailInfo){
			return false;
		}
		if($mailInfo['is_read']){//已读邮件		
			return true;
		}


		if($mailInfo['uid'] ==0 ){//公共邮件
			$isRead = $DB->getCount('mail_read','mail_id='.$mail_id.' AND uid='.(int)$uid);

			if(!$isRead){
		
				$DB->insert('mail_read', array(
					'mail_id'=>$mail_id,
					'uid'=>$uid,
					'createTime'=>time()
					)
				);
			}			
		}else{

			$DB->update('mail',[
					'is_read' => 1
				],'id='.$mail_id);
		}

		return true;
	}


	/**
	 * 查询是否已读/已领取
	 * @param int $mail_id 邮件id
	 * @return bool true
	 * @access public
	 */
	public static function getFlag($mail_id) {
		$DB = useDB();
		
		return $DB->getValue('SELECT id,rmb_flag FROM mail_read WHERE mail_id='.$mail_id.' AND uid='.UID);
	}


	/**
	 * 标记领取
	 * @param int $mail_id 邮件id
	 * @return bool true
	 * @access public
	 */
	public static function getRmb($mail_id,$uid=UID) {
		$DB = useDB();
		
		$mailInfo = Mail::getValue($mail_id);

		if(!$mailInfo){
			return false;
		}
		if($mailInfo['uid'] == 0 ){//公共邮件
			$info = $DB->getValue('SELECT id,rmb_flag FROM mail_read WHERE mail_id='.$mail_id
				.' AND uid='.$uid);
			if(!$info || $info['rmb_flag']) {
				return false;
			}

			return $DB->update('mail_read', array('rmb_flag'=>1), 'mail_id='.$mail_id.' AND uid='.(int)$uid);
		}else{

			return $DB->update('mail',[
					'is_rec_rmb' => 1
				],'id='.$mail_id);
		}
	}

	/**
	 * 发送邮件
	 * @param array $info 邮件内容
	 * @return bool 失败返回false /int 成功返回新增id
	 * @access public
	 */
	public static function send($info) {
		$DB = useDB();
	
		$uid = (int)$info['uid'];

		if($uid===0){
			$nickname = 'system';
		}else{
			$nickname = User::get('nickname','uid='.$uid);
			if(!$nickname){
				return false;
			}		
		}

		$rs = $DB->insert('mail',[
				'uid' 		=> $uid,
				'title' 	=> $info['title'],
				'content' 	=> $info['content'],
				'nickname' 	=> $nickname,
				'create_uid'=> $info['create_uid'],
				'create_nickname' => $info['create_nickname'],
				'rmb' 		=> (int)$info['rmb'],
				'createTime'=> time()
			]);

		return $rs;
	}

	/**
	 * 删除邮件
	 * @param int array $idArr
	 * @return int 返回删除id
	 * @access public
	 */
	public static function delete($idArr) {
		$DB = useDB();	

		$where = 'id in ('.implode(',',$idArr).')';

		return $DB->delete('mail',$where);
	}
}