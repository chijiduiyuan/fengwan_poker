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
	 * 发送全服通告
	 * @param string $msg 消息内容
	 * @param int $loop 循环播放次数
	 * @param int $loopTs 循环播放间隔时间 单位秒
	 * @param string $color red=红色 white=白色(默认) yellow=黄色    
	 * @return void
	 * @access public
	 */
	public static function add($_P) {

		$msg 	= trim($_P['msg']);
		$color  = trim($_P['color'])?$_P['color']:'white';
		if(!$msg ){
			CMD('ILLEGAL_PARAM');
		}
		$rs = postUrl([		
			'uid'		=> $_P['uid'],
			'msg' 		=> $msg,
			'loop' 		=> $_P['loop']?(int)$_P['loop']:1,
			'loopTs' 	=> $_P['loopTs']?(int)$_P['loopTs']:1,
			'color' 	=> $color			
		]);

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


		if(!$uid){
			CMD('ILLEGAL_PARAM');
		}

		$rs = postUrl([					
			'uid' 	=> $uid			
		]);

		return $rs['data'];	
	}
	
	/**
	 * 停机维护	
	 * @param int $uid	 
	 * @return void
	 * @access public
	 */
	public static function stop_server($_P) {

		$rs = postUrl([				
			
		]);

		return $rs['data'];	
	}

	/**
	 * 开启游戏
	 * @param int $uid	 
	 * @return void
	 * @access public
	 */
	public static function start_server($_P) {

		$rs = postUrl([				
			
		]);

		return $rs['data'];	
	}

	/**
	 * 取服务器状态	
	 * @param int $uid	 
	 * @return status  服务器状态 1=关闭状态  0=开启状态
	 * @access public
	 */
	public static function get_server($_P) {

		$rs = postUrl([				
			
		]);

		return $rs['data'];	
	}

	/**
	 * 读取游戏进程数	
	 * @param int $uid	 
	 * @return status  服务器状态 1=关闭状态  0=开启状态
	 * @access public
	 */
	public static function getGameProcessNum($_P) {

		$rs = postUrl([
			
		]);

		return $rs['data'];	
	}
}