<?php
/**
 * 公共俱乐部房间(桌子)操作类
 *
 * @author HJH
 * @version  2017-7-18
 */


class  PublicRoom{
	
	/**
	 * 构造函数 
	 */
	function __construct( ) {	 
		
	}


	/**
	 * 创建买入数据
	 * @param int $gameType 游戏类型 1=德州 2=牛加水
	 * @param int $bet 买入筹码数量
	 * @param int $roomId 房间id
	 * @param int $uid 会员uid
	 * @return void
	 * @access public
	 */
	public static function createAddBet($gameType, $bet, $roomId, $uid=UID) {
		
		$ary = array(
			'roomId'		=> $roomId,
			'uid'			=> $uid,
			'gameType'		=> $gameType,
			'curBet'		=> $bet,
			'preAddBet'		=> 0,
			'totalBuyBet'	=> $bet
		);

		$cache = getCache();
		return $cache->setArray('clubRoomUser:'.$roomId.':'.$uid, $ary, false);
	}


	/**
	 * 读取买入的筹码数据
	 * @param string $field 字段
	 * @param int $roomId 房间id
	 * @param int $uid 会员uid
	 * @return array 玩家信息
	 * @access public
	 */
	public static function getBetData($roomId, $uid=UID) {
		$cache = getCache();
		return $cache->getArray('clubRoomUser:'.$roomId.':'.$uid);
	}


	/**
	 * 预约买入筹码
	 * @param int $preAddBet 预买入筹码数量
	 * @param int $totalBuyBet 总买入筹码数量
	 * @param int $roomId 房间id
	 * @return bool
	 * @access public
	 */
	public static function preAddBet($preAddBet, $totalBuyBet, $roomId, $uid=UID) {
		
		$ary = array(
			'preAddBet'		=> $preAddBet,
			'totalBuyBet'	=> $totalBuyBet
		);

		$cache = getCache();
		return $cache->setArray('clubRoomUser:'.$roomId.':'.$uid, $ary, false);
	}


	/**
	 * 合并预约买入筹码
	 * @param int $id 记录id
	 * @return void
	 * @access public
	 */
	public static function plusPreBet($curBet, $roomId, $uid=UID) {

		$ary = array(
			'curBet'	=> $curBet,
			'preAddBet'	=> 0
		);

		$cache = getCache();
		return $cache->setArray('clubRoomUser:'.$roomId.':'.$uid, $ary, false);
	}


	/**
	 * 增减当前筹码
	 * @param int $roomId 房间id
	 * @param int $uid 玩家id
	 * @param int $num 要增加的数量
	 * @param bool $add true增加,false减少
	 * @param bool $flag true可以减到0,false不足提示失败
	 * @return bool $result 成功则返回true,否则返回false
	 * @access public
	 */
	public static function bet( $roomId, $uid, $num, $add=true, $flag=false ) {

		$betData = PublicRoom::getBetData($roomId, $uid);
		
		if($add){
			$curBet = (int)$betData['curBet'] + $num;

		}else{
			$oldNum = (int)$betData['curBet'];
			if($oldNum<$num){
				if($flag) {
					$num = $oldNum;
				}else{
					return false;
				}
			}
			$curBet = $oldNum - $num;
		}
		
		$cache = getCache();
		$rs = $cache->setArray('clubRoomUser:'.$roomId.':'.$uid, array('curBet'=>$curBet), false);
		if(!$rs) {
			return false;
		}

		return $curBet;
	}




}