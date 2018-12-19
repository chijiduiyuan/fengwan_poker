<?php
/**
 * 绑定模块
 *
 * @author HJH
 * @version  2017-6-6
 */

if(!$_P){exit;}

$phone = trim($_P['phone']);
$uid    =(int)$_P['uid'];
//验证参数
if(!$phone || !$uid) {
	CMD(202);
}
$allphone = User::allPhone();
foreach($allphone as $vv){
	if($vv['phone'] == $phone){
		CMD(201);
	}
	if($vv['uid'] == $uid){
		$tel = User::bind($phone,$uid);
		$rmb = 10;
		$title 	= '赠送钻石通知';
		$content 	= '绑定手机成功! 系统赠送您赠送您'.$rmb.'钻石';
		$rmb 		= 0;		

		$rs = Mail::send([
			'uid' 		=> $uid,
			'title' 	=> $title,
			'content' 	=> $content,				
			'create_uid'=> 0,
			'create_nickname' => '系统',
			'rmb' 		=> $rmb
		]);
		//返回玩家信息给客户端
		CMD(200, $tel);
	}

}




