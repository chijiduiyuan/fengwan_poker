<?php

//关闭错误提示(生产环境使用)
ini_set("display_errors",0);
error_reporting(E_ALL ^ E_NOTICE);
ini_set("log_errors",1);
// error_reporting(E_ALL ^ E_NOTICE);

// 跨域设置
// header("Access-Control-Allow-Origin: http://localhost:8080");
// header("Access-Control-Allow-Credentials : true");

//响应类型
// header("Access-Control-Allow-Methods:HEAD,POST,GET,PUT,DELETE,OPTIONS");
// header("Access-Control-Max-Age:60");

 //响应头设置
// header('Access-Control-Allow-Headers:x-requested-with,content-type');



//输出格式
header('Content-Type: application/json; charset=UTF-8');

//时区
function_exists('date_default_timezone_set') && date_default_timezone_set('PRC');