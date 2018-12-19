<?php
/**
 * 常量配置
 *
 * @author   HJH
 * @version  2017-6-6
 */


//Memcache缓存配置
define('MEM_CACHE_IP', '127.0.0.1');
define('MEM_CACHE_PROT', 11211);


//Redis缓存配置
define('REDIS_CACHE_IP', 'poker-redis');
define('REDIS_CACHE_PROT', 6379);


//使用的缓存
define('CACHE_TYPE', 'Redis');  //Redis OR Memcache
//超时时间(Cache AND Session)
define('CACHE_TIMEOUT', 3600);


/*
 * 俱乐部相关配置
 */
define('CLUB_MANAGE_SUBAGENT', 1);  //副代理
define('CLUB_MANAGE_CREATOR', 2);   //主代理(创建者)

/*
 * 俱乐部会员权限标识
 */
define('CLUB_PURVIEW_IN', 1);    //批准玩家进入/踢出俱乐部
define('CLUB_PURVIEW_SEND', 2);  //筹码发放/回收
define('CLUB_PURVIEW_DESK', 3);  //管理游戏桌子
define('CLUB_PURVIEW_BUYRB', 4); //钻石与俱乐部币兑换
define('CLUB_PURVIEW_EDIT', 5);  //编辑俱乐部信息

/*
 * 俱乐部会员状态
 */
define('CLUB_MEMBER_ING', 1);     //申请中
define('CLUB_MEMBER_AGREE', 2);   //同意
define('CLUB_MEMBER_REJECT', 3);  //拒绝


define('SMS_SWITCH_FLAG', 1);  //短信验证码开关

/*
 * 来路IP配置
 */
define('GM_MANAGE_IP', '192.168.1.1');   //GM管理工具IP
define('NODE_PHP_IP', '192.168.1.1');    //node调用php来路IP

define('DEFAULT_SMS_CODE',9988); //默认sms验证码