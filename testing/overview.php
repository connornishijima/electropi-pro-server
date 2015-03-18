<?php
	$invested = floatval(file_get_contents("invested.txt"));
	$sold = floatval(file_get_contents("sold.txt"));

	if(isset($_GET["spentMoney"])){
		$invested = $invested + floatval($_GET["spentMoney"]);
		file_put_contents("invested.txt",$invested);
	}

	if(isset($_GET["soldItem"])){
		$sold = $sold + floatval($_GET["soldItem"]);
		file_put_contents("sold.txt",$sold);
	}

	$invested = floatval(file_get_contents("invested.txt"));
	$sold = floatval(file_get_contents("sold.txt"));
	$netted = floatval($sold-$invested);
	$left = 0;
	$batch = 58.00;
	$profitPer = 20.00;
	$weMake = 0;
	if($netted <= 0){
		$left = floatval($invested) / $profitPer; // Profit of board
		$weWillMake = ($batch-$left)*$profitPer;
	}
?>
<html>
<!-- HTML ------------------------------------------------------>
	<head>
		<title>ElectroPi Orders</title>

		<link href='http://fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'>
	        <link href='http://fonts.googleapis.com/css?family=Dosis' rel='stylesheet' type='text/css'>


		<script src="js/jquery.js"></script>
        	<script src="js/jquery-ui.js"></script>

		<style>
			body{
				font-family: 'Dosis', sans-serif;
				color:#aaaaaa;
				background-color:#181818;
				margin:20px;
			}
			#title{
				font-family: 'Oswald', sans-serif;
				font-size:36px;
			}
			.sectionLink{
				font-size:24px;
				color:#00ffbe;
				cursor:pointer;
			}
		</style>
	</head>
	<body>
		<div id="title">ElectroPi Orders</div><br>

		<div id="homeMenu">
			<div class="sectionLink" onclick="switchView('newOrder','New Order');">NEW ORDER</div>
			<div class="sectionLink" onclick="switchView('currentOrders','Current Orders');" style="color:#ff5c93;">CURRENT ORDERS</div>
			<div class="sectionLink" onclick="switchView('newExpense','New Expense');" style="color:#aaaaaa;" onclick="switchView('newOrder','New Cost');">NEW BUSINESS EXPENSE</div><br>
			<br>
			<table width="720">
				<tr>
					<td>
						<div class="money">INVESTED: <div id="invested">$<?php echo $invested;?></div></div>
					</td>
					<td>
						<div class="money">SOLD: <div id="sold">$<?php echo $sold;?></div></div>
					</td>
					<td>
						<div class="money">NETTED: <div id="netted">$<?php echo $netted;?></div></div>
					</td>
					<td>
						<div class="money">BOARDS LEFT UNTIL NET POSITIVE: <div id="left"><?php echo $left;?></div></div>
					</td>
					<td>
						<div class="money">CASH MADE IN BATCH: <div id="make"><?php echo $weWillMake;?></div></div>
					</td>
				</tr>
			</table>
		</div>

		<div id="newOrder" style="display:none;">
			<div class="back" onclick="switchView('homeMenu','ElectroPi Orders');">
				BACK TO MAIN
			</div><br>
			NEW ORDER PAGE SHOWN HERE
		</div>
		<div id="currentOrders" style="display:none;">
			<div class="back" onclick="switchView('homeMenu','ElectroPi Orders');">
				BACK TO MAIN
			</div><br>
			CURRENT ORDERS PAGE SHOWN HERE
		</div>
		<div id="newExpense" style="display:none;">
			<div class="back" onclick="switchView('homeMenu','ElectroPi Orders');">
				BACK TO MAIN
			</div><br>
			NEW EXPENSES PAGE SHOWN HERE
		</div>

	</body>

<!-- JS -------------------------------------------------------->
	<script>
		window.currentView = "homeMenu";

		function switchView(newView,title){
			$("#title").html(title);
			$("#"+window.currentView).fadeOut("fast",function(){
                                $("#"+newView).fadeIn("fast",function(){
                                });
                        });
			window.currentView = newView;
		}

	</script>
</html>
