<?php
/**
 * Inform - 游戏通告 相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Inform_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 发送通告
	 * @param string $msg 消息内容
	 * @param int $loop 循环播放次数
	 * @param int $loopTs 循环播放间隔时间 单位秒
	 * @param string $color red=红色 white=白色(默认) yellow=黄色    
	 * @return void
	 * @access public
	 */
	public static function add($_P) {
		$uid = (int)$_P['uid'];
		$info = [
			'msg' 		=> $_P['msg'],
			'loop' 		=> (int)$_P['loop'],
			'loopTs' 	=> (int)$_P['loopTs'],
			'color' 	=> $_P['color']	
		];
		//全服在线玩家推送消息		
		if( $uid>0 ){//发送给单个玩家
			$rs = Fun::addNotify( [$uid],$info,'inform' );
		}else{//全服发送
			$rs = Fun::addNotify( [],$info,'inform' );
		}
		

		return $rs['data'];
	}
	
	/**
	 * 踢出玩家下线	
	 * @param int $uid	 
	 * @return void
	 * @access public
	 */
	public static function kick($_P) {
		$uid = (int)$_P['uid'];
	
		User::delOnline($uid);//移出在线列表

		//全服在线玩家推送消息
		$rs = Fun::addNotify( [$uid],[],'kick' );

		return $rs['data'];
	}


	/**
	 * 停机维护		  
	 * @return void
	 * @access public
	 */
	public static function stop_server($_P) {		

		//全服在线玩家推送消息
		$rs = Fun::addNotify( [],[],'stop_server' );
		//$rs = Fun::addNotify( [100000],[],'stop_server' );

		$cache = getCache();
		$cache->set('stop_server',1);
		return [];
	}

	/**
	 * 开启入口
	 * @return void
	 * @access public
	 */
	public static function start_server($_P) {		

		$cache = getCache();
		$cache->set('stop_server',0);

		//初始化公共俱不部房间
		include($global['path']['root'].'_code/gm/Room.php');
		Room_control::initPublicRoom();

		return [];
	}

	/**
	 * 开启入口
	 * @return int status 服务器状态 1=开启状态  0=关闭状态
	 * @access public
	 */
	public static function get_server($_P) {		

		$cache = getCache();
		$rs = (int)$cache->get('stop_server');

		if(!$rs){
			$rs = 0;
		}

		$status = $rs? 0: 1; //1=开启状态  0=关闭状态

		return [
			'status' => $status
		];
	}


	/**
	 * 读取游戏进程数
	 * @return array
	 * @access public
	 */
	public static function getGameProcessNum($_P) {		

		$list = array();

		$cache = getCache();
		$keys = $cache->keys('process_num_*');
		foreach ($keys as $k) {
			$list[] = $cache->getArray($k);
		}

		return $list;
	}
}