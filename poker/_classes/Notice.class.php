<?php
/**
 * 公告操作类
 *
 * @author HJH
 * @version  2017-7-20
 */


class  Notice{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 公告件列表
	  * @param string $where 条件
	 * @return array 公告列表
	 * @access public
	 */
	public static function getList($where) {
		$DB = useDB();
		return $DB->getList('SELECT id,title,createTime,status FROM notice WHERE '.$where);
	}


	/**
	 * 取公告信息
	 * @param int $id 公告id
	 * @return array 公告信息
	 * @access public
	 */
	public static function getValue($id) {
		$DB = useDB();
		return $DB->getValue('SELECT id,title,content,createTime,status FROM notice WHERE id='.$id.' AND status=1');
	}

	/**
	 * 取公告信息
	 * @param int $id 公告id
	 * @return array 公告信息
	 * @access public
	 */
	public static function getValueForGm($id) {
		$DB = useDB();
		return $DB->getValue('SELECT id,title,content,createTime,status FROM notice WHERE id='.$id);
	}

	/**
	 * 取邮件总条数
	 * @param string $where sql条件
	 * @return int 邮件条数
	 * @access public
	 */
	public static function getCount($where) {
		$DB = useDB();
		
		return $DB->getCount('notice',$where);
	}

	/**
	 * 添加公告
	 * @param array $info 公告内容
	 * @return bool 失败返回false /int 成功返回新增id
	 * @access public
	 */
	public static function add($info) {
		$DB = useDB();
	
		$uid = (int)$info['uid'];


		$rs = $DB->insert('notice',[				
				'title' 	=> $info['title'],
				'content' 	=> $info['content'],
				'status' 	=> $info['status'],
				
				'createTime'=> time()
			]);

		return $rs;
	}

	/**
	 * 修改公告
	 * @param array $info 公告内容
	 * @return bool 失败返回false /int 成功返回新增id
	 * @access public
	 */
	public static function edit($id,$info) {
		$DB = useDB();
		
		$rs = $DB->update('notice',$info,'id='.$id);

		return $rs;
	}

	/**
	 * 删除公告
	 * @param int array $idArr
	 * @return int 返回删除id
	 * @access public
	 */
	public static function delete($idArr) {
		$DB = useDB();	
		
		$where = 'id in ('.implode(',',$idArr).')';

		return $DB->delete('notice',$where);
	}
}