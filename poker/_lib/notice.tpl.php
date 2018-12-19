<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo $title?></title>
	<style type="text/css">
		body{
			background-color: #fff;
		}
		.line_01{
			margin: 50px 0;
			line-height: 35px;
			text-align: center;
			border-bottom: 1px #eee solid;
		}
		.mg{
			margin: 30px;
		}
		.ag{
			text-align: center;
		}
		.title{
			font-size: 30px;
			font-weight: bold;
			color: #404040;
			text-align: center;
		}
	</style>
</head>
<body>

<div>
	<div class="mg ag title"><?php echo $title?></div>
	<div class="line_01"><?php echo $time?></div>
	<div class="mg"><?php echo $content?></div>
</div>

</body>
</html>