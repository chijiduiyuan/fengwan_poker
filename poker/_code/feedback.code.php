<?php


if(!$_P){exit;}

$uid = (int)$_P['uid'];
$feed_type = trim($_P['feed_type']);
$feed_msg = trim($_P['feed_msg']);

//验证参数
// if($uid){
//     CMD(8888);
// }
// if($feed_type){
//     CMD(7777);
// }
// if($feed_msg){
//     CMD(6666);
// }
if(!$uid || !$feed_type || !$feed_msg) {
	CMD(202);
}
$info = array(
    'uid'       =>$uid,
    'feed_type' =>$feed_type,
    'feed_msg'  =>$feed_msg,
    'feed_sj'   =>date('Y-m-d H:i:s')
);

$feedId = Feed::addFeed($info);

// $cache = getCache();
 //缓存意见信息
// $cache->setArray('feed_id_'.(int)$uid, $rs);


CMD(200,$feedId);