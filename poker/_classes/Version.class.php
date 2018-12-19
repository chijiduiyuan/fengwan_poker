<?php

class  Version{
    /**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
    }
    
    public static function getValue($sys) {		
		$DB = useDB();
		return $DB->getValue('SELECT * FROM version WHERE sys=' . (int)$sys);
	}
}