<?php
/**
 * Pay - 支付 相关操作接口
 *
 * @author CXF
 * @version  2017-8-2
 */


class  Pay_control{	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}

	/**
	 * 取支付列表 
	 * @param  array 搜索条件
	 * @return array 支付列表
	 * @access public
	 */
	public static function getList($_P) {
		
		$DB = useDB();

		//支付时间
		$startTime = (int)$_P['startTime'];
		$endTime   = (int)$_P['endTime'];

		//玩家信息
		$uid 		= (int)$_P['uid'];
		$username	= $_P['username'];
		$nickname	= $_P['nickname'];

		//订单号
		$order_no	= $_P['orderNo'];

		//第三方订单号
		$pay_id	    = $_P['payId'];

		//充值状态
		$status  	= $_P['status'];

		//支付方式
		$pay_type  	= $_P['payType'];


		$curPage 	= $_P['curPage']?(int) $_P['curPage']:1;
		$pageSize 	= $_P['pageSize']?(int)$_P['pageSize']:20;

		$whereArr = [];
		$whereArr[] = '1=1';

		//支付时间
		if( $startTime ){
			$whereArr[] = 'pr.pay_time>='.$startTime;
		}
		if( $endTime ){
			$whereArr[] = 'pr.pay_time<='.$endTime;
		}

		//玩家信息
		if( $uid ){
			$whereArr[] = 'pr.uid='.$uid;
		}
		if( $username ){
			$whereArr[] = 'user.username=\''.$username.'\'' ;
		}	
		if( $nickname ){
			$whereArr[] = 'user.nickname=\''.$nickname.'\'' ;
		}

		//订单号
		if( $order_no ){
			$whereArr[] = 'pr.order_no=\''.$order_no.'\'' ;
		}

		//第三方订单号
		if( $pay_id ){
			$whereArr[] = 'pr.pay_id=\''.$pay_id.'\'' ;
		}

		//充值状态
		if( is_numeric($status) ){
			$whereArr[] = 'pr.status='.$status;
		}

		//支付方式
		if( is_numeric($pay_type) ){
			$whereArr[] = 'pr.pay_type='.$pay_type;
		}

		$where = implode(' AND ',$whereArr);
		$total = (int)$DB->getValue('SELECT count(pr.id) FROM pay_record pr LEFT JOIN user ON pr.uid=user.uid WHERE '.$where);

		$limitForm = ($curPage-1)*$pageSize;

		$where .= ' ORDER BY id DESC LIMIT '.$limitForm.','.$pageSize;
	
		$list = $DB->getList('SELECT pr.*,user.username,user.nickname,user.avatar FROM pay_record pr LEFT JOIN user ON pr.uid=user.uid WHERE '.$where);
		
		return [
			'list' 	=> $list,
			'total' => $total
		];
	}//getList


	/**
	 * 取支付详情
	 * @param  int 支付记录id
	 * @return array 支付详情
	 * @access public
	 */
	public static function getValue($_P) {
		
		$DB = useDB();
		$info = $DB->getValue('SELECT pr.*,user.username,user.nickname,user.avatar FROM pay_record pr LEFT JOIN user ON pr.uid=user.uid WHERE pr.id='.(int)$_P['id']);
		if(!$info) {
			CMD('FAILE');
		}
		return $info;
	}//getValue


	/**
	 * 确认扣款成功
	 * @param  int 支付记录id
	 * @return bool 是否成功
	 * @access public
	 */
	public static function check($_P) {
		
		$DB = useDB();

		//判断是否已支付成功
		$info = $DB->getValue('SELECT uid,num,extra,status FROM pay_record WHERE id='.(int)$_P['id']);
		if( !$info || (int)$info['status'] ) {
			CMD('FAILE');
		}

		//支付成功
		$ary = array(
			'status'   => 1,
			'note'     => '管理员确认扣款成功，管理员aid：'.$_P['aid'],
			'pay_time' => time(),
			'pay_date' => date('Y-m-d H:i:s')
		);

		$DB->beginTransaction();  //开启事务

		//修改状态
		$rs1 = $DB->update('pay_record', $ary, 'id='.(int)$_P['id']);

		//新增rmb
		$rs2 = User::rmb(((int)$info['num']+(int)$info['extra']), true, (int)$info['uid']);
		
		if($rs1 && $rs2) {
			$DB->commit();    //提交
			$infoUpdate = [];		
			$infoUpdate['rmb'] = $rs2;
			Fun::addNotify( [(int)$info['uid']], $infoUpdate,'user_info' );
		}else{
			$DB->rollBack();  //回滚
			CMD('FAILE');
		}


	}//check


	/**
	 * 删除支付记录
	 * @param  int $num 删除几月前
	 * @return bood 是否成功
	 * @access public
	 */
	public static function delPay($_P) {
		
		$num = (int)$_P['num'];
		if($num<=0) {
			return;
		}
		$time = strtotime('- '.$num.' month');
		$DB = useDB();
		$DB->delete('pay_record', 'status=0 AND order_time<'.$time);

	}//countDay







	/**
	 * 定时统计(日流水)
	 * @return array 统计信息
	 * @access public
	 */
	public static function countDay($_P) {
		
		//支付时间
		$startTime = (int)$_P['startTime'];
		$endTime   = (int)$_P['endTime'];

		$DB = useDB();

	
		$xhWepay = (int)$DB->getValue('SELECT SUM(price) FROM pay_record WHERE pay_type=5 AND pay_time>='.$startTime.' AND pay_time<'.$endTime);
		$xhQqpay  = (int)$DB->getValue('SELECT SUM(price) FROM pay_record WHERE pay_type=6 AND pay_time>='.$startTime.' AND pay_time<'.$endTime);

		$xhWebpay  = (int)$DB->getValue('SELECT SUM(price) FROM pay_record WHERE pay_type=7 AND pay_time>='.$startTime.' AND pay_time<'.$endTime);

		return [
			'xhWepay' => $xhWepay,
			'xhQqpay'  => $xhQqpay,
			'xhWebpay' => $xhWebpay
		];
		
	}//countDay


	/**
	 * 定时统计(月流水)
	 * @return array 统计信息
	 * @access public
	 */
	public static function countMonth($_P) {
		
		//支付时间
		$startTime = (int)$_P['startTime'];
		$endTime   = (int)$_P['endTime'];

		$DB = useDB();

		$xhWepay = (int)$DB->getValue('SELECT SUM(price) FROM pay_record WHERE pay_type=5 AND pay_time>='.$startTime.' AND pay_time<'.$endTime);
		$xhQqpay  = (int)$DB->getValue('SELECT SUM(price) FROM pay_record WHERE pay_type=6 AND pay_time>='.$startTime.' AND pay_time<'.$endTime);

		$xhWebpay  = (int)$DB->getValue('SELECT SUM(price) FROM pay_record WHERE pay_type=7 AND pay_time>='.$startTime.' AND pay_time<'.$endTime);

		return [
			'xhWepay' => $xhWepay,
			'xhQqpay'  => $xhQqpay,
			'xhWebpay' => $xhWebpay
		];
		
	}//countMonth

}