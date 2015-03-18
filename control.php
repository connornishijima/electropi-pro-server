<?php
	include("includes/configFile.php");
	include("includes/functions.php");
        include("includes/strings.php");

	$title="Home Automation";
	$titleType="hide";
	$noWatchCheck = "1";
	$controlPage = "true";
	$maxWidth = $sets["SETTINGS"]["maxWidth"];

	function deleteDir($dirPath) {
	    if (! is_dir($dirPath)) {
	        throw new InvalidArgumentException("$dirPath must be a directory");
	    }
	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
	        $dirPath .= '/';
	    }
	    $files = glob($dirPath . '*', GLOB_MARK);
	    foreach ($files as $file) {
	        if (is_dir($file)) {
	            self::deleteDir($file);
	        } else {
	            unlink($file);
	        }
	    }
	    rmdir($dirPath);
	}

	if(isset($_GET["remove"])){
		$fixSwitches = "FIX";
		$UID = $_GET["remove"];
		deleteDir("data/switches/".$UID);
	}

	if(isset($_GET["view"])){
                $currentView = "#".$_GET["view"];
        }
        else{
                $currentView = "#none";
        }

	function includeSources(){
		$count = 0;
		$switchesFound = 0;
		$switches = scandir("data/switches");
		$switchCount = count($switches)-2;
		$switchCount = 99;
		while($count < $switchCount){
			foreach($switches as &$switchUIDn){
				if(strlen($switchUIDn) == 5){
					$switchInfo = file_get_contents("data/switches/".$switchUIDn."/info.ini");
					$lines = explode("\n",$switchInfo);
					foreach($lines as &$line){
						if(strlen($line) > 1){
							$line = explode(" = ",$line);
							$key = $line[0];
							$val = $line[1];
							if($key == "position"){
								$switchPos = intval($val);
							}
						}
					}
					if($switchPos == $count){
						$parseMessageList .= "parseMessage".$switchUIDn."(msg.data);\n";
						include("data/switches/".$switchUIDn."/source.php");
						$switchesFound = 1;
					}
				}
			}
		$count++;
		}
		if($switchesFound == 0){
			$GLOBALS['warningString'] = "You have no switches! <a href='learn.php'>Let's take control</a>.";
		}
	}

	function includeActions($max){
		$actions = scandir("data/actions");
		$actionString = "";
		foreach($actions as &$action){
			if($action != "." && $action != ".."){
				$data = file_get_contents("data/actions/".$action);
				$data = explode("\n",$data);
				$actionID = explode(".",$action)[0];
				$actionNick = ltrim($data[0],"*");
				$actionType = ltrim($data[1],"$");
				if($actionType == "SHOW"){
					$message = '"'.$actionID.'"'.',"ACTION:'.$actionID.'"';
					$actionString .=
						"<table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-left:auto;margin-right:auto;margin: 0px auto;margin-top: 5px;max-width:".$max."px;'>
	                                                <tr class='actionControl' id='".$actionID."' onclick='sendAction(".$message.");' style='-webkit-animation:none;'>
	                                                        <td>
	                                                                <div class='action'><img src='images/action.png' style='width:20px;height:20px;margin-right: 10px;opacity: 0.5;'>".$actionNick."</div>
	                                                        </td>
	                                                </tr>
	                                        </table>";
				}
			}
		}
		echo $actionString;
	}

?>
<html>
	<!-- Include Header -->
	<?php include("header.php"); ?>

	<style>
		#addSwitch{
			background-color:#282828;
			text-align: center;
		}
		#addAction{
			background-color:#282828;
			text-align: center;
		}
		#addTrack{
			background-color:#282828;
			text-align: center;
		}
		#addEvent{
			background-color:#282828;
			text-align: center;
		}
		#addPlugin{
			background-color:#282828;
			text-align: center;
		}
		#lock{
			background-color: <?php echo $sets['SETTINGS']["offColor"];?>;
		}

		@-webkit-keyframes flashAction{
			0%	{background-color:#aaaaaa;color:#242424;}
			100%	{background-color:#242424;color:<?php echo $sets["SETTINGS"]["offColor"];?>}
		}
		@-webkit-keyframes flashOn{
			0%	{background-color:<?php echo $sets["SETTINGS"]["onColor"];?>;color:#181818;;}
			100%	{background-color:#181818;color:#aaaaaa;}
		}
	</style>

	<body id="bodyMain">
	<link rel="prefetch" href="config.php">
	<!------------------------------------------------------->
		<div id="wrapper" style="margin-top: -10px;"> <!-- We remove the header margin to make the control list fit flush. -->
			<div id="slideButtonsRow">
				<table <?php echo $tabStretch;?>>
					<tr>
						<td class="slideButtons" id="slideRadioB" onclick="switchSlide('#slideRadio');">RADIO</td>
						<td class="hSpace"></td>
						<td class="slideButtons" id="slideWemoB" onclick="switchSlide('#slideWemo');">WEMO</td>
						<td class="hSpace"></td>
						<td class="slideButtons" id="slideHueB" onclick="switchSlide('#slideHue');">HUE</td>
						<td class="hSpace"></td>
						<td class="slideButtons" id="slideActionsB" onclick="switchSlide('#slideActions');">ACTIONS</td>
						<td class="hSpace"></td>
						<td id="addButton" id="slidePlus"><a href="add.php">+</a></td>
					</tr>
				</table>
			</div>
			<div id="slideRadio" style="display:none;">
				<?php includeSources();?>
			</div>
			<div id="slideActions" style="display:none;">
				<?php includeActions($maxWidth);?>
			</div>
			<div id="slideHue" style="display:none;">
				<div id="HUEB-HueLeft" style="width: 50%;height: 50px;float:left;">
					<input type=color id="HUE-HueLeft" oninput="hueColor(this.id.split('-')[1],this.value);" style="width: 100%;height: 50px;opacity:0;"></input>
				</div>
				<div id="HUEB-HueRight" style="width: 50%;height: 50px;float:left;">
					<input type=color id="HUE-HueRight" oninput="hueColor(this.id.split('-')[1],this.value);" style="width: 100%;height: 50px;opacity:0;"></input>
				</div>
				<br><br>
				<div id="HUEB-HueLeft_HueRight" style="width: 100%;height: 50px;float:left;">
					<input type=color id="HUE-HueLeft_HueRight" onchange="hueColor(this.id.split('-')[1],this.value);" style="width: 100%;height: 50px;opacity:0;"></input>
				</div>
			</div>
		</div>
	<!------------------------------------------------------->

	<!-- Include Footer -->
	<?php include("footer.php");?>
	</body>

	<script>
		window.currentView = <?php echo json_encode($currentView);?>;

                $(document).ready(function(){
			if(window.currentView == "#none"){
				switchSlide("#slideRadio");
			}
			else{
				switchSlide(window.currentView);
			}
                        $(window.currentView).fadeIn("fast");
                });

		function sScroll(div){
                        $('html, body').animate({
                                scrollTop: $(div).offset().top-5
                        }, 500);
                }

		function switchSlide(newView){
			if(newView != window.currentView){
				if(window.currentView != "#none"){
	                	        window.history.replaceState('page2', 'Title', '/control.php?view='+newView.slice(1));
				}
                        	$(window.currentView+"B").animate({backgroundColor: "#242424" }, 100);
                        	$(window.currentView+"B").animate({color:"#aaaaaa"}, 100);
                        	$(newView+"B").animate({color:"#080808"}, 100);
                        	$(newView+"B").animate({backgroundColor: "#999999" }, 100);
                        	$(window.currentView).fadeOut("fast",function(){
	                        	$(newView).fadeIn("fast",function(){
	                        	        sScroll(newView);
					});
				});
                        	window.currentView = newView;
			}
                }
	</script>

</html>
