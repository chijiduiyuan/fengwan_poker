<?php
/**
 * 玩家登出
 *
 * @author HJH
 * @version  2017-6-6
 */

if(!$_P){exit;}

session_unset();
session_destroy();