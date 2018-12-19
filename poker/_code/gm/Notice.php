<?php
/**
 * Notice - 公告 相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
 */


class  Notice_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 取公告列表
	 * @param int $status 公告状态0=未发布 1=已发布 
	 * @param string $curPage 当前页
	 * @param string $pageSize 每页条数
	 * @return array 公告列表
	 * @access public
	 */
	public static function getList($_P) {

		$status 	= $_P['status'];
		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$whereArr = [];
		$whereArr[] = '1=1';

		if( is_numeric($status) ){
			$whereArr[] = 'status='.$status ;		
		}		
		$where = implode(' AND ',$whereArr);		
		$total = Notice::getCount($where);		
		$limitForm = ($curPage-1)*$pageSize;			

		$where .= ' ORDER BY id DESC LIMIT '.$limitForm.','.$pageSize;

		return [
			'list' 	=> Notice::getList($where),
			'total' => $total
		];

	}

	/**
	 * 取公告详情
	 * @param int $id 公告id
	 * @return array 公告详情
	 * @access public
	 */
	public static function getValue($_P) {

		$id 	= (int)$_P['id'];
	
		$rs = Notice::getValueForGm($id);
		if(!$rs){
			CMD('RECORD_NOT_FOUND');
		}

		return $rs;

	}

	/**
	 * 添加公告	 
	 * @param string $title 公告标题
	 * @param string $content 公告内容
	 * @param int $status 公告状态 1发布 0 未发布
	 * @return void
	 * @access public
	 */
	public static function add($_P) {

		$title 		= $_P['title'];
		$content 	= $_P['content'];
		$status		= (int)$_P['status'];		

		$rs = Notice::add([				
				'title' 	=> $title,
				'content' 	=> $content,				
				'status'	=> $status
			]);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 修改公告	 
	 * @param int $id 公告id
	 * @param string $title 公告标题
	 * @param string $content 公告内容
	 * @param int $status 公告状态 1发布 0 未发布
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {

		$id 		= (int)$_P['id'];
		$title 		= $_P['title'];
		$content 	= $_P['content'];
		$status		= (int)$_P['status'];		

		$rs = Notice::edit($id,[				
				'title' 	=> $title,
				'content' 	=> $content,				
				'status'	=> $status
			]);

		if(!$rs && $rs !=0){
			CMD('FAILE');
		}
	}

	/**
	 * 发布/不发布 公告	 
	 * @param int $id 公告id
	 * @param int $status 公告状态 1发布 0 未发布
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {

		$id 		= (int)$_P['id'];	
		$status		= (int)$_P['status'];		

		$rs = Notice::edit($id,[						
				'status'	=> $status
			]);

		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 删除公告
	 * @param int array $ids 公告id列表
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

		$rs = Notice::delete($idArr);
		if(!$rs){
			CMD('RECORD_NOT_FOUND');
		}
	}


	
}