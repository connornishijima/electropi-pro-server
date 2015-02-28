<?php
<<<<<<< HEAD
	include("password.php");
	$ip = $_SERVER['REMOTE_ADDR'];
	checkIP($ip);

	if(!isset($logoColor)){
		$logoColor = $sets['SETTINGS']["onColor"];
	}
	else{
		if($logoColor == "off"){
			$logoColor = $sets['SETTINGS']["offColor"];
		}
	}

	if($titleType == "hide"){
		$titleLine = "<title>ElectroPi</title>";
	}
	else{
		$titleLine = "<title>ElectroPi | ".$title."</title>";
	}

	if(!isset($gear)){
		$gearLink = "<td align='right'><a href='config.php'><img src='images/settings.png' style='width:32px;height:32px;opacity: 0.3;'/></a></td>";
	}

	if($passMD5 == "4dfcb7e47d53ff431f231f8bfc51c32d"){
		$warningString = "Access password is default 'electropi'. <a href='config.php?view=confPassword'>Change it now?</a>";
	}

	echo $titleLine;

?>
<head>
	&nbsp;
	<link rel="manifest" href="manifest.json">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="app-mobile-web-app-capable" content="yes">

	<link rel="icon" sizes="192x192" href="images/icon-192x192.png">
	<link rel="icon" sizes="144x144" href="images/icon-144x144.png">
	<link rel="icon" sizes="120x120" href="images/icon-120x120.png">
	<link rel="icon" sizes="96x96" href="images/icon-96x96.png">
	<link rel="icon" sizes="48x48" href="images/icon-48x48.png">
	<link rel="icon" sizes="36x36" href="images/icon-36x36.png">

	<link rel="apple-touch-icon" sizes="192x192" href="images/icon-192x192.png">
	<link rel="apple-touch-icon" sizes="144x144" href="images/icon-144x144.png">
	<link rel="apple-touch-icon" sizes="120x120" href="images/icon-120x120.png">
	<link rel="apple-touch-icon" sizes="96x96" href="images/icon-96x96.png">
	<link rel="apple-touch-icon" sizes="48x48" href="images/icon-48x48.png">
	<link rel="apple-touch-icon" sizes="36x36" href="images/icon-36x36.png">

	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/tooltipster.css" />

	<link href='http://fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Dosis' rel='stylesheet' type='text/css'>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

	<script src="js/jquery.js"></script>
	<script src="js/jquery-ui.js"></script>

	<style type="text/css">
		a{color:<?php echo $sets['SETTINGS']["onColor"];?>;text-decoration:none;}
		.h1{color:<?php echo $sets['SETTINGS']["onColor"];?>;}
		.freqLeft,.freqRight{color:<?php echo $sets['SETTINGS']["offColor"];?>;}
		#confBack{
			color:<?php echo $sets['SETTINGS']["offColor"];?>;
		}
		.linkRow{
		        background-color: #080808;
		        padding: 9px;
		        font-size: 20px;
		        color: #ff5c93;
		}
		.highlightOn{
			color:<?php echo $sets['SETTINGS']["onColor"];?>;
		}
		.highlightOff{
			color:<?php echo $sets['SETTINGS']["offColor"];?>;
		}

	</style>

	<div id="warning">
		<div id="warningHead" style="color:<?php echo $sets['SETTINGS']['offColor'];?>">
			WHOOPS! ONE SEC.
		</div>
		<div id="warningTail">
			We've lost connection to the watchdog backend.<br>Reboot your Pi if this warning doesn't subside within a few moments...
		</div>
		<div id="warningRefresh" onClick="history.go(0);" style="cursor: pointer;font-size: 22px;margin-top: 10px;background-color:<?php echo $sets['SETTINGS']['offColor'];?>;color:#181818;cursor: pointer;width: 160px;padding: 5px;margin-left: auto;margin-right: auto;">
			MANUAL REFRESH
		</div>
	</div>
	<div id="whole">
	<div id="wholeInner">

	<div id="headWrap">
		<table <?php echo $tabStretch;?>>
			<tr>
				<td style="padding-top:8px;width:32px;"><a href="index.php"><img src="images/logostatic.png" id="logo" style="width:64px;height:64px;"/></a></td>
				<td id="epTitle"><a href="index.php" style="color:<?php echo $logoColor;?>;">ELECTRO<div style="display:inline-block;color:#CCC;">PI</div></a><br><div id="epSubtitle"><?php echo $title;?></div></td>
				<?php echo $gearLink;?>
			</tr>
		</table>
		<table <?php echo $tabStretch;?>>
			<tr>
				<td style="padding-bottom: 15px;"></td>
			</tr>
			<tr>
				<td style="background-image:url('images/gradient.png');background-repeat: no-repeat;height: 2px;padding-bottom:10px;"></td>
			</tr>
		</table>
	</div>
	<div id="bannerWrap" style="display:none;">
		<table <?php echo $tabStretch;?>>
                        <tr>
				<td>
					<div id="bannerInner">
						BANNER TEXT
					</div>
				</td>
			</tr>
		</table>
	</div>
</head>
=======
	$beta = readSetting("BETA_MODE");
	$maxWidth = readSetting("MAX_WIDTH");
	$notifications = readSetting("NOTIFICATIONS");
	$notification = trim(file_get_contents("misc/notification.txt"));
	$theTime = trim(file_get_contents("misc/time.txt"));
	$IP = $_SERVER['REMOTE_ADDR'];
	$haptic = readSetting("HAPTIC");

	if($title == "NOTITLE"){
		$title = "";
	}

	if($beta == "ENABLED"){
		$betaVisibility = "x";
	}
	else{
		$betaVisibility = "none";
	}

	if($hideHeader == True){
		$headerVisibility = "none";
	}
	else{
		$headerVisibility = "block";
	}

	if($hideSettings == True){
		$settingsVisibility = "none";
	}
	else{
		$settingsVisibility = "table-cell";
	}

	$specialColorEnabled = trim(file_get_contents("misc/special/colorEnabled"));
	if($specialColorEnabled == "ENABLED"){
		$onColor = trim(file_get_contents("misc/special/onColor"));
		$offColor = trim(file_get_contents("misc/special/offColor"));
	}

		$trusted = 0;

		$deviceList = file_get_contents("conf/device.list");
		$deviceList = explode("\n",$deviceList);
		foreach($deviceList as $device){
			if(strlen($device) > 3){
				$pieces = explode("|",$device);
				$nick = $pieces[0];
				$mac = $pieces[1];
				$ipS = $pieces[2];
				if($ipS == $IP && $trusted == 0){
					$trusted = 1;
				}
			}
		}

		if($trusted == 0){
			header("Location: addDevice.php");
		}

?>

<link rel="manifest" href="manifest.json">
<meta name="mobile-web-app-capable" content="yes">
<meta name="app-mobile-web-app-capable" content="yes">

<link rel="icon" sizes="192x192" href="images/icon-192x192.png">
<link rel="icon" sizes="144x144" href="images/icon-144x144.png">
<link rel="icon" sizes="120x120" href="images/icon-120x120.png">
<link rel="icon" sizes="96x96" href="images/icon-96x96.png">
<link rel="icon" sizes="48x48" href="images/icon-48x48.png">
<link rel="icon" sizes="36x36" href="images/icon-36x36.png">

<link rel="apple-touch-icon" sizes="192x192" href="images/icon-192x192.png">
<link rel="apple-touch-icon" sizes="144x144" href="images/icon-144x144.png">
<link rel="apple-touch-icon" sizes="120x120" href="images/icon-120x120.png">
<link rel="apple-touch-icon" sizes="96x96" href="images/icon-96x96.png">
<link rel="apple-touch-icon" sizes="48x48" href="images/icon-48x48.png">
<link rel="apple-touch-icon" sizes="36x36" href="images/icon-36x36.png">

<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700,300' rel='stylesheet' type='text/css'>

<script src="js/jquery.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/slip.js"></script>
<script type="text/javascript">

window.supportsVibrate = "vibrate" in window.navigator;
window.notification = "<?php echo $notification?>";
window.notificationTime = 0;
window.notificationPresent = 0;
window.watchdog = "<?php echo $theTime?>";
window.server = "<?php echo $theTime?>";
window.uState = "FALSE";
window.repop = 0;

$("#notify").hide();

function notify(inputText){
	window.notification = inputText;
	document.getElementById("notification").innerHTML = inputText;
	$("#notify").fadeIn(0);
	if(window.notificationTime < 5){
		window.notificationTime = window.notificationTime + 5;
	}
	window.notificationPresent = 1;
}

function notifyTimeout(){
	window.notificationTime = window.notificationTime - 1
	if(window.notificationTime <= 0 && window.notificationPresent == 1){
		$("#notify").fadeOut("fast");
		window.notificationPresent = 0;
	}
}

function ajaxFunctionNotification(){
        var ajaxRequestN;  // The variable that makes Ajax possible!

        try{
                // Opera 8.0+, Firefox, Safari
                ajaxRequestN = new XMLHttpRequest();
        } catch (e){
                // Internet Explorer Browsers
        try{
                ajaxRequestN = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
        try{
                ajaxRequestN = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (e){
                // Something went wrong
                alert('Your browser broke!');
                return false;
        }
        }
        }
        // Create a function that will receive data sent from the server
        ajaxRequestN.onreadystatechange = function(){
                if(ajaxRequestN.readyState == 4){
                        if(ajaxRequestN.responseText != window.notification){
				notify(ajaxRequestN.responseText);
                        }
                }
        };
        ajaxRequestN.open('POST', 'misc/notification.txt', true);
        ajaxRequestN.send(null);
}

function ajaxFunctionWatchdog(){
        var ajaxRequestW;  // The variable that makes Ajax possible!

        try{
                // Opera 8.0+, Firefox, Safari
                ajaxRequestW = new XMLHttpRequest();
        } catch (e){
                // Internet Explorer Browsers
        try{
                ajaxRequestW = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
        try{
                ajaxRequestW = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (e){
                // Something went wrong
                alert('Your browser broke!');
                return false;
        }
        }
        }
        // Create a function that will receive data sent from the server
        ajaxRequestW.onreadystatechange = function(){
                if(ajaxRequestW.readyState == 4){
                        if(ajaxRequestW.responseText == window.watchdog){
                                window.watchdog = ajaxRequestW.responseText;
                                document.getElementById("wstatus").innerHTML = "OFFLINE";
                        }
			else{
                                window.watchdog = ajaxRequestW.responseText;
				document.getElementById("wstatus").innerHTML = "ONLINE";
			}
                }
        };
        ajaxRequestW.open('POST', 'misc/time.txt', true);
        ajaxRequestW.send(null);
}

function ajaxFunctionCPU(){
        var ajaxRequestC;  // The variable that makes Ajax possible!

        try{
                // Opera 8.0+, Firefox, Safari
                ajaxRequestC = new XMLHttpRequest();
        } catch (e){
                // Internet Explorer Browsers
        try{
                ajaxRequestC = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
        try{
                ajaxRequestC = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (e){
                // Something went wrong
                alert('Your browser broke!');
                return false;
        }
        }
        }
        // Create a function that will receive data sent from the server
        ajaxRequestC.onreadystatechange = function(){
                if(ajaxRequestC.readyState == 4){
                        document.getElementById("cstatus").innerHTML = ajaxRequestC.responseText;
                }
        };
        ajaxRequestC.open('POST', 'misc/cpu.txt', true);
        ajaxRequestC.send(null);
}

function ajaxFunctionUpdate(){
        var ajaxRequestU;  // The variable that makes Ajax possible!

        try{
                // Opera 8.0+, Firefox, Safari
                ajaxRequestU = new XMLHttpRequest();
        } catch (e){
                // Internet Explorer Browsers
        try{
                ajaxRequestU = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
        try{
                ajaxRequestU = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (e){
                // Something went wrong
                alert('Your browser broke!');
                return false;
        }
        }
        }
        // Create a function that will receive data sent from the server
        ajaxRequestU.onreadystatechange = function(){
                if(ajaxRequestU.readyState == 4){
			reply = ajaxRequestU.responseText;
			reply = reply.replace("\n","");

			updating = document.getElementById("updating");
			if(reply.split(' ')[0] == "Not"){
				alert("EMPTY");
			}
			else{
				if(reply == "TRUE" && window.uState == "FALSE"){
					window.uState = reply;
					$(".shade").fadeIn("fast");
					$("#updating").slideDown("fast");
				}
				if(reply == "FALSE" && window.uState == "TRUE"){
					window.uState = reply;
					$(".shade").fadeOut("fast")
					$("#updating").slideUp("fast", function() {
						location.reload();
					});
				}
			}
                }
        };
        ajaxRequestU.open('POST', 'conf/updating.state', true);
        ajaxRequestU.send(null);

}

function ajaxFunctionBrief(){
	if(window.uState == "TRUE"){

        var ajaxRequestB;  // The variable that makes Ajax possible!

        try{
                // Opera 8.0+, Firefox, Safari
                ajaxRequestB = new XMLHttpRequest();
        } catch (e){
                // Internet Explorer Browsers
        try{
                ajaxRequestB = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
        try{
                ajaxRequestB = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (e){
                // Something went wrong
                alert('Your browser broke!');
                return false;
        }
        }
        }
        //Create a function that will receive data sent from the server
        ajaxRequestB.onreadystatechange = function(){
                if(ajaxRequestB.readyState == 4){
			replyB = ajaxRequestB.responseText;
			document.getElementById("brief").innerHTML = replyB;
                }
        };
        ajaxRequestB.open('POST', 'conf/updateBreif.php', true);
        ajaxRequestB.send(null);
	}
}

function ping(){
	$.ajax({
		url: 'index.php',
		success: function(result){
			if(window.uState == "FALSE"){
				document.getElementById("warning").innerHTML = "SYSTEM UPDATING...";
                                document.getElementById("brief").innerHTML = "Please wait...";
                                document.getElementById("sstatus").innerHTML = "ONLINE";
                                $(".shade").fadeOut("fast");
                                $("#updating").slideUp("fast");
			}
		},
		error: function(result){
			if(window.uState == "FALSE"){
                                document.getElementById("warning").innerHTML = "ELECTROPI IS OFFLINE!";
                                document.getElementById("brief").innerHTML = "Please wait...";
                                document.getElementById("sstatus").innerHTML = "OFFLINE";
                                $(".shade").fadeIn("fast");
                                $("#updating").slideDown("fast");
                        }

		}
	});
}

function haptic(){
	if(window.supportsVibrate == true && <?php echo json_encode($haptic);?> == "ENABLED"){
		window.navigator.vibrate(50);
	}
}

function heightShift(){
	var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	var maxW = <?php echo json_encode($maxWidth);?>;
	spacer = document.getElementById("headerSpace");
	currentHeight = spacer.style.height;
	currentHeight = currentHeight.replace("px","");
	currentHeight = currentHeight.valueOf() - 10;
	if(w.valueOf() > maxW.valueOf()){
		h = (w.valueOf() - maxW.valueOf()) / 10;
		if(h > 50){
			h = 50;
		}
		if(currentHeight <= 50){
			spacer.style.height = (h+10) + "px";
		}
	}
}

$(window).resize(function() {
	heightShift();
});

$(function(){  // $(document).ready shorthand
	if(<?php echo json_encode($notifications);?> == "ENABLED"){
	        setInterval(ajaxFunctionNotification, 200);
	        setInterval(notifyTimeout, 1000);
	}
	ajaxFunctionUpdate();
	ajaxFunctionWatchdog();
	heightShift();

        setInterval(ajaxFunctionWatchdog, 1000);
        setInterval(ajaxFunctionCPU, 500);
        setInterval(ajaxFunctionUpdate, 1000);
        setInterval(ajaxFunctionBrief, 250);
	setInterval(ping, 1000);
});

</script>

<STYLE type="text/css">
	a{text-decoration:none;color:<?php echo $onColor;?>;}
	.greenBack{background-color:<?php echo $onColor;?>;margin-bottom: -64px;z-index:-2;-webkit-box-shadow: 0px 0px 23px 0px <?php echo $onColor;?>;-moz-box-shadow:0px 0px 23px 0px <?php echo $onColor;?>;box-shadow:0px 0px 23px 0px <?php echo $onColor;?>;}
	.redBack{background-color:<?php echo $offColor;?>;margin-bottom: -64px;z-index:-2;}
	#linkButton{color:<?php echo $offColor;?>;}
        .beta{display:<?php echo $betaVisibility;?>;}
	#appPreview{width: 64px;height: 64px;background-position: center;background-size: cover;}
	#actionName{color:<?php echo $offColor;?>;}
</STYLE>

<div id="updating" style="display:none;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FFEB9B;color: #242424;padding: 20px;margin-bottom: 20px;font-size: 20px;margin-left:auto;margin-right:auto;max-width: <?php echo $maxWidth;?>px;">
		<tr>
			<td id="warning" style="padding:0px 10px;padding-top:10px;text-align:left;">SYSTEM UPDATING...</td>
		</tr>
		<tr>
			<td id="brief" style="padding:0px 10px;font-size:16px;">LOADING...</td>
		</tr>
	</table>
</div>
<div class="shade"></div>
<div id="wrapper">
<div id="headerSpace"></div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-left:auto;margin-right:auto;max-width: <?php echo $maxWidth;?>px;display:<?php echo $headerVisibility;?>;">
	<tr id="headerRow">
		<td id="headerCell"><a href="index.php"><img id="logo" src="images/tx_animation_slow.gif?<?php echo date('Ymdgis');?>"></a><font id="logoText" style="color:<?php echo $offColor; ?>;padding-top: 10px;vertical-align: top;">ELECTRO</font>PI <font style="font-size: 30px;vertical-align: super;"><?php echo $title;?></font></td>
		<td id="horizontalSpace"></td>
		<td id="settingsBtn" style="display:<?php echo $settingsVisibility;?>;"><a href="setup.php"><span style="width: 64px;height: 64px;position: absolute;margin-top: -32px;"></span></a></td>
	</tr>
	<tr id="verticalSpace"></tr>
</table>
>>>>>>> ac0ef8c4385863d39a1ff6ca8ce1dd9d1df7f91c
