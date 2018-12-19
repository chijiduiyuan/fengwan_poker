<?php
// 跨域设置
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Credentials : true");

//响应类型
header("Access-Control-Allow-Methods:HEAD,POST,GET,PUT,DELETE,OPTIONS");
header("Access-Control-Max-Age:60");
 //响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');