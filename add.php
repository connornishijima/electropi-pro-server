<?php
	include("includes/configFile.php");
	include("includes/functions.php");
        include("includes/strings.php");

	$title="Learn";

	$noWatchCheck = "1";
	file_put_contents("python/decode.ON","");
	file_put_contents("python/decode.OFF","");

	function get_random_string($valid_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ", $length = 5)
	{
	    // start with an empty random string
	    $random_string = "";

	    // count the number of chars in the valid chars string so we know how many choices we have
	    $num_valid_chars = strlen($valid_chars);

	    // repeat the steps until we've created a string of the right length
	    for ($i = 0; $i < $length; $i++)
	    {
	        // pick a random number from 1 up to the number of valid chars
	        $random_pick = mt_rand(1, $num_valid_chars);

	        // take the random character out of the string of valid chars
	        // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
	        $random_char = $valid_chars[$random_pick-1];

	        // add the randomly-chosen char onto the end of our string so far
	        $random_string .= $random_char;
	    }

	    // return our finished random string
	    return $random_string;
	}

	if(isset($_POST["updated"])){
		$updated = "true";
		$nick = str_replace(" ","_",$_POST["nick"]);
		$freq = $_POST["freq"];
		$onCode = $_POST["onCode"];
		$offCode = $_POST["offCode"];

		$UID = get_random_string();

		mkdir("data/switches/".$UID, 0777, true);
		copy("presets/learned-default/source.php", "data/switches/".$UID."/source.php");

		file_put_contents("data/switches/".$UID."/info.ini","[HTML]\nposition = ".(count(scandir("data/switches"))-2)."\nstate = 0\n\n[ID]\nnickname = ".$nick."\n\n[CONTROL]\noncodedata = on.bin\noffcodedata = off.bin\nrepeat = 10\nfreq = ".$freq."\n");
		file_put_contents("data/switches/".$UID."/on.bin",$onCode);
		file_put_contents("data/switches/".$UID."/off.bin",$offCode);
		header("Location: index.php");
	}
?>
<html>
	<!-- Include Header -->
	<?php include("header.php"); ?>

	<body id="bodyMain">
	<!------------------------------------------------------->
		<div id="wrapper" style="margin-top: -10px;"> <!-- We remove the header margin to make the control list fit flush. -->
			<div id="setup">
			<table <?php echo $tabStretch;?>>
				<tr>
					<td>
						<div class="h1" style="text-align:left;margin-top:20px;">ADD COMPONENT</div>
						<div class="h2" style="text-align:left;margin-top:5px;margin-bottom:20px;font-family: 'Dosis',sans-serif;font-size: 18px;">Here you can add many<span class="highlightOn"> Components</span>, which will give you controls on your homescreen. Add an RF switch, a Belkin WeMo, a Phillips Hue, and more!</div>
					</td>
				</tr>
			</table>
			<table <?php echo $tabStretch;?>>
				<tr class="addRow">
					<td class="addIcon"><img src="images/rf.png" width="32px" height="32px" /></td>
					<td class="addCol">ADD RF SWITCH</td>
				</tr>
				<tr class="compSpace"></tr>
				<tr class="addRow">
					<td class="addIcon"><img src="images/wemo.png" width="32px" height="32px" /></td>
					<td class="addCol">ADD BELKIN WEMO</td>
				</tr>
				<tr class="compSpace"></tr>
				<tr class="addRow">
					<td class="addIcon"><img src="images/hue.png" width="32px" height="32px" /></td>
					<td class="addCol">ADD PHILIPS HUE</td>
				</tr>
				<tr class="compSpace"></tr>
				<tr class="addRow">
					<td class="addIcon"><img src="images/action.png" width="32px" height="32px" /></td>
					<td class="addCol"><a href="actions.php" style="color:#aaaaaa;">ADD ACTION</a></td>
				</tr>
				<tr class="compSpace"></tr>
				<tr class="addRow">
					<td class="addIcon"><img src="images/event.png" width="32px" height="32px" /></td>
					<td class="addCol"><a href="events.php" style="color:#aaaaaa;">ADD EVENT</a></td>
				</tr>
				<tr class="compSpace"></tr>
				<tr class="addRow">
					<td class="addIcon"><img src="images/tracking.png" width="32px" height="32px" /></td>
					<td class="addCol"><a href="devices.php" style="color:#aaaaaa;">ADD DEVICE</a></td>
				</tr>
			</table>
		</div>

	<!-- Include Footer -->
	<?php include("footer.php");?>
	</body>
</html>
