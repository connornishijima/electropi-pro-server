<<<<<<< HEAD
<?php
	$parseMessageList = "";
	$UIDlist = scandir("data/switches");
	foreach($UIDlist as $UIDitem){
		if(strlen($UIDitem) == 5){
			$parseMessageList .= "parseMessage".$UIDitem."(msg.data);\n";
		}
	}
?>

<div id="footerPush"></div>
<div id="footer">
	<table <?php echo $tabStretch;?>>
		<tr style="height: 64px;">
			<td id="footerLeft">WATCHDOG: <div class="footerStats" id="watchdogState">ONLINE</div><br>CPU USAGE: <div class="footerStats" id="cpuUpdate">XX%</div></td>
			<td id="footerRight"><div id="time">TIME: <div id="timeUpdate">TIME</div></div> | DEVELOPED BY<br><a href="config.php?view=confCredits" class="footerLinks">CONNOR NISHIJIMA & FRIENDS</a></td>
		</tr>
	</table>
</div>
<div id="notifyBar">
	<table <?php echo $tabStretch;?>>
		<tr style="height: 64px;">
			<td id="notifyIcon"></td>
			<td id="notifyMessage" style="color:#000000;"></td>
		</tr>
	</table>
</div>

<div id="dummy" style="display:none;"></div>

</div>
</div>

<script type="text/javascript">
	$( document ).ready(function() {
		setupWebSockets();
		sendMessage('CONNECTED-<?php echo $_SERVER['REMOTE_ADDR']; ?>');

		setInterval(ping,2000);

		if(<?php echo json_encode($fixSwitches);?> == "FIX"){
			sendMessage("FIX-SWITCHES");
		}

		window.notifyTimeout = 0;
		window.watchTime = 4;
		window.watchdogStart = 1;
		window.watchdogOffline = "0";
		window.watchdogTimeStamp = "X";
		window.excontent = "X";
		window.warningState = 0;
		window.serverStatus = "ONLINE";

		showBody();

		notifyChecker();
		if(<?php echo json_encode($noWatchCheck);?> != "1"){
			checkWatchdogPHP();
		}

		if(<?php echo json_encode($warningString);?> != null){
			showBanner(<?php echo json_encode($warningString);?>);
		}
	});

	function showBody(){
		setTimeout(function(){$("#whole").fadeIn("fast");},200);
	}

	function animOnce(id,animation,length){
		$("#"+id).css("-webkit-animation","none");
		$("#"+id).css("-webkit-animation",animation+" "+(length/1000)+"s");
		setTimeout(function(){
			$("#"+id).css("-webkit-animation","none");
		},length);
	}

	function notifyClient(message,icon){
		var notifyMessage = document.getElementById("notifyMessage");
		notifyMessage.innerHTML = message;
		var notifyIcon = document.getElementById("notifyIcon");
		notifyIcon.innerHTML = icon;
		window.notifyTimeout = parseInt(<?php echo $sets["SETTINGS"]["notifyHangtime"];?>);
		haptic();
		$( "#notifyBar" ).fadeIn("fast");
	}

	function notifyChecker(){
		var time = window.notifyTimeout;
		if(time > 0){
			window.notifyTimeout = time - 1;
		}
		else{
			$( "#notifyBar" ).fadeOut("fast");
		}
		setTimeout(notifyChecker, 100);
	}

	function sendAction(div,message){
		animOnce(div,"flashAction",500);
		sendMessage(message);
	}

	function checkWatchdogPHP(){
		var wState = 0;
		$.get("checkWatchdog.php", function(data){
			window.excontent = data;
		});
		if(window.excontent != window.watchdogTimeStamp){
				window.watchdogTimeStamp = window.excontent;
				wState = 1;
				document.getElementById("watchdogState").innerHTML = "ONLINE";
				hideWarning();
				if(window.watchdogOffline == 1){
					location.reload();
				}
		}
		else{
			if(window.watchdogStart == 1){
				window.watchdogStart = 0;
			}
			else{
				console.log(555);
				showWarning("head","desc","image");
				document.getElementById("watchdogState").innerHTML = "OFFLINE";
				window.watchdogOffline = 1;
			}
		}
		console.log(wState);
		setTimeout(checkWatchdogPHP, 1000);
	}

	function setupWebSockets(){
		if (!("WebSocket" in window)) {
			alert("Your browser does not support web sockets");
		}else{
			setupWS();
		}
		function setupWS(){
			// Note: You have to change the host var
			// if your client runs on a different machine than the websocket server
			var host = "ws://192.168.1.200:9393/ws";
			window.wsocket = new WebSocket(host);
			//console.log("socket status: " + window.wsocket.readyState);
			// event handlers for websocket
			if(window.wsocket){
				window.wsocket.onopen = function(){
				//alert("connection opened....");
				}
				window.wsocket.onmessage = function(msg){
					parseMessage(msg.data);
					if(<?php echo json_encode($controlPage);?> == "true"){
						<?php echo $parseMessageList;?>
					}
				}
				window.wsocket.onclose = function(){
				//alert("connection closed....");
				showServerResponse("The connection has been closed.");
				}
			}else{
			console.log("invalid socket");
			}
		}
	}

function sendMessage(msg){
    // Wait until the state of the socket is not ready and send the message when it is...
    waitForSocketConnection(window.wsocket, function(){
        console.log("message sent!!!");
        window.wsocket.send(msg);
    });
}
// Make the function wait until the connection is made...
function waitForSocketConnection(socket, callback){
    setTimeout(
        function () {
            if (socket.readyState === 1) {
                console.log("Connection is made")
                if(callback != null){
                    callback();
                }
                return;
            } else {
                console.log("wait for connection...")
                waitForSocketConnection(socket, callback);
            }
        }, 5); // wait 5 milisecond for the connection...
}

	function parseMessage(message){
		message = message.split("|");
		type = message[0];
		if(type == "NOTIFY"){
			icon = message[1];
			notification = message[2];
			notifyClient(notification,icon);
		}
		if(type == "TIME"){
			time = message[1];
			document.getElementById("timeUpdate").innerHTML = time;
		}
		if(type == "CPU"){
			cpu = message[1]+"%";
			document.getElementById("cpuUpdate").innerHTML = cpu;
		}
		if(type == "LEARNED"){
			code = message[1];
			learnedCode(code);
		}
	}

window.supportsVibrate = "vibrate" in window.navigator;
function haptic(){
	if(window.supportsVibrate == true){
		window.navigator.vibrate(50);
	}
}

function showWarning(head,desc,image){
	if(window.warningState == 0){
		$("#warningHead").html(head);
		$("#warningTail").html(desc);
		window.warningState = 1;
		$("#warning").fadeIn("fast");
		document.getElementById("whole").style.opacity = "0.1";
		document.getElementById("whole").style.webkitFilter = "grayscale(1)";
		$("#whole").css( 'pointer-events', 'none' );
	}
}

function hideWarning(){
	if(window.warningState == 1){
		window.warningState = 0;
		$("#warning").fadeOut("slow");
		document.getElementById("whole").style.opacity = "1";
		document.getElementById("whole").style.webkitFilter = "grayscale(0)";
		$("#whole").css( 'pointer-events', 'auto' );
	}
}

function showBanner(h){
	$("#bannerInner").html(h);
	$("#bannerWrap").fadeIn("fast");
}

function hideBanner(h){
	$("#bannerWrap").fadeOut("fast");
}

function ping(){
       $.ajax({
          url: 'checkIn.php',
	  timeout:5000,
          success: function(result){
		if(window.serverStatus != "ONLINE"){
			window.serverStatus = "ONLINE";
			setTimeout(function(){
				location.reload();
			},5000);
		}
          },
          error: function(result){
		if(window.serverStatus != "OFFLINE"){
			window.serverStatus = "OFFLINE";
			showWarning("EPi IS OFFLINE!","Control is unavailable at this time.<br>If you just rebooted your Pi, hang tight<br>and this message will subside.","image");
		}
          }
       });
}

</script>

=======

<html>
	<head>
		<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
	</head>
	<body>
		<div id="footerSpace"></div>
		<div id="notify" style="position: fixed;bottom: 0px;width: 100%;text-align: center;">
			<div id="notification"></div>
		</div>
		<div class="push"></div>
		</div>
		<div id="footer">SERVER: <div id="sstatus">LOADING... </div>&nbsp;|&nbsp;
			WATCHDOG: <div id="wstatus">LOADING...</div>&nbsp;|&nbsp;
			CPU: <div id="cstatus">LOADING...</div>&nbsp;|&nbsp;
			<font style="color:#666666;">VERSION: <a href="change.log" style="color:#999999;"><?php echo file_get_contents("conf/local.version");?></a></font>&nbsp;|&nbsp;
			DEVELOPED BY <a href="http://facebook.com/tobifilmgroup">CONNOR NISHIJIMA</a> 2013-2014
		<div>
	</body>
</html>
>>>>>>> ac0ef8c4385863d39a1ff6ca8ce1dd9d1df7f91c
