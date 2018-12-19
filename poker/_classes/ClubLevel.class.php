<?php
/**
 * 俱乐部等级商店操作类
 *
 * @author HJH
 * @version  2017-6-18
 */


class  ClubLevel{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}



	/**
	 * 取等级商品信息
	 * @param string $param 字段
	 * @param int $id 商品id
	 * @return array 商品信息
	 * @access public
	 */
	public static function get( $param='*', $id=0 ) {				
		
		$DB = useDB();

		if($id) {
			return $DB->getValue('SELECT '.$param.' FROM club_level_shop WHERE id=' . (int)$id . ' AND status=1');
		}else{
			return $DB->getList('SELECT '.$param.' FROM club_level_shop WHERE status=1 ORDER BY orders ASC');
		}
		
	}


}