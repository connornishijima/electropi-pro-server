import commands

ssidList = ""
try:
	ssidList = commands.getoutput("sudo iwlist wlan0 scan")
except:
	ssidList = "NONE"

ssidList = ssidList.split("\n")
for item in ssidList:
	try:
		if item[20:25] == "ESSID":
			essid = item[20:]
			ssid = essid.split(":")[1].strip('"')
			print ssid
	except:
		pass
