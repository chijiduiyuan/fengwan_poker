<?php
/**
 * 国家配置操作类
 *
 * @author HJH
 * @version  2017-6-18
 */


class  Country{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 读取国家信息
	 * @param string $field 字段
	 * @param string $where 条件
	 * @return array 信息
	 * @access public
	 */
	public static function getValue($field, $where) {
		$DB = useDB();
		return $DB->getValue('SELECT '.$field.' FROM country WHERE '.$where);
	}


	/**
	 * 读取国家列表
	 * @param string $field 字段
	 * @param string $where 条件
	 * @return array 信息
	 * @access public
	 */
	public static function getList($field, $where) {
		$DB = useDB();
		return $DB->getList('SELECT '.$field.' FROM country WHERE '.$where);
	}



}