<?php
/**
 * 玩家操作类
 *
 * @author HJH
 * @version  2017-6-6
 */


class  Feed{
    /**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
    }
    
    public static function addFeed($info){
        $DB = useDB();
        $feedId = $DB->insert('feed',$info);
        return $feedId;
    }
}