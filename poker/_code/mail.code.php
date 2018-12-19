<?php
/**
 * 邮件操作模块
 *
 * @author HJH
 * @version  2017-7-19
 */

if(!$_P){exit;}

switch ($_a) {
	

	//邮件列表
	case 'list':

		//分页参数
		$id       = (int)$_P['id'];
		$pageSize = (int)$_P['pageSize']>0 ? (int)$_P['pageSize'] : 20;

		$pageWhere = '';
		if($id) {
			$pageWhere = ' AND id<'.$id;
		}

		$cfg = getCFG('data');

		$where = '(uid=0 OR uid='.UID.') AND createTime>'.(time()-$cfg['mailTimeout']).$pageWhere.' ORDER BY id DESC LIMIT '.$pageSize;


		$list = Mail::getList('id,uid,rmb,title,createTime,is_read,is_rec_rmb',$where);

		$rs = [];
		foreach ($list as &$item) {
			

			if($item['uid'] == 0 ){//公共邮件特殊处理
				$flagInfo = Mail::getFlag($item['id']);

				//是否已读
				$item['readFlag'] = (int)$flagInfo['id'];

				//是否已领取
				if((int)$item['rmb']) {
					$item['rmbFlag'] = (int)$flagInfo['rmb_flag'];
				}else{
					$item['rmbFlag'] = 0;
				}
			}else{
				
				$item['readFlag'] = $item['is_read'];
				$item['rmbFlag']  = $item['is_rec_rmb'];
			}

			

			$rs[] = [
				'id'		=> $item['id'],
				'rmb'		=> $item['rmb'],
				'title'		=> $item['title'],
				'createTime'=> $item['createTime'],

				'rmbFlag'	=> $item['rmbFlag'],	//是否领取附件
				'readFlag'	=> $item['readFlag']	//是否已读
			];
		}
		
		CMD(200, $rs);

		break;            


	//阅读详情
	case 'read':

		//邮件id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD(202);
		}

		$info = Mail::getValue($id);

		if(!$info) {
			CMD(210);
		}

		//记录已读
		Mail::read($id);

		if($info['uid'] == 0){//公共邮件
			//查询附件钻石是否领取
			$flagInfo = Mail::getFlag($id);
			if((int)$info['rmb']) {
				$info['rmbFlag'] = (int)$flagInfo['rmb_flag'];
			}else{
				$info['rmbFlag'] = 0;
			}
		}else{	//个人邮件		
			$info['rmbFlag'] = $info['is_rec_rmb'];
		}		

		$rs = [
			'id'		=> $info['id'],
			'rmb'		=> $info['rmb'],
			'title'		=> $info['title'],
			'content'	=> $info['content'],
			'createTime'=> $info['createTime'],

			'rmbFlag'	=> $info['rmbFlag']			
		];

		CMD(200, $rs);

		break;

	//领取附件钻石
	case 'getRmb':
		
		//邮件id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD(202);
		}

		$info = Mail::getValue($id);
		if(!$info || !(int)$info['rmb']) {
			CMD(210);
		}

		$DB = useDB();
		$DB->beginTransaction();  //开启事务

		//标记已领取
		$rs = Mail::getRmb($id);
		if(!$rs) {
			$DB->rollBack(); //回滚
			CMD(210);
		}

		//增加rmb
		$rmb = User::rmb((int)$info['rmb']);
		if($rmb===false) {
			$DB->rollBack(); //回滚
			CMD(210);
		}

		$DB->commit(); //提交

		CMD(200, array('rmb'=>$rmb));

		break;



	//未读邮件统计
	case 'unread':

		$cfg = getCFG('data');

		$DB = useDB();

		$where = 'createTime>'.(time()-$cfg['mailTimeout']);

		$num = (int)$DB->getCount('mail','uid='.UID.' AND is_read=0 AND '.$where);

		$list = Mail::getList( 'uid=0 AND '.$where);
		foreach ($list as $item) {

			if( !Mail::getFlag($item['id']) ) {
				$num++;
			}
		}
		
		CMD(200, array('num'=>$num));

		break;
	
}