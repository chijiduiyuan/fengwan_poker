<?php
/**
 * 检查邀请人uid模块
 *
 * @author HJH
 * @version  2017-6-6
 */

if(!$_P){exit;}

$clubId = (int)$_P['clubId'];
$inviteUid = (int)$_P['inviteUid'];

if(!$clubId || !$inviteUid){
    CMD(202);
}

//推广员信息
$info = Club::getTgList($clubId,$inviteUid);


CMD(200,$info);