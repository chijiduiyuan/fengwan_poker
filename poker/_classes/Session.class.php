<?php
/**
 * Session类
 *
 * @author HJH
 * @version  2017-6-6
 */


//session 初始化
Session::sessionStart();
session_start();


class Session {
	
	/**
	 * 构造函数
	 */
	function __construct() {
				
	}

	
	/**
	 * session初始化
	 * @return void
	 * @access public	
	 */
	public static function sessionStart() {
		
		global $global;


		//设置垃圾回收最大生存时间
        ini_set('session.gc_maxlifetime',   CACHE_TIMEOUT);


        //设置缓存类型
        if(CACHE_TYPE=='Redis') {

        	//设置为redis存储方式
	        ini_set("session.save_handler", "redis");
			ini_set("session.save_path", "tcp://".REDIS_CACHE_IP.":".REDIS_CACHE_PROT);

        }elseif(CACHE_TYPE=='Memcache') {

        	//将 session.save_handler 设置为 user，而不是默认的 files
       		session_module_name('user');
        }
		
		
		if(CACHE_TYPE=='Redis' || CACHE_TYPE=='Memcache') {

	        //定义 SESSION 各项操作所对应的方法名
	        session_set_save_handler(
	            array('Session', 'sessionOpen'),
	            array('Session', 'sessionClose'),
	            array('Session', 'sessionRead'),
	            array('Session', 'sessionWrite'),
	            array('Session', 'sessionDestroy'),
	            array('Session', 'sessionGC')
	        );
	    }
	}
	
	
	/**
	 * session 打开操作
	 * @return void
	 * @access public
	 */
	public static function sessionOpen($save_path, $session_name) {
		getCache();
        return true;
    }

	
 	/**
	 * session 关闭操作
	 * @return void
	 * @access public
	 */
    public static function sessionClose() {
        return true;
    }

 
 	/**
	 * session 读操作
	 * @return array 返回要读取的session数据
	 */
    public static function sessionRead($sesskey) {
		$_cache = getCache();
		return $_cache->get($sesskey);
	}

	
	/**
	 * session 写操作
	 * @return void
	 * @access public
	 */
    public static function sessionWrite($sesskey, $data) {
		$_cache = getCache();
		
		if($_cache->set($sesskey,$data)) {
			return true;
		}else{
			return false;
		}
		
	}
	

	/**
	 * session 销毁
	 * @return void
	 * @access public
	 */	
	public static function sessionDestroy($sesskey) {
		$_cache = getCache();
		$_cache->delete($sesskey);
		return true;
	}
	

	/**
	 * session 垃圾清理
	 * @return void
	 * @access public
	 */
	public static function sessionGC($maxlifetime = null) {
		return true;
	}
}