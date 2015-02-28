<?php
	include("includes/configFile.php");
	include("includes/functions.php");
        include("includes/strings.php");

	if(isset($_GET["view"])){
		$currentView = "#".$_GET["view"];
	}
	else{
		$currentView = "#trackingAdd";
	}

	$title="TRACKING";
	$logoColor = "off";
	$noWatchCheck = "1";
	$gear = "link";
        $gearLink = "<td align='right'><a href='index.php'><img src='images/home.png' id='homeIcon'></a></td>";

	$networks = file_get_contents("/etc/network/interfaces");
	$networks = explode("\n",$networks);
	foreach($networks as $netLine){
		if(substr($netLine, 0, 8) == "wpa-ssid"){
			$ssid = trim(str_replace('"','',substr($netLine, 8)));
		}
	}

	$deviceNickList = [];
	$deviceOSList = [];

	$checkinLogs = file_get_contents("requestLogs.csv");
	$checkinLogs = explode("\n",$checkinLogs);
	foreach($checkinLogs as $checkinLine){
		if(substr($checkinLine, 0, 4) != "TYPE"){
			if(strlen($checkinLine) > 1){
				$pieces = explode(",",$checkinLine);
				$deviceOS = $pieces[0];
				$deviceNick = $pieces[1];
				if($deviceNick != "epi"){
					if(!in_array($deviceNick,$deviceNickList)){
						array_push($deviceOSList,$deviceOS);
						array_push($deviceNickList,$deviceNick);
					}
				}
			}
		}
	}

	$deviceTable = "";
	$deviceCount = count($deviceNickList);
	$i = 0;
	while($i < $deviceCount){
		if(trim($deviceOSList[$i]) == "ANDROID"){
			$icon = "images/android.png";
		}
		else if(trim($deviceOSList[$i]) == "IOS"){
			$icon = "images/apple.png";
		}
		$deviceLine = "<tr style='background-color:#080808;font-size:24px;cursor:pointer;'>
					<td style='height:64px;padding-left:10px;width: 50px;'>
						<img src='".$icon."' width='40px'/>
					</td>
					<td style='color:".$sets['SETTINGS']['offColor'].";'>
						".$deviceNickList[$i]."
					</td>
				</tr><tr height='5px'></tr>";
		$deviceTable .= $deviceLine;
		$i++;
	}
?>
<html>
	<!-- Include Header -->
	<?php include("header.php"); ?>

	<body id="bodyMain">
	<!------------------------------------------------------->
		<div id="wrapper"> <!-- We remove the header margin to make the control list fit flush. -->
			<div id="actionsConfirmWrap">
				<table <?php echo $tabStretch;?>>
					<tr>
						<td id="actionAddConfirm">Action added. <a href="index.php" style="color:#ffffff;">Return to control?</a></td>
					</tr>
				</table>
			</div>
				<div id="trackingAdd" style="display:none;">

					<table <?php echo $tabStretch;?>>
						<tr>
							<td id="confSubtitle"><img src="images/tracking.png" style="width: 26px;height: 26px;margin-right: 5px;">DEVICE TRACKING</td>
						</tr>
					</table>

					<table <?php echo $tabStretch;?>>
                        		        <tr>
                        		                <td>
                        		                        <div class="h2" style="text-align:left;margin-top:0px;margin-bottom:20px;font-family: 'Dosis',sans-serif;font-size: 18px;"><span class="highlightOff" style="font-size: 24px;">"Tracking" can see who's home, and light the house for them.</span><br><br></div>
                        		                </td>
                        		        </tr>
                        		</table>

					<table <?php echo $tabStretch;?>>
						<tr style="background-color:#242424;">
							<td style="padding:5px;font-size: 24px;">
								PICK A KNOWN DEVICE:
							</td>
						</tr>
						<tr style="background-color:#242424;font-family: 'Dosis';">
							<td style="padding:5px;">
								<table <?php echo $tabStretch;?>>
									<?php echo $deviceTable;?>
								</table>
							</td>
						</tr>
					</table>
				</div>
		</div>
	<!------------------------------------------------------->

	<script>

		window.currentView = <?php echo json_encode($currentView);?>;
		window.switches = [<?php echo $jsSwitchArray;?>];

		<?php echo $jsVariables;?>

		$(document).ready(function(){
			$(window.currentView).fadeIn("fast");
		});

		$(document).on("keypress", function (e) {
			if (e.which == 13 && window.currentView != "#actionMenu" && window.currentView == "#actionAdd" ) {
				sendDataActionAdd();
			}
		});

		function sScroll(div){
                        $('html, body').animate({
                                scrollTop: $(div).offset().top-5
                        }, 500);
                }

		function switchView(newView){
			window.history.replaceState('page2', 'Title', '/actions.php?view='+newView.slice(1));
			$("#confConfirmWrap").fadeOut("fast");
			$("#confConfirm").fadeOut("fast");
			$(window.currentView).fadeOut("fast",function(){
				$(newView).fadeIn("fast",function(){
					if(newView == "#confMenu"){
						sScroll("#whole");
					}
					else{
						sScroll(newView);
					}
				});
			});
			window.currentView = newView;
		}

		function switchState(id,idc){
			window[id+"state"] = document.getElementById(idc).innerHTML;
			if(window[id+"state"] == "DO NOTHING"){
				window[id+"state"] = "TURN ON";
				document.getElementById(idc).innerHTML = window[id+"state"];
				document.getElementById(idc).style.color = <?php echo json_encode($sets['SETTINGS']['onColor']);?>;
			}
			else if(window[id+"state"] == "TURN ON"){
				window[id+"state"] = "TURN OFF";
				document.getElementById(idc).innerHTML = window[id+"state"];
				document.getElementById(idc).style.color = <?php echo json_encode($sets['SETTINGS']['offColor']);?>;
			}
			else if(window[id+"state"] == "TURN OFF"){
				window[id+"state"] = "DO NOTHING";
				document.getElementById(idc).innerHTML = window[id+"state"];
				document.getElementById(idc).style.color = "#666666";
			}

			sumSwitches();
		}

		function sumSwitches(){
			outString = "";
			window.switches.forEach(function(entry){
				if(entry != "end"){
					if(window[entry+"state"] == "TURN ON"){
						outString += entry+" | 1 | COM-RF:"+window[entry+"freq"]+" python/tx "+window[entry+"onCode"]+" "+window[entry+"repeat"]+"\n";
					}
					if(window[entry+"state"] == "TURN OFF"){
						outString += entry+" | 0 | COM-RF:"+window[entry+"freq"]+" python/tx "+window[entry+"offCode"]+" "+window[entry+"repeat"]+"\n";
					}
				}
			});
			return outString;
		}

		function sendDataActionAdd(){
			actionData = "*"+document.getElementsByName("actionName")[0].value+"\n"+document.getElementsByName("actionType")[0].value+"\n"+sumSwitches();
			actionDataURL = encodeURIComponent(actionData);
			$.ajax({
				url: "actions.php",
				type:'POST',
				data:
				{
					actionFile : actionData,
					updated : "true"
				},
				success: function(msg)
				{
					sScroll("#whole");
					$("#actionConfirmWrap").fadeIn("fast");
					$("#actionAddConfirm").fadeIn("fast");
				}
			});
		}

	</script>

	<!-- Include Footer -->
	<?php include("footer.php");?>
	</body>
</html>
