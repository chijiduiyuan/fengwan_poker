<?php
/**
 * Version - 游戏版本管理 相关操作接口
 *
 * @author CXF
 * @version  2017-8-5
 */


class  Version_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}
	

	/**
	 * 取版本列表 
	 * @param  array 搜索条件
	 * @return array 版本列表
	 * @access public
	 */
	public static function getList($_P) {

		$DBGM = useDBGM();

		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE;

		$where = '1=1';
		//设备搜索
		if( $_P['device']!='' ) {
			$where .= ' AND device='.(int)$_P['device'];
		}
		//状态搜索
		if( $_P['status']!='' ) {
			$where .= ' AND status='.(int)$_P['status'];
		}

		$total = $DBGM->getCount('game_version',$where);

		$limitForm = ($curPage-1)*$pageSize;
		$where .= ' ORDER BY vcode DESC LIMIT '.$limitForm.','.$pageSize;
		$list  = $DBGM->getList('SELECT * FROM game_version WHERE '.$where);

		return [
			'total' => $total,
			'list' => $list
		];
	}//getList

	/**
	 * 添加版本
	 * @param array 版本详情
	 * @return void
	 * @access public
	 */
	public static function add($_P) {
		$DBGM = useDBGM();
				
		if( !$_P['title'] || !$_P['txt'] ||!$_P['vcode']||!$_P['url'] ) {
			CMD('ILLEGAL_PARAM');
		}

		//判断版本号有效性(只能唯一且递增)
		$id = (int)$DBGM->getValue('SELECT id FROM game_version WHERE vcode>='.(int)$_P['vcode']);
		if($id) {
			CMD('ILLEGAL_PARAM_VCODE');
		}

		$info = [
			'device'	=> (int)$_P['device'],
			'title'		=> $_P['title'],
		 	'vcode'		=> (int)$_P['vcode'],
			'url'		=> $_P['url'],
			'isForceUpdate'	=> (int)$_P['isForceUpdate'],
			'isHotfix'		=> (int)$_P['isHotfix'],
			'txt'			=> $_P['txt'],
			'packageSize'	=> (int)$_P['packageSize'],
			'status'		=> (int)$_P['status'],
			'create_time'	=> time()
		];

		$rs = $DBGM->insert('game_version',$info);
		if(!$rs){
			CMD('FAILE');
		}
		
	}//add

	/**
	 * 修改版本
	 * @param int $id 版本id
	 * @param array 版本详情
	 * @return void
	 * @access public
	 */
	public static function edit($_P) {
				
		$DBGM = useDBGM();

		$id = $_P['id'];

		//用户名		
		if( !$id || !$_P['title'] || !$_P['txt'] || !$_P['vcode'] || !$_P['url'] ) {
			CMD('ILLEGAL_PARAM');
		}

		$info = [
			'device'	=> (int)$_P['device'],
			'title'		=> $_P['title'],
		 	'vcode'		=> (int)$_P['vcode'],
			'url'		=> $_P['url'],
			'isForceUpdate'	=> (int)$_P['isForceUpdate'],
			'isHotfix'		=> (int)$_P['isHotfix'],
			'txt'			=> $_P['txt'],
			'packageSize'	=> (int)$_P['packageSize'],
			'status'		=> (int)$_P['status'],
			'create_time'	=> time()
		];

		$rs = $DBGM->update('game_version',$info,'id='.$id);
		if(!$rs){
			CMD('FAILE');
		}
		
	}


	/**
	 * 锁定/解锁 版本
	 * @param int $id 版本id
	 * @param int $status 状态 1=正常 0=冻结
	 * @return void
	 * @access public
	 */
	public static function changeStatus($_P) {	
		$DBGM = useDBGM();

		//玩家id
		$id = (int)$_P['id'];
		if($id<=0) {
			CMD('ILLEGAL_PARAM');
		}

		$status = (int)$_P['status'] ? 1 : 0;
		$info = [
			'status' => $status
		];
		$rs = $DBGM->update('game_version',$info,'id='.$id);
		if(!$rs){
			CMD('FAILE');
		}
	}

	/**
	 * 删除版本
	 * @param int array $ids 版本uid列表
	 * @return void
	 * @access public
	 */
	public static function delete($_P) {	
		$DBGM = useDBGM();
		$idArr  = $_P['ids'];

		if( !is_array($idArr) || sizeof($idArr)==0 )  {
			CMD('ILLEGAL_PARAM');
		}
		//id必须是整形
		foreach ($idArr as $item) {
			if( !is_numeric( $item ) ){
				CMD('ILLEGAL_PARAM');
			}
		}
		$ids   = implode(',',$idArr);
		$where = 'id in ('.$ids.')';
		$rs = $DBGM->delete('game_version',$where);
		if(!$rs){
			CMD('FAILE');
		}
		
	}


	/**
	 * 生成配置文件
	 * @access public
	 */
	public static function makefile($_P) {
		global $global;

		$DBGM = useDBGM();


		//android
		$info = $DBGM->getValue('SELECT url,isForceUpdate,txt,vcode,title FROM game_version WHERE device=1 AND status=1 ORDER BY vcode DESC');
		if($info) {
			$data = array(
				"downloadUrl" => $info['url'],
				"forceUpdate" => $info['isForceUpdate'],
				"updateLog"   => $info['txt'],
				"versionCode" => $info['vcode'],
				"versionName" => $info['title']
			);

			$content  = '';
			$content .= '<?php'.chr(13).chr(13);
			$content .= 'return ';
			$content .= var_export($data, true);
			$content .= ';';


			//判断目录是否存在
			if(!is_dir($global['path']['output'])) {
				@mkdir($global['path']['output'], 0777);
			}
			
			file_put_contents( $global['path']['output'] . 'android.php', $content);
		}

		//ios
		$info = $DBGM->getValue('SELECT url,isForceUpdate,txt,vcode,title FROM game_version WHERE device=2 AND status=1 ORDER BY vcode DESC');
		if($info) {
			$data = array(
				"downloadUrl" => $info['url'],
				"forceUpdate" => $info['isForceUpdate'],
				"updateLog"   => $info['txt'],
				"versionCode" => $info['vcode'],
				"versionName" => $info['title']
			);

			$content  = '';
			$content .= '<?php'.chr(13).chr(13);
			$content .= 'return ';
			$content .= var_export($data, true);
			$content .= ';';
			
			//判断目录是否存在
			if(!is_dir($global['path']['output'])) {
				@mkdir($global['path']['output'], 0777);
			}
			
			file_put_contents( $global['path']['output'] . 'ios.php', $content);
		}
		
	}

}