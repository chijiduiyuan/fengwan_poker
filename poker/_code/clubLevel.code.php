<?php
/**
 * 俱乐部等级商店操作模块
 *
 * @author HJH
 * @version  2017-6-18
 */

if(!$_P){exit;}

switch ($_a) {
	

	//等级商店列表
	case 'list':
		
		CMD(200, ClubLevel::get('id,title,avatar,level,subagent_num,member_num,lucky_flag,time_num,price'));

		break;


	//购买等级商品
	case 'buy':
		
		$clubId = (int)$_P['clubId'];
		$id = (int)$_P['id'];

		//验证参数
		if( !$clubId || !$id ) {
			CMD(202);
		}

		$info = ClubLevel::get('level,subagent_num,member_num,lucky_flag,time_num,price', $id);
		if(!$info) {
			CMD(210);
		}

		$rmb = User::rmb($info['price'], false);
		if($rmb!==false) {
			$clubInfo = Club::getInfo($clubId,'level,expir', false);

			if($clubInfo['level']==$info['level'] && $clubInfo['expir']>time()) {
				//相同则时间叠加
				Club::edit( array('expir'=>((int)$clubInfo['expir']+((int)$info['time_num']*86400))), $clubId );
			
			//不同则覆盖
			}else{
				Club::edit( array('level'=>$info['level'],'expir'=>(time()+((int)$info['time_num']*86400)),'subagentLimit'=>$info['subagent_num'],'memberLimit'=>$info['member_num'],'luckyFlag'=>$info['lucky_flag']), $clubId );
			}

			$modifyInfo = [];
			$modifyInfo['rmb'] = $rmb;
			Fun::addNotify( [UID], $modifyInfo,'user_info' );

		}else{
			CMD(206);
		}
		
		break;
	
}