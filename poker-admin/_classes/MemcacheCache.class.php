<?php
/**
 * Memcache缓存类
 *
 * @author HJH
 * @version  2017-6-6
 */
 
class MemcacheCache {	
	
	public $cacheObj; 
	  
	/**
	 * 构造函数
	 */
	function __construct() {

		global $global;
		
		$this->cacheObj = new Memcache();
		$this->cacheObj->connect(MEM_CACHE_IP, MEM_CACHE_PROT) or die ('EEOR:Memcache not connect');
		return $this->cacheObj;
	}


	public function get($key) {
		if(!$key){return false;}
		return $this->cacheObj->get($key);
	}
	
	
	public function set($key,$value,$timeOut=CACHE_TIMEOUT) {
		if(!$key){echo '::::'.$key;return false;}
		return $this->cacheObj->set($key,$value,0,$timeOut);
	}


	public function add($key,$value) {
		return $this->cacheObj->add($key,$value);
	}


	public function delete($key) {
		if(strlen($key)==''){return false;}
		return $this->cacheObj->delete($key);
	}

	
	public function flush() {
		return $this->cacheObj->flush();
	}
}