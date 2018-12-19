<?php
/**
 * 数据库操作类 （继承PDO）
 *
 * @author HJH
 * @version  2017-6-6
 */
 
class DB extends PDO {	
	
	
	/**
	 * 调试开关 0=关闭 | 1=直接输出 | 2=保存不输出
	 * @var integer $debug
	 */	
	public $debug=0;
	

	/**
	 * 调试信息
	 * @var string $debugMsg
	 */	
    public $debugMsg = '';	
	

	/**
	 * 错误输出 0=关闭 | 1=输出 | 2=保存到数据库
	 * @var integer $errorOutput
	 */	
	public $errorOutput = 1;
	

	/**
	 * 构造函数
	 * @var array $dbConf 数据库配置
	 */
	function __construct($dbConf) {	
		
		$dbURL = "{$dbConf['provider']}:host={$dbConf['host']};dbname={$dbConf['dbName']}";
		try {
			parent::__construct($dbURL, $dbConf['userName'], $dbConf['passWord']);
			//$this->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); //设置属性 强制列名小写
			//$this->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);  //将数值转换为字符串 false
			$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);  //将数值转换为字符串 false
		    $this->query('SET NAMES '.$dbConf['charset']); //设置数据库编码
		
		} catch (PDOException $e) {
			die("Error: " . $e->__toString() . "<br/>");
			exit;
		}
	}


	/**
	 * 根据SQL语句从数据表中取数据，只取第一条记录的值，
	 * 如果记录中只有一个字段，则只返回字段值。
	 * @param string $sql sql语句
	 * @param array $paramAry sql绑定参数（存在则使用参数化查询）
	 * @return array  返回查询的结果
	 * @access public
	 */
	public function getValue($sql,$paramAry=NULL) {
			
		$sql .= ' LIMIT 0,1' ;
		if($paramAry){		
			//参数化查询
			$result = $this->query_p($sql,$paramAry,'one');	
		} else {
			//常规查询		
			$result =  $this->query_m($sql,'one');	
		}
	
		if( $result ){
			$result = ( count($result)<=1 ) ? current($result) : $result;
		}

		return $result;
	}

		
	/**
	 * 取多条记录
	 * @param string $sql sql语句
	 * @param array $paramAry sql绑定参数（存在则使用参数化查询）
	 * @param integer $form 开始行数
	 * @param integer $to 结束行数	 
	 * @return array 返回查询的结果
	 * @access public
	 */
	public function getList($sql,$paramAry=NULL,$form=0,$to=0) {			
		
		if($form){
			$form--;
		} else {
			$form = 0;
		}
		
		if ($to) {		
			$to  -= $form;
			$sql .=' LIMIT '. $form. ','. $to;
		}		
		
		if($paramAry){		
			//参数化查询
			return $this->query_p($sql,$paramAry,'all');	
		} else {
			//常规查询		
			return $this->query_m($sql,'all');	
		}
	}

				
	/**
	 * 更新记录
	 * @param string $tableName 表名
	 * @param array $fieldArray 字段数组
	 * @param string $where 条件 	 	 
	 * @return integer  返回更新记录数
	 * @access public
	 */
	public function update($tableName,$paramAry,$where=NULL) {
			
		$fieldAry = array();
		foreach($paramAry as $key=>$value){
			$fieldAry[] = "{$key}=:{$key}";
		}	
		if($where){
			$where = ' WHERE '.$where;
		}		
		$fieldStr 	= implode(',',$fieldAry);
		$sql 		= "UPDATE {$tableName} SET {$fieldStr} {$where}";		
		
		$exe 		= $this->query_p($sql,$paramAry);
		return $exe->rowCount();		
	}

	
	/**
	 * 插入一条记录
	 * @param string $tableName 表名
	 * @param array $paramAry sql绑定参数数组 	 
	 * @return integer 返回添加的主键(id)值
	 * @access public
	 */	
	public function insert($tableName,$paramAry) {				
		
		$fieldAry = array();
		$valueList = array();
		foreach($paramAry as $key=>$value){
			$fieldAry[] = $key;				
			$fieldParm[] = ':'.$key;								
			$valueList[] = $value;				
		}		
		
		$fieldStr 	  = implode(',',$fieldAry);
		$fieldParmStr = implode(',',$fieldParm);
		$sql = "INSERT INTO {$tableName} ({$fieldStr})VALUES({$fieldParmStr})";

		$exe 		  = $this->query_p($sql,$paramAry);
		return PDO::lastInsertId();	
	}

		
	/**
	 * 删除记录
	 * @param string $tableName 表名 
	 * @param string $where 条件
	 * @param array $paramAry sql绑定参数数组 （存在则转为参数化sql） 
	 * @return bool $result 删除成功返回true,否则返回false
	 * @access public
	 */
	public function delete($tableName,$where,$paramAry=NULL) { 					
		
		$where = ' WHERE '.$where;		
		$sql = 'DELETE FROM '. $tableName.$where;	
		
		if( $paramAry ){
			//参数化sql
			$exe 	= $this->query_p($sql,$paramAry);
			$result = $exe->rowCount();
		} else {
			//常规sql
			$result = $this->exec($sql);
		}
		$this->checkDebug($sql);
		return $result;
	}

			
	/**
	 * 取记录数
	 * @param string $tableName 表名 
	 * @param string $where 条件
	 * @param array $paramAry sql绑定参数数组 （存在则转为参数化sql） 	 
	 * @return integer 返回记录数
	 * @access public
	 */
	public function getCount($tableName,$where=NULL,$paramAry=NULL) {
		
		$sql = 'SELECT COUNT(*) AS recordcount FROM '. $tableName;
		if($where){
			$sql .= ' WHERE '.$where;
		}	
			
		return (int)$this->getValue($sql,$paramAry);			
	}


	/**
	 * 取自增值(Auto_increment)
	 * @param string $tableName 表名  
	 * @return integer 返回自增值
	 * @access public
	 */
	public function getAutoIncrement($tableName) {
		
		if(!$tableName) return;
		
		$sql = 'show table status where name ="'.$tableName.'"';
		
		$query = $this->query($sql);
		$info = $query->fetch();
		
		return (int)$info['Auto_increment'];

	}

	
	/**
	 * 执行常规sql查询(带返回值)
	 * @param string $sql sql语句
	 * @return array $result 返回查询的结果
	 * @access public
	 */
	private function query_m($sql,$returnRow='one') {
	
		$rs = $this->query($sql);
		
		if($rs===false){
			//SQL执行错误捕捉	
			$this->sqlError($this->errorInfo(),$sql);
		}
		
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		
		if($returnRow=='all'){
			$result = $rs->fetchAll();
		} else {
			$result = $rs->fetch();
		}
		
		$this->checkDebug($sql);
		return $result;
	}

		
	/**
	 * 执行参数化sql查询
	 * @param string $sql sql语句
	 * @param array $paramAry sql参数化绑定参数
	 * @param string $returnType 返回数据类型（默认=不返回 'one'=返回一条记录 'all'=返回所有记录）
	 * @return array $result 返回查询的结果
	 * @access public
	 */
	private function query_p($sql,$paramAry,$returnType=NULL) {
	
		$stmt = $this->prepare($sql);
		//绑定参数
		foreach($paramAry as $key=>$value){
						
			if( gettype($value)=='integer' ){
				$dataType = PDO::PARAM_INT;
			} else {
				$dataType = PDO::PARAM_STR;
			}			
			$stmt->bindParam(':'.$key, $paramAry[$key]);		
		}		
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		
		
		if( !$stmt->execute() ){
			//SQL执行错误捕捉	
			$this->sqlError($stmt->errorInfo(),$sql,$paramAry);
		}
		switch($returnType){		
		case 'one':
			$result = $stmt->fetch();		
			break;
		case 'all':
			$result = $stmt->fetchAll();
			break;	
		default:
			$result = $stmt;
			break;
		}	
		$this->checkDebug($sql,$paramAry);
	
		return $result;	
	}

		
	/**
	 * 执行sql(不带返回值)
	 * @param string $sql sql语句
	 * @param array $paramAry sql绑定参数数组 （存在则转为参数化sql） 
	 * @return bool $result 操作成功返回true,否则返回false
	 * @access public
	 */
	public function exeSql($sql,$paramAry=NULL) { 					
		
		if( $paramAry ){
			//参数化sql
			$result = $this->query_p($sql,$paramAry);		
		} else {
			//常规sql
			$result = $this->exec($sql);
			if( $result===false ){
				//SQL执行错误捕捉	
				$this->sqlError($this->errorInfo(),$sql);
			}
		}
			
		return $result;
	}

	
	/**
	 * debug 操作
	 * @param string $sql 条件	
	 * @param array $paramAry sql绑定参数数组 （存在表示当前为参数化sql） 	 	  
	 * @return void
	 * @access public
	 */
	public function checkDebug($msg,$paramAry=NULL) {		
	
		if(!$this->debug){
			return false;
		}
		
		if($paramAry){
			$ary = array();
			foreach($paramAry as $key=>$value){				
				$ary[] = "{$key}:$value";								
			}	
			$msg .= '<br>PARAM:'.implode(',',$ary).'<br>';
			
		}
		$msg .='<br><br>';
	    if($this->debug==1){
			echo $msg;
		}else if($this->debug==2){
			$this->debugMsg .= $msg;
		}
	}

	
	/**
	 * 执行sql错误输出
	 * @param string $errorMsg 错误信息
	 * @param string $sql 执行的sql
	 * @param array $paramAry sql绑定参数 数组
	 * @return void
	 * @access public
	 */
	private function sqlError($errorInfo, $sql='', $paramAry=NULL) {

		$errorMsg = "SQL：{$sql}<br>ErrorCode：{$errorInfo[0]} ({$errorInfo[1]})<br>Error Describe ：{$errorInfo[2]}";
		
		switch($this->errorOutput) {

		//直接输出
		case 1:
			die($errorMsg);
			break;

		//保存到数据库
		case 2:
			Log::write($errorMsg,AID);
			break;
		}
	}
}