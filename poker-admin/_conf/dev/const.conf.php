<?php
/**
 * 常量配置
 *
 * @author   HJH
 * @version  2017-7-28
 */


//管理员密码加密串
define('LOGIN_SECRET_KEY', 'gm_tool_key');

//Memcache缓存配置
define('MEM_CACHE_IP', '127.0.0.1');
define('MEM_CACHE_PROT', 11211);


//Redis缓存配置
define('REDIS_CACHE_IP', '127.0.0.1');
define('REDIS_CACHE_PROT', 6379);


//使用的缓存
define('CACHE_TYPE', 'Redis');  //Redis OR Memcache
//超时时间(Cache AND Session)
define('CACHE_TIMEOUT', 3600);

//分页默认条数
define('PAGE_SIZE', 20);