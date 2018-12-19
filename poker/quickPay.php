<?php
/* *
 * 功能：一般支付调试入口页面
 * 版本：1.0
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码。
 */
 	require_once("lib/pay.Config.php");
	$time		= time();
	$orderNo	= date("YmdHis",$time);
	$tradeDate	= date("Ymd",$time);
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>网银支付</title>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<script type="text/javascript" src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
	<script type="text/javascript">
		$(function(){
			$(".bank-list img").on("click", function() {
				$(".bank-list input[type='radio']").prop("checked", false);
				$(this).prev("input[type='radio']").prop("checked", true);
			})
		})
	</script>
</head>
<body>
   <div class="container">
	   <div class="header">
		   <h3>快捷支付表单填写：</h3>
	   </div>

	<div class="main">
		 <form target="_blank" method="post" action="doQuickPayApply.php">
			<ul>
				<li>
					<label>订单号</label>
					<input type="text" name="tradeNo" value='<?php echo $orderNo; ?>' />

				</li>
				<li>
					<label>交易日期</label>
					<input type="text" name="tradeDate" value='<?php echo $tradeDate; ?>'/>

				</li>
				<li>
					<label>订单金额</label>
					<input type="text" name="amount" value="0.01" />
				</li>
				<li>
					<label>商户参数</label>
					<input type="text" name="extra" value="canshu" />
				</li>
				<li>
					<label>交易摘要</label>
					<input type="text" name="summary" value="支付测试" />
				</li>
				<li>
					<label>银行卡类型</label>
					<select name="cardType">
						<option value="1" selected>借记卡</option>
					</select>
				</li>
				<li>
					<label>银行卡卡号</label>
					<input type="text" name="cardNo" value="" />
				</li>
				<li>
					<label>开户姓名</label>
					<input type="text" name="cardName" value="" />
				</li>
				<li>
					<label>身份证号</label>
					<input type="text" name="idCardNo" value=""  />
				</li>
				<li>
					<label>银行预留手机号</label>
					<input type="text" name="mobile" value=""  />
				</li>
				<li style="margin-top: 50px">
					<label></label>
					<button type="submit">获取验证码</button>
				</li>
             </ul>
		</form>
	  </div>
    </div>
  </body>
</html>
