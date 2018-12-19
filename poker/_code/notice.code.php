<?php
/**
 * 公告操作模块
 *
 * @author HJH
 * @version  2017-7-20
 */

if(!$_P){exit;}

switch ($_a) {
	

	//公告列表
	case 'list':

		//分页参数
		$id       = (int)$_P['id'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND id<'.$id;
		}

		$where = 'status=1'.$pageWhere.' ORDER BY id DESC LIMIT '.$pageSize;

		$list = Notice::getList($where);

		$cfg = getCFG('data');
		foreach ($list as &$item) {
			$item['url'] = $cfg['gameDomainName'].'/public.php?_c=notice&_a=info&id='.(int)$item['id'];
		}

		CMD(200, $list);

		break;


	//公告详情
	case 'info':

		//邮件id
		$id = (int)$_P['id'];
		if($id<=0) {
			echo 'BAD_REQUEST';
			exit();
		}

		$info = Notice::getValue($id);
		if(!$info) {
			echo 'BAD_REQUEST';
			exit();
		}

		$title   = $info['title'];
		$time    = date('Y-m-d H:i:s', $info['createTime']);
		$content = $info['content'];

		header('Content-Type: text/html; charset=UTF-8');
		include($global['path']['lib'] . 'notice.tpl.php');
		exit();

		break;
	
}