<?php
/**
 * 工具函数类
 *
 * @author HJH
 * @version  2017-6-6
 */


class  Fun{

	/**
	 * 构造函数
	 */
	function __construct( ) {	
		
	}

	//判断状态是否空闲
	public static function opFree($op) {
		$cache = getCache();
		if( $cache->get($op.UID) ) {
			return false;
		}
		return true;
	}


	//设置状态为忙
	public static function opBusy($op) {
		$cache = getCache();
		$cache->set($op.UID,1,30);
		return true;
	}


	//设置状态空闲
	public static function opOK($op) {
		$cache = getCache();
		$cache->delete($op.UID);
		return true;
	}


	//手牌历史表hash
	public static function handHash($clubId) {
		return 'club_room_hand_'.(($clubId%30)+1);
	}


	//参数过滤
	public static function addslashe($param) {

		if(!$param) {
			return $param;
		}

		if(!is_array($param)) {
			return addslashes($param);
		}

		foreach($param as &$item) {
			if(is_array($item)) {
				$item = Fun::addslashe($item);
			}else{
				$item = addslashes($item);
			}
		}

		return $param;
	}


	/**
	 * 取当前ip
	 */
	public static function getIP() {
		
		global  $_SERVER;  
		
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$realip  =  $_SERVER["HTTP_X_FORWARDED_FOR"];  

		}elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$realip  =  $_SERVER["HTTP_CLIENT_IP"];

		}else{
			$realip  =  $_SERVER["REMOTE_ADDR"];  
		}

		return  $realip;
	}


	/**
	 * 过期时间转换成剩余天数
	 * @param array $clubInfo 俱乐部信息
	 * @return void
	 * @access public
	 */
	public static function ttod(&$info) {
		
		if(!$info || !is_array($info) || !$info['expir'])return;

		$t = (int)$info['expir']-time();
		if($t<=0) {
			$info['expir'] = 0;
		}else{
			$info['expir'] = ceil($t/86400);
		}
	}


	/**
	 * 写入消息队列
	 * @param array $uidArr 需要发送到的uid
	 * @param array $ary 需要发送的数据
	 * @return void
	 * @access public
	 */
	public static function addNotify($uidArr, $ary=[],$op=false) {
		global $_c, $_a;

		$cache = getCache();

		if($op){
			$data['op'] = $op;
		}else{
			$data['op'] = $_c.'_'.$_a;
		}

		
		$data['data'] = $ary;

		$key = 'notify_list_key_'.$_c.'_'.$_a.Math.rand();
		while ($cache->exists($key)) {
			$key = 'notify_list_key_'.$_c.'_'.$_a.Math.rand();
		}
		$obj = array('uidArr'=>serialize($uidArr), 'data'=>serialize($data));

		$cache->setArray($key, $obj);

		return $cache->cacheObj->rpush('NOTIFY_LIST', $key);
	}


	/**
	 * 读取消息队列
	 * @return Object
	 * @access public
	 */
	public static function getNotify() {
		
		$cache = getCache();

		$key = true;
		$ary = array();

		while($key) {
			
			$key = $cache->cacheObj->lpop('NOTIFY_LIST');

			if($key) {

				$obj = $cache->getArray($key);
				$obj['uidArr'] = unserialize($obj['uidArr']);
				$obj['data']   = unserialize($obj['data']);

				$ary[] = $obj;

				$cache->delete($key);
			}
		}

		return $ary;
	}





}