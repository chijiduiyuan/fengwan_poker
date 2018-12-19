<?php
/**
 * Pay - 支付 相关操作接口
 *
 * @author CXF
 * @version  2017-8-4
 */


class  Pay_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {
		
	}

	/**
	 * 取支付列表
	 * @param string $curPage 当前页
	 * @param string $pageSize 每页条数
	 * @return array 列表
	 * @access public
	 */
	public static function getList($_P) {
		$rs = postUrl($_P);
		return $rs['data'];
	}


	/**
	 * 取支付详情
	 * @param int $id 支付记录id
	 * @return array 详情
	 * @access public
	 */
	public static function getValue($_P) {
		$rs = postUrl($_P);
		return $rs['data'];
	}


	/**
	 * 确认扣款成功
	 * @param int $id 支付记录id
	 * @return array 详情
	 * @access public
	 */
	public static function check($_P) {

		$rs = postUrl([
			'id'=>$_P['id'],
			'aid'=>AID
		]);
		return $rs['data'];
	}


	/**
	 * 删除未支付记录
	 * @param int $num 删除几月前
	 * @return bool 是否成功
	 * @access public
	 */
	public static function delPay($_P) {
		$rs = postUrl($_P);
		return $rs['data'];
	}



	/**
	 * 读取日流水数据
	 * @access public
	 */
	public static function getDay($_P) {

		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE;

		$limitForm = ($curPage-1)*$pageSize;

		$DBGM = useDBGM();

		$year  = $_P['year'];
		$month = $_P['month'];
		$day   = $_P['day'];

		$where = '1=1';
		if($year && $month && $day) {
			$dayTime = mktime(0,0,0,$month,$day,$year);
			$where = 'time='.$dayTime;
		}

		$total = $DBGM->getCount('pay_day', $where);
		$list  = $DBGM->getList('SELECT id,dateTime,xhWepay,xhQqpay,xhWebpay,total FROM pay_day WHERE '.$where.' ORDER BY time DESC LIMIT '.$limitForm.','.$pageSize);

		return [
			'list' 	=> $list,
			'total' => $total
		];
	}



	/**
	 * 读取月流水数据
	 * @access public
	 */
	public static function getMonth($_P) {

		$curPage  = $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize = $_P['pageSize']?(int)$_P['pageSize']:PAGE_SIZE;

		$limitForm = ($curPage-1)*$pageSize;

		$DBGM = useDBGM();

		$year  = $_P['year'];
		$month = $_P['month'];

		$where = '1=1';
		if($year && $month) {
			$dayTime = mktime(0,0,0,$month,1,$year);
			$where = 'time='.$dayTime;
		}

		$total = $DBGM->getCount('pay_month', $where);
		$list  = $DBGM->getList('SELECT id,dateTime,xhWepay,xhQqpay,xhWebpay,total FROM pay_month WHERE '.$where.' ORDER BY time DESC LIMIT '.$limitForm.','.$pageSize);

		return [
			'list' 	=> $list,
			'total' => $total
		];
	}




	
	/**
	 * 定时统计(日流水)
	 * @access public
	 */
	public static function countDay($_P) {

		$DBGM = useDBGM();

		//生成今天零点时间戳
		$dayTime = mktime(0,0,0,date('m'),date('d'),date('Y'));

		//读取当前最新一条的时间
		$time = (int)$DBGM->getValue('SELECT time FROM pay_day ORDER BY id DESC');
		
		if($time==$dayTime) {
			
			//更新今天统计
			$rs = postUrl([
				'startTime' => $dayTime,
				'endTime'   => $dayTime+86400,
			]);
			$info = $rs['data'];
			$DBGM->update('pay_day', array('xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay'],
					'xhWebpay'=>(int)$info['xhWebpay'],'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']), 'time='.$time);


		}elseif($time<$dayTime) {
			
			if($time>0) {

				//更新当天统计
				$rs = postUrl([
					'startTime' => $time,
					'endTime'   => $time+86400,
				]);
				$info = $rs['data'];
				$DBGM->update('pay_day', array('xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay'],
					'xhWebpay'=>(int)$info['xhWebpay'],'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']), 'time='.$time);

				//新增后一天统计
				$time += 86400;
				$rs = postUrl([
					'startTime' => $time,
					'endTime'   => $time+86400,
				]);
				$info = $rs['data'];
				$DBGM->insert('pay_day', array('time'=>$time,'dateTime'=>date('Y-m-d',$time),'xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay'],'xhWebpay'=>(int)$info['xhWebpay'],
					'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']));


			}else{

				//统计从今天开始
				$rs = postUrl([
					'startTime' => $dayTime,
					'endTime'   => $dayTime+86400,
				]);
				$info = $rs['data'];
				$DBGM->insert('pay_day', array('time'=>$dayTime,'dateTime'=>date('Y-m-d',$dayTime),'xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay'],'xhWebpay'=>(int)$info['xhWebpay'],
					'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']));
			}
		}
	}



	/**
	 * 定时统计(月流水)
	 * @access public
	 */
	public static function countMonth($_P) {

		$DBGM = useDBGM();

		//生成本月1日零点时间戳
		$startTime = mktime(0,0,0,date('m'),1,date('Y'));
		$endTime   = strtotime('+ 1 month', $startTime);

		//读取当前最新一条的时间
		$time = (int)$DBGM->getValue('SELECT time FROM pay_month ORDER BY id DESC');
		
		if($time==$startTime) {
			
			//更新本月统计
			$rs = postUrl([
				'startTime' => $startTime,
				'endTime'   => $endTime,
			]);
			$info = $rs['data'];
			$DBGM->update('pay_month', array('xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay']
				,'xhWebpay'=>(int)$info['xhWebpay'],'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']), 'time='.$time);


		}elseif($time<$startTime) {
			
			if($time>0) {

				//更新当月统计
				$rs = postUrl([
					'startTime' => $time,
					'endTime'   => strtotime('+ 1 month', $time),
				]);
				$info = $rs['data'];
				$DBGM->update('pay_month', array('xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay']
				,'xhWebpay'=>(int)$info['xhWebpay'],'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']), 'time='.$time);

				//新增后一月统计
				$time = strtotime('+ 1 month', $time);
				$rs = postUrl([
					'startTime' => $time,
					'endTime'   => strtotime('+ 1 month', $time),
				]);
				$info = $rs['data'];
				$DBGM->insert('pay_month', array('time'=>$time,'dateTime'=>date('Y-m',$time),'xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay'],'xhWebpay'=>(int)$info['xhWebpay'],
					'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']));


			}else{

				//统计从本月开始
				$rs = postUrl([
					'startTime' => $startTime,
					'endTime'   => $endTime,
				]);
				$info = $rs['data'];
				$DBGM->insert('pay_month', array('time'=>$startTime,'dateTime'=>date('Y-m',$startTime),'xhWepay'=>(int)$info['xhWepay'],'xhQqpay'=>(int)$info['xhQqpay'],'xhWebpay'=>(int)$info['xhWebpay'],
					'total'=>(int)$info['xhWepay']+(int)$info['xhQqpay']+(int)$info['xhWebpay']));
			}
		}
	}


		
}