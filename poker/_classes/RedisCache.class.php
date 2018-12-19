<?php
/**
 * Redis缓存类
 *
 * @author HJH
 * @version  2017-6-6
 */
 
class RedisCache {
	
	public $cacheObj; 
	  
	/**
	 * 构造函数
	 */
	function __construct() {

		global $global;
		
		$this->cacheObj = new Redis();
		$this->cacheObj->connect(REDIS_CACHE_IP, REDIS_CACHE_PROT) or die ('EEOR: Redis not connect');
		return $this->cacheObj;
	}


	/**
	 * 字符串类型读取
	 */
	public function get($key) {
		if(!$key){return false;}
		return $this->cacheObj->get($key);
	}
	
	
	/**
	 * 字符串类型写入
	 */
	public function set($key,$value,$timeOut=CACHE_TIMEOUT) {
		if(!$key){echo '::::'.$key;return false;}
		if($timeOut) {
			return $this->cacheObj->set($key,$value,$timeOut);
		}else{
			return $this->cacheObj->set($key,$value);
		}
		
	}


	/**
	 * 数组类型读取
	 */
	public function getArray($key, $field='') {
		if(!$key){return false;}

		if($field) {

			if( is_array($field) ) {
				$val = $this->cacheObj->hmget($key, $field);

			}else{
				$val = $this->cacheObj->hget($key, $field);
			}

		}else{
			$val = $this->cacheObj->hgetall($key);
		}

		return $val;
	}


	/**
	 * 数组类型写入
	 */
	public function setArray($key,$array,$timeOut=CACHE_TIMEOUT) {
		if(!$key){echo '::::'.$key;return false;}

		if($timeOut)$this->delete($key);
		$this->cacheObj->hmset($key,$array);
		if($timeOut)$this->cacheObj->expire($key,$timeOut);
		
		return true;
	}


	/**
	 * 将key的值设为value，当且仅当key不存在。
	 */
	public function SETNX($key, $value) {
		return $this->cacheObj->SETNX($key, $value);
	}


	/**
	 * 删除string
	 */
	public function delete($key) {
		return $this->cacheObj->del($key);
	}


	/**
	 * 删除hash
	 */
	public function hdel($key, $field) {
		return $this->cacheObj->hdel($key, $field);
	}


	/**
	 * 检查给定key是否存在
	 */
	public function exists($key) {
		return $this->cacheObj->exists($key);
	}


	/**
	 * 将key改名为newkey
	 * 当key和newkey相同或者key不存在时，返回一个错误
	 * 当newkey已经存在时，rename命令将覆盖旧值
	 * 改名成功时提示OK，失败时候返回一个错误
	 */
	public function rename($key, $newkey) {
		return $this->cacheObj->rename($key,$newkey);
	}


	/**
	 * 查找符合给定模式的key
	 *
	 * KEYS * 命中数据库中所有key
	 * KEYS h?llo 命中hello、hallo、hxllo 等
	 * KEYS h*llo 命中hllo、heeeeello 等
	 * KEYS h[ae]llo 命中hello、hallo 但不命中hillo
	 */
	public function keys($pattern) {
		if(!$pattern){return [];}
		return $this->cacheObj->keys($pattern);
	}


	/**
	 * 获取key的剩余生存时间(以秒为单位)
	 * 当key不存在或没有设置生存时间时，返回-1
	 */
	public function ttl($key) {
		if(!$key){return false;}
		return $this->cacheObj->ttl($key);
	}


	/**
	 * 设置超时时间
	 */
	public function expire($key, $timeOut=CACHE_TIMEOUT) {
		if(!$key){return false;}
		return $this->cacheObj->expire($key,$timeOut);
	}


	/**
	 * 为哈希表key中的域field的值加上增量increment
	 * 增量也可以为负数，相当于对给定域进行减法操作
	 * 如果key不存在，一个新的哈希表被创建并执行HINCRBY命令
	 * 如果域field不存在，那么在执行命令前，域的值被初始化为0
	 * 对一个储存字符串值的域field执行HINCRBY命令将造成一个错误
	 * 返回值 执行HINCRBY命令之后，哈希表key中域field的值
	 */
	public function hincrby($key, $field, $increment) {
		if(!$key){return false;}
		return $this->cacheObj->hincrby($key, $field, $increment);
	}
}