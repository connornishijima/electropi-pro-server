<?php
	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return strtoupper($randomString);
	}

	$invested = floatval(file_get_contents("invested.txt"));
	$sold = floatval(file_get_contents("sold.txt"));

	if(isset($_POST["newOrder"])){
		if($_POST["newOrder"] == "PRO"){
			$item = "EPi Pro";
			$name = $_POST["name"];
			$address1 = $_POST["address1"];
			$address2 = $_POST["address2"];
			$city = $_POST["city"];
			$state = $_POST["state"];
			$zip = $_POST["zip"];
			$country = $_POST["country"];
			$shipType = $_POST["shipType"];
			if($shipType == "domestic"){
				$price = 35.00;
				$sold = $sold + floatval($price);
				file_put_contents("sold.txt",$sold);
			}
			else{
				$price = 55.00;
				$sold = $sold + floatval($price);
				file_put_contents("sold.txt",$sold);
			}
			$OID = generateRandomString();
			$order = $OID." * ".$item." * ".$name." * ".$address1." * ".$address2." * ".$city." * ".$state." * ".$zip." * ".$country." * ".$shipType." * NO * NONE\n";
			file_put_contents("orders.txt",$order,FILE_APPEND);
			$banner = "Order ".$OID." for ".$name." added!";
		}
	}

	if(isset($_POST["soldItem"])){
		$sold = $sold + floatval($_POST["soldItem"]);
		file_put_contents("sold.txt",$sold);
	}

	if(isset($_POST["spentMoney"])){
		$invested = $invested + floatval($_POST["spentMoney"]);
		file_put_contents("invested.txt",$invested);
		$banner = "Expense of $".floatval($_POST["spentMoney"])." added!";
	}

	if(isset($_POST["shipped"])){
		$OID = $_POST["shipped"];
		$orderList = file_get_contents("orders.txt");
		$orderList = explode("\n",$orderList);
		$orderOut = "";
		foreach($orderList as $order){
			if(strlen($order) > 1){
				$order = explode(" * ",$order);
				$OIDs = $order[0];
				if($OIDs == $OID){
					$item = $order[1];
					$name = $order[2];
					$address1 = $order[3];
					$address2 = $order[4];
					$city = $order[5];
					$state = $order[6];
					$zip = $order[7];
					$country = $order[8];
					$shipType = $order[9];
					$hasShipped = $order[10];
					$tracking = $_POST["trackingNumber"];

					$order = $OIDs." * ".$item." * ".$name." * ".$address1." * ".$address2." * ".$city." * ".$state." * ".$zip." * ".$country." * ".$shipType." * YES * ".$tracking."\n";
					$banner = "Order ".$OID." for ".$name." shipped!";
				}
				else{
					$item = $order[1];
					$name = $order[2];
					$address1 = $order[3];
					$address2 = $order[4];
					$city = $order[5];
					$state = $order[6];
					$zip = $order[7];
					$country = $order[8];
					$shipType = $order[9];
					$hasShipped = $order[10];
					$tracking = $order[11];

					$order = $OIDs." * ".$item." * ".$name." * ".$address1." * ".$address2." * ".$city." * ".$state." * ".$zip." * ".$country." * ".$shipType." * ".$hasShipped." * ".$tracking."\n";
				}
				$orderOut .= $order;
			}
		}
		file_put_contents("orders.txt",$orderOut);
	}

	$invested = floatval(file_get_contents("invested.txt"));
	$sold = floatval(file_get_contents("sold.txt"));
	$netted = floatval($sold-$invested);
	$left = 0;
	$batch = 58.00;
	$profitPer = 20.00;
	$weMake = 0;
	$netColor = "#00ffbe";
	$leftColor = "#00ffbe";
	$soldColor = "#00ffbe";
	if($netted <= 0){
		$left = (floatval($invested) - $sold) / $profitPer; // Profit of board
		$weWillMake = ($batch-$left)*$profitPer;
		$netColor = "#ff5c93";
		$leftColor = "#ff5c93";
	}
	if($sold <= 0){
		$soldColor = "#ff5c93";
	}

	if(strlen($banner) > 0){
		$bannerText = "<div id='bannerText'>".$banner."</div><br>";
	}

	$orderString = "";
	$orders = file_get_contents("orders.txt");
	$orders = explode("\n",$orders);
	$orderCount = 0;
	foreach($orders as $order){
		if(strlen($order) > 1){
			$order = explode(" * ",$order);
			$OID = $order[0];
			$item = $order[1];
			$name = $order[2];
			$address1 = $order[3];
			$address2 = $order[4];
			$city = $order[5];
			$state = $order[6];
			$zip = $order[7];
			$country = $order[8];
			$shipType = $order[9];
			$hasShipped = $order[10];
			$tracking = $order[11];

			if($hasShipped == "NO"){
				$orderLine = "<tr><td>" . $OID . "</td><td>" . $item . "</td><td>" . $name . "</td><td>" . $address1 . "</td><td>" . $address2 . "</td><td>" . $city . "</td><td>" . $state . "</td><td>" . $zip . "</td><td>" . $country . "</td><td>" . $shipType . "</td><td><form action='overview.php' method='POST'><input type='hidden' name='shipped' value='".$OID."'></input><input type='text' name='trackingNumber' placeholder='Tracking Number'></input> <input type='submit' value='MARK AS SHIPPED'></input></td></tr>";
				$orderCount += 1;
			}
			if($hasShipped == "YES"){
				$orderLine = "<tr><td>" . $OID . "</td><td>" . $item . "</td><td>" . $name . "</td><td>" . $address1 . "</td><td>" . $address2 . "</td><td>" . $city . "</td><td>" . $state . "</td><td>" . $zip . "</td><td>" . $country . "</td><td>" . $shipType . "</td><td>SHIPPED: <a href='https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=".$tracking."'>".$tracking."</a></td></tr>";
			}

			$orderString .= $orderLine;
		}
	}
?>
<html>
<!-- HTML ------------------------------------------------------>
	<head>
		<title>ElectroPi - BATCH B001</title>

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
			a{
				color:#00ffbe;
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
			.priceTable{
				font-size: 24px;
			}
			#invested,#sold,#netted,#make{
				color:#00ffbe;
			}
		</style>
	</head>
	<body>
		<?php echo $bannerText;?>
		<div id="title">ElectroPi - BATCH #B001</div><br>

		<div id="homeMenu">
			<div class="sectionLink" onclick="switchView('newOrder','New Order');">NEW ORDER</div>
			<div class="sectionLink" onclick="switchView('currentOrders','Current Orders');" style="color:#ff5c93;">CURRENT ORDERS</div>
			<div class="sectionLink" onclick="switchView('newExpense','New Expense');" style="color:#aaaaaa;" onclick="switchView('newOrder','New Cost');">NEW BUSINESS EXPENSE</div><br>
			<br>
			<table width="100%" class="priceTable">
				<tr>
					<td>
						<div class="money">INVESTED: <div id="invested">$<?php echo $invested;?></div></div>
					</td>
					<td>
						<div class="money">SOLD: <div id="sold" style="color:<?php echo $soldColor;?>;">$<?php echo $sold;?></div></div>
					</td>
					<td>
						<div class="money">NETTED: <div id="netted" style="color:<?php echo $netColor;?>;">$<?php echo $netted;?></div></div>
					</td>
					<td>
						<div class="money">BOARDS LEFT UNTIL NET POSITIVE: <div id="left" style="color:<?php echo $leftColor;?>;"><?php echo intval($left);?></div></div>
					</td>
					<td>
						<div class="money">BATCH WORTH: <div id="make"><?php echo $weWillMake;?></div></div>
					</td>
					<td>
						<div class="money">ORDERS IN QUEUE: <div id="queue"><?php echo $orderCount;?></div></div>
					</td>
				</tr>
			</table>
		</div>

		<div id="newOrder" style="display:none;">
			<div class="back" onclick="switchView('homeMenu','ElectroPi Orders');">
				BACK TO MAIN
			</div><br>
			What type of order came in?<br><br>
			<form action="overview.php" method="POST">
				<select name="newOrder">
					<option value="PRO">ElectroPi Pro ($35)</option>
				</select><br><br>
				<input type="text" name="name" placeholder="Full Name"></input><br><br>
				<input type="text" name="address1" placeholder="Address Line 1"></input><br><br>
				<input type="text" name="address2" placeholder="Address Line 2"></input><br><br>
				<input type="text" name="city" placeholder="City"></input><br><br>
				<input type="text" name="state" placeholder="State"></input><br><br>
				<input type="text" name="zip" placeholder="ZIP"></input><br><br>
				<input type="text" name="country" placeholder="Country"></input><br><br>
				<select name="shipType">
					<option value="domestic">Domestic</option>
					<option value="international">International</option>
				</select><br><br>
				<input type="submit"></input>
			</form>
		</div>
		<div id="currentOrders" style="display:none;">
			<div class="back" onclick="switchView('homeMenu','ElectroPi Orders');">
				BACK TO MAIN
			</div><br>
			<table width="100%" border="1">
				<tr>
						<td>Order ID</td>
						<td>Item</td>
						<td>Full Name</td>
						<td>Address Line 1</td>
						<td>Address Line 2</td>
						<td>City</td>
						<td>State</td>
						<td>ZIP</td>
						<td>Country</td>
						<td>ShipType</td>
						<td>Status</td>
				</tr>
				<tr>
				</tr>
				<?php echo $orderString;?>
			</table>
		</div>
		<div id="newExpense" style="display:none;">
			<div class="back" onclick="switchView('homeMenu','ElectroPi Orders');">
				BACK TO MAIN
			</div><br>
			How much was spent?<br><br>
			<form action="overview.php" method="POST">
				<input type="text" name="spentMoney"></input>
				<input type="submit"></input>
			</form>
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
