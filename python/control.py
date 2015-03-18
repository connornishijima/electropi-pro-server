from __future__ import division # Futures have to be first-line imports

# Shameless giant ASCII-art logo
print " "
print "///////////////////////////////////////////////////////////////////////"
print " "
print '8888888888 888                   888                     8888888b.  d8b '
print '888        888                   888                     888   Y88b Y8P '
print '888        888                   888                     888    888     '
print '8888888    888  .d88b.   .d8888b 888888 888d888  .d88b.  888   d88P 888 '
print '888        888 d8P  Y8b d88P"    888    888P"   d88""88b 8888888P"  888 '
print '888        888 88888888 888      888    888     888  888 888        888 '
print '888        888 Y8b.     Y88b.    Y88b.  888     Y88..88P 888        888 '
print '8888888888 888  "Y8888   "Y8888P  "Y888 888      "Y88P"  888        888' 
print " "
print "///////////////////////////////////////////////////////////////////////"
print " "

# Import modules
import phue
from phue import Bridge
import subprocess
import logging
import os
import time
import datetime
from time import gmtime, strftime
import sys
import RPi.GPIO as GPIO
import argparse
import urllib2
from ConfigParser import ConfigParser
import tornado.ioloop
import tornado.web
import tornado.websocket
import tornado.template
import threading
from threading import Thread
import string
import random
import psutil
import commands
from prettytable import PrettyTable

#///////////////////////////////////////////////////////////////////////////////////////////////////////
# INITIAL SETUP ////////////////////////////////////////////////////////////////////////////////////////
#///////////////////////////////////////////////////////////////////////////////////////////////////////

# READ CONFIG ////////////////////////////
global config
config = ConfigParser()
config.read('/var/www/config/settings.ini')
rootDir = config.get("SETTINGS","rootDir").strip('"')
webDir = config.get("SETTINGS", "webDir").strip('"')
rgbLed = config.get("SETTINGS", "rgbLed").strip('"')
masterFreq = config.get("SETTINGS", "masterFreq").strip('"')
boardType = config.get("SETTINGS", "boardType").strip('"')
hueTTime = config.get("SETTINGS", "hueTTime").strip('"')

# CHANGE DIRECTORY ///////////////////////
os.chdir(rootDir)

# KILL LAST INSTANCE OF THIS WATCHDOG  ///
pid = os.getpid()
with open("python/pid.temp") as f:
        lastPid = f.read()
os.system("sudo kill -9 "+lastPid)
with open("python/pid.temp","w") as f:
        f.write(str(pid))
print "PID is:",pid,"- killed",lastPid

#///////////////////////////////////////////////////////////////////////////////////////////////////////
# FUNCTIONS HERE ///////////////////////////////////////////////////////////////////////////////////////
#///////////////////////////////////////////////////////////////////////////////////////////////////////

#/////////////////////////////////////////////////////////////////////////////////////
#// COMMUNICATION ////////////////////////////////////////////////////////////////////

# Main handler for HTML5 WebSockets
class MainHandler(tornado.web.RequestHandler):
	def get(self):
		loader = tornado.template.Loader(".")
		self.write(loader.load("/var/www/header.php").generate())

# Web Socket Handler - passes incoming messages to parseCommand()
class WSHandler(tornado.websocket.WebSocketHandler):
	global connections
	connections = set()
	
	def check_origin(self, origin):
		return True
	def open(self):
		connections.add(self)
		print 'connection opened...'
		self.write_message("The server says: 'Hello'. Connection was accepted.")
	
	def on_message(self, message):
		parseCommand(message)
		self.write_message(message)
	
	def on_close(self):
		connections.remove(self)
		print 'connection closed...'

# This is called in a seperate thread than the main programe, and is a lopp reading WebSockets
def runServer():
	application.listen(9393)
	print "SERVER STARTED."
	tornado.ioloop.IOLoop.instance().start()

# Sends WebSockets messages to all connected browsers at once
def sendAll(msg):
	print "SENT TO CLIENTS:",str(msg)
	colorWrite("blue")
	try:
		for con in connections:
			con.write_message(str(msg))
	except:
		pass

# Lets footer.php know that the watchdog is running
def sendAliveSignal(size=6, chars=string.ascii_uppercase + string.digits):
	s = ''.join(random.choice(chars) for _ in range(size))
	with open("/var/www/watchdog.rng","w") as f:
		f.write(s)

# FUNCTION TO HANDLE COMMANDS FROM WEB SOCKETS CLIENTS
def parseCommand(commandIN):
	global config
	config = ConfigParser()
	config.read('/var/www/config/settings.ini')

	print "RECEIVED: "+commandIN
	colorWrite("green")
	command = commandIN.strip("\n")
	command = command.split(":")
	type = command[0]

	if type == "COM-RF":
		print "COM-RF"
		input = command[1]
		inputFreq = input[:3]
		print "FREQUENCY IS",inputFreq

		if boardType == "STANDARD":
			if inputFreq == masterFreq:
				com = "sudo nice -n -20 "+input[3:]+" "+inputFreq
				os.system(com)
			else:
				print "Sending command to slave instead!"
		elif boardType == "PRO":
			com = "sudo nice -n -20 "+input[3:]+" "+inputFreq
			os.system(com)

	elif type == "AJAX-UPDATE":
		UID = command[1]
		newState = command[2]
		if newState == "1":
			notify("SWITCHED "+getSwitchName(UID)+" ON","power")
		if newState == "0":
			notify("SWITCHED "+getSwitchName(UID)+" OFF","power")
		print "AJAX updating",UID+"'s state to",newState
		sendAll("AJAX:"+UID+":"+newState)
		setConfigLine("/var/www/data/switches/"+UID+"/info.ini","HTML","State",str(newState))

	elif type == "LEARN":
		freq = command[1]
		state = command[2]
		os.system("sudo python /var/www/python/decode.py "+freq+" "+state)
		with open("/var/www/python/decode."+state) as f:
			message = f.read()
		sendAll("LEARNED|"+message)

	elif type == "ACTION":
		AID = command[1]
		doAction("data/actions/"+AID+".action")

	elif type == "FIX-SWITCHES":
		fixSwitchPositions()

	elif type == "HUE-COLOR":
		try:
			t = int(float(config.get("SETTINGS", "hueTTime").strip('"')) * 10)
			lampSentIn = str(command[1])
			color = command[2]
			lampSent = lampSentIn.split("_")
			print lampSent
			hueColor(lampSent,color,t)
			sendAll("HUE-AJAX|"+lampSentIn+"|"+color)
		except Exception as inst:
			print inst
			print "HUE PARSE FAILED! RETRYING in 0.5s..."
			time.sleep(0.5)
			parseCommand(commandIN)

	elif type == "HUE-ALERT":
		try:
			lampSent = str(command[1])
			alertType = str(command[2])
			lampSent = lampSent.split("_")
			print lampSent
			hueAlert(lampSent,alertType)
		except Exception as inst:
			print inst
			print "HUE PARSE FAILED! RETRYING..."
			parseCommand(commandIN)

	elif type == "RST":
		restart_program("NORM")

	elif type == "RST-FAST":
		restart_program("FAST")

	elif type == "MANUAL":
		print "MANUAL"
	else:
		print "Client says: "+commandIN

def notify(msg,type):
	if type == "power":
		toSend = "NOTIFY|<img src='images/lightning-icon.png' style='width:40px;height:64px;margin-right:-24px;'/>|<font style='margin-left:-14px;'>"+str(msg)+"</font>"
		sendAll(toSend)
		print toSend
	if type == "general":
		toSend = "NOTIFY||<font style='margin-left:-64px;'>"+str(msg)+"</font>"
		sendAll(toSend)
		print toSend

#/////////////////////////////////////////////////////////////////////////////////////
#// TOOLS ////////////////////////////////////////////////////////////////////////////

def getSwitchName(UID):
	foundName = 0
	while foundName == 0:
		try:
			UIDconfig = ConfigParser()
			UIDfile = '/var/www/data/switches/'+UID+'/info.ini'
			print UIDfile
			UIDconfig.read(UIDfile)
			nick = UIDconfig.get("ID", "Nickname")
			nick = nick.replace("_"," ")
			foundName = 1
		except:
			print "INI READ ERROR!"
	return nick

def setConfigLine(file,section,key,val):
	config = ConfigParser()
	config.read(file)
	config.set(section,key,val)
	with open(file, 'wb') as configfile:
		config.write(configfile)

def getCPUusage():
	cpu = int(psutil.cpu_percent())
        if not cpu == 0:
        	print "CPU:",cpu
		sendAll("CPU|"+str(cpu))

def fixSwitchPositions():
	inconsistency = 0

	switches = commands.getoutput("sudo ls data/switches").replace("\n"," ").split(" ")
	if str(switches) == "['']":
		return
	currentMessID = []
	currentMessPos = []
	currentMessPosOld = []
	index = 0
	
	for item in switches:
		UID = item
		with open("data/switches/"+UID+"/info.ini") as f:
			switchInfo = f.read()
		switchInfo = switchInfo.split("\n")
		for item in switchInfo:
			line = item
			try:
				line = line.split(" = ")
				key = line[0]
				val = line[1]
				if key == "position":
					switchPos = int(val)
					index += 1
			except:
				pass
	
		currentMessID.append(UID)
		currentMessPos.append(switchPos)
		currentMessPosOld.append(switchPos)
	
	print currentMessID
	print currentMessPos
	
	correctNeeded = len(currentMessPos)
	correctSwitches = 0
	
	while correctSwitches != correctNeeded:
		correctSwitches = 0
		gapStart = 0
		gapStarted = False
		shift = 0
		count = 1
		countTo = 0
		for item in currentMessPos:
			if int(item) > countTo:
				countTo = int(item)
		
		while count <= countTo:
			numFound = 0
			for item in currentMessPos:
				if int(item) == count:
					numFound = 1
			if numFound == 1:
				if gapStarted == True:
					shift = gapStart - count
					count = 999
				correctSwitches += 1
			else:
				if gapStarted == False:
					inconsistency = 1
					gapStarted = True
					gapStart = count
			count += 1
		
		index = 0
		while index < len(currentMessPos):
			if currentMessPos[index] > gapStart:
				currentMessPos[index] = currentMessPos[index]+shift
			index += 1
	
	index = 0
	switchCount = len(currentMessID)
	
	while index < switchCount:
		print "--------------------------------------------"
		print "UID: "+str(currentMessID[index])+" NEW:"+str(currentMessPos[index])+" OLD:"+str(currentMessPosOld[index])
		print "--------------------------------------------"
		UID = str(currentMessID[index])
		outString = ""
	        with open("data/switches/"+UID+"/info.ini") as f:
	                switchInfo = f.read()	
		switchInfo = switchInfo.split("\n")
		for item in switchInfo:
			line = item
			outLine = line
			try:
				line = line.split(" = ")
				key = line[0]
				if key == "position":
					outLine = "position = "+str(currentMessPos[index])
			except:
				pass
	
			outString += outLine+"\n"
	
		with open("data/switches/"+UID+"/info.ini","w") as f:
			f.write(outString)
	
		index += 1
	
	print "DONE SORTING SWITCHES"
	if inconsistency == 1:
		print "An inconsistency in your switch positions was automatically corrected."

def doAction(file):
	with open(file) as f:
		actions = f.read()
	actions = actions.split("\n")
	actionNick = actions[0]
	for item in actions:
		if len(item) > 1:
			if item[0] != "*" and item[0] != "$" and item[0] != "/":
				item = item.split(" | ")
				UID = item[0]
				newState = item[1]
				try:
					with open("data/switches/"+UID+"/info.ini") as f:
						switchInfo = f.read()
					switchInfo = switchInfo.split("\n")
					for itemS in switchInfo:
						try:
							itemS = itemS.split(" = ")
							key = itemS[0]
							val = itemS[1]
							if key == "state":
								oldState = val;
						except:
							pass
					if str(oldState) != str(newState):
						com = item[2]
						parseCommand("AJAX-UPDATE:"+UID+":"+newState)
						parseCommand(com)
					else:
						print "Skipping",UID,", state is already",newState+"."

					UIDPresent.append(UID)
				except:
					print "ACTION CALLS FOR MISSING SWITCH!"
					fixActions()
					pass

def fixActions():
	print "Checking actions for inconsistencies..."
	actions = commands.getoutput("sudo ls data/actions").replace("\n"," ").split(" ")
	if str(actions) == "['']":
		return

	for file in actions:
		file = "data/actions/"+file
		UIDNeeded = []
		UIDPresent = []
		inconsistency = 0
	
		with open(file) as f:
			actions = f.read()
		actions = actions.split("\n")
		actionNick = actions[0]
		for item in actions:
			if len(item) > 1:
				if item[0] != "*" and item[0] != "$" and item[0] != "/":
					item = item.split(" | ")
					UID = item[0]
					UIDNeeded.append(UID)
					try:
						with open("data/switches/"+UID+"/info.ini") as f:
							switchInfo = f.read()
	
						UIDPresent.append(UID)
					except:
						inconsistency = 1
						print "ACTION "+actionNick+" CALLS FOR MISSING SWITCH!"
						pass
	
		if inconsistency == 1:
			print "Something wrong with action "+actionNick+", investigating..."
			missingSwitches = []
			outString = ""
			outLines = []
			for item in UIDNeeded:
				if item in UIDPresent:
					pass
				else:
					print "SWITCH "+item+" IS NO LONGER PRESENT!"
					missingSwitches.append(item)
			for item in missingSwitches:
				with open(file) as f:
			                actions = f.read()
			        actions = actions.split("\n")
			        for itemA in actions:
			                if len(itemA) > 1:
						UID = itemA[:5]
						if UID in missingSwitches:
							warning = "// (SWITCH with UID "+UID+" was deleted, so this command was removed automatically.)"
							if not warning in outLines:
								outLines.append(warning)
						else:
							if not itemA in outLines:
								outLines.append(itemA)
			for item in outLines:
				outString += item+"\n"
	
			with open(file,"w") as f:
				f.write(outString)
	
			print "A deleted switch was called in this action, the action was altered to no longer call for these switches:"
			print missingSwitches
	
			with open(file) as f:
	                        actions = f.read()
	                actions = actions.split("\n")
	
			switches = commands.getoutput("sudo ls data/switches").replace("\n"," ").split(" ")
	
			anyLeft = 0
	                for item in actions:
	                        if len(item) > 1:
					UID = item[:5]
					if UID in switches:
						anyLeft = 1
			if anyLeft == 0:
				print "Action "+actionNick+" now contains no current switches, deleting empty action."
				os.system("sudo rm "+file)
			else:
				print "After changes, action "+actionNick+" still contains at least one current switch."

def scheduleCheck():
	sendAll("WATCHDOG")
	global lastTimeString
	t = str(datetime.datetime.now().time())
	t = t.split(".")
	x = str(t[0])
	x = x.split(":")
	hour = x[0]
	min = x[1]
	sec = x[2]
	timeString = hour+":"+min
	timeStringWeb = timeString+":"+sec
	sendAll("TIME|"+timeStringWeb)
	print "TIME = ",hour,min,sec
	if not timeString == lastTimeString:
		eventFound = False
		lastTimeString = timeString
		print "TIME CHANGED, CHECKING SCHEDULED EVENTS..."
		notify("TIME IS NOW: "+timeString,"general")
		with open("python/event.list") as f:
			eventList = f.read()
		eventList = eventList.split("\n")
		for item in eventList:
			if len(item) > 3:
				item = item.split("|")
				nickS = item[0]
				typeS = item[1]
				hourS = item[2]
				minS = item[3]
				AIDS = item[4]
				eventTime = hourS+":"+minS
				if timeString == eventTime:
					eventFound = True
					print "EVENT",nickS,"HAPPENING!"
					notify("EVENT "+nickS+" HAPPENING!","general")
					doAction("data/actions/"+AIDS+".action");
					if typeS == "TEMP":
						print "Deleting temporary event '"+nickS+"'..."
						outString = ""
						with open("python/event.list") as f:
							eventList = f.read()
                				eventList = eventList.split("\n")
                				for itemLine in eventList:
                        				if len(itemLine) > 3:
                                				item = itemLine.split("|")
                                				AIDD = item[4]
								if AIDD != AIDS:
									outString = outString + itemLine + "\n"
						with open("python/event.list","w") as f:
							f.write(outString)
		if eventFound == False:
			print "Nothing scheduled."

# FUNCTION TO WRITE COLOR TO GPIO
def colorWrite(color):
	if rgbLed == "ENABLED":
		if color == "kill":
			GPIO.output(rPin,1)
			GPIO.output(gPin,1)
			GPIO.output(bPin,1)
		# this writes colors to the LED
		elif color == "red":
			GPIO.output(rPin,0)
			GPIO.output(gPin,1)
			GPIO.output(bPin,1)
		elif color == "green":
			GPIO.output(rPin,1)
			GPIO.output(gPin,0)
			GPIO.output(bPin,1)
		elif color == "blue":
			GPIO.output(rPin,1)
			GPIO.output(gPin,1)
			GPIO.output(bPin,0)

#/////////////////////////////////////////////////////////////////////////////////////
#// PHILIPS HUE //////////////////////////////////////////////////////////////////////

# Turn Hue bulb(s) on
def hueOn(lampList,t = "X"):
	global hueTTime
	global lampStates
	global b

	lampListTemp = []

	for item in lampList:
		lampStates[item] = True
	if t == "X":
                t = int(hueTTime*10)
        else:
                t = t

	for item in lampList:
		if lampStates[str(item)] != "X":
			lampListTemp.append(str(item))
		else:
			print "LAMP '"+str(item)+"' IS OFFLINE"

	command = {"on":True,"transitiontime":t}
	b.set_light(lampListTemp, command)
	print "HUE COMMAND - Set state of Hue Lamp(s) '"+str(lampListTemp)+"' to ON."

# Turn Hue bulb(s) off
def hueOff(lampList,t = "X"):
	global hueTTime
	global lampStates
	global b

	lampListTemp = []

	for item in lampList:
		lampStates[item] = False
	if t == "X":
                t = int(hueTTime*10)
        else:
                t = t

	for item in lampList:
		if lampStates[str(item)] != 'X':
			lampListTemp.append(str(item))
		else:
			print "LAMP '"+str(item)+"' IS OFFLINE"

	command = {"on":False,"transitiontime":t}
	b.set_light(lampListTemp, command)
	print "HUE COMMAND - Set state of Hue Lamp(s) '"+str(lampListTemp)+"' to OFF."

# Change Hue bulb(s) color. If set to #000000 (black) shut the lamp off. If the lamp is off when the change is made, turn it on first.
def hueColor(lampList,hex,t = "X"):
	global hueTTime
	global lampStates
	global lampHexs
	global b

	lampListTemp = []

	if t == "X":
		t = int(hueTTime*10)
	else:
		t = t

	if hex!="000000" and hex!="#000000":
		rgb = hex2rgb(hex)
		color = rgb2xyz(rgb[0],rgb[1],rgb[2])
		x = color[0]
		y = color[1]
		z = color[2]
		for item in lampList:
			if lampStates[str(item)] == False:
				hueOn(lampList,t)
			if lampStates[str(item)] == 'X':
				print "LAMP '"+str(item)+"' IS OFFLINE"
			lampListTemp.append(str(item))
			lampHexs[str(item)] = hex
		command = {"bri":int(z),"xy":[x,y],"transitiontime":t}
		b.set_light(lampListTemp, command)
	else:
		hueOff(lampList,t)
	print "HUE COMMAND - Set color of Hue Lamp(s) '"+str(lampListTemp)+"' to "+hex+"."
	print "\n\n"

# Flash the Hue bulb(s) listed to this command
def hueAlert(lampList,type):
	global lampStates
	global b

	lampListTemp = []

	for item in lampList:
		if lampStates[str(item)] == 'X':
			print "LAMP '"+str(item)+"' IS OFFLINE"
		lampListTemp.append(str(item))
	if type=="once":
		command = {"alert":"select"}
		b.set_light(lampListTemp, command)
		print "HUE COMMAND - Single alert on Hue Lamp(s) '"+str(lampListTemp)+"."
	if type=="start":
		command = {"alert":"lselect"}
		b.set_light(lampListTemp, command)
		print "HUE COMMAND - Started alert on Hue Lamp(s) '"+str(lampListTemp)+"."
	if type=="stop":
		command = {"alert":"none"}
		b.set_light(lampListTemp, command)
		print "HUE COMMAND - Stopped alert on Hue Lamp(s) '"+str(lampListTemp)+"."
	print "\n\n"

# Philips Phucktards use a complicated color space in place of RGB, this is the best conversion method I have found. -_-
# rgb > xyz works great, xyz > rgb currently does not. Make Philips add RGB/HEX to their API.
def xyz2rgb(X,Y,Z):

	Z = (1/255)*Z
	R = 0.7982 * X + 0.3389 * Y - 0.1371 * Z;
	G = -0.5918 * X + 1.5512 * Y + 0.0406 * Z;
	B = 0.0008 * X + 0.0239 * Y + 0.9753 * Z;
	
	R = int(255*R)
	G = int(255*G)
	B = int(255*B)
	return R,G,B

# RGB -> XYZ colorspace
def rgb2xyz(R,G,B):
	if R==0 and G==0 and B==0:
		return 0,0,0
	else:
		X = 1.076450 * R - 0.237662 * G + 0.161212 * B;
		Y = 0.410964 * R + 0.554342 * G + 0.034694 * B;
		Z = -0.010954 * R - 0.013389 * G + 1.024343 * B;
		z = max(R,G,B)
	
		print "z IS "+str(z)

		try:
			x = X / (X + Y + Z)
			y = Y / (X + Y + Z)
		except ZeroDivisionError:
			x = X
			y = Y
			Z = 0
			print x,y,Z
	
		if x<0:
			x = 0
		if y<0:
			y = 0
		if z<0:
			z = 0
	
		if x>1:
			x = 1
		if y>1:
			y = 1
		if z>254:
			z = 254

		print z
	
		return x,y,z

# Converts HEX from the Web UI to RGB for RGB -> XYZ colorspace conversion
def hex2rgb(value):
	value = value.lstrip("#")
	if value != "000000":
		lv = len(value)
		return tuple(int(value[i:i + lv // 3],16) for i in range(0, lv, lv //3))
	else:
		return (0,0,0)

#Recursively converts dictionary keys to strings. PHue's API returns Unicode strings sometimes.
def convert_keys_to_string(dictionary):
    if not isinstance(dictionary, dict):
        return dictionary
    return dict((str(k), convert_keys_to_string(v)) 
        for k, v in dictionary.items())

# Retreive various dictionaries of the Hue lamps' states, colors and more. Write results to hueLamps.list
def getHueLamps():
	print "Retreiving Hue lamp list..."
	global lampStates
	global lampBrights
	global lampXYs
	global lampHexs
	lampStates = {}
	lampBrights = {}
	lampXYs = {}
	lightNames = b.get_light_objects('name')
	for light in lightNames:
		state = b.get_light(str(light), 'on')
		bri = b.get_light(str(light), 'bri')
		xy = b.get_light(str(light), 'xy')
		reach = b.get_light(str(light), 'reachable')

		l = str(light.encode('ascii','ignore'))
		if reach == True:
			lightNames[l] = state
		else:
                        lightNames[l] = "X"
		lampBrights[l] = bri
		lampXYs[l] = xy
		try:
			if not l in lampHexs:
				lampHexs[l] = "XXXXXX"
		except:
			lampHexs = {}
			lampHexs[l] = "XXXXXX"			
	
	lampStates = convert_keys_to_string(lightNames)
	t = PrettyTable(['Name', 'State', 'XY', 'Hex'])

	hueList = ""

	for item in lampStates:
		t.add_row([ str(item),lampStates[item],lampXYs[item],lampHexs[item] ])
		hueList += str(item)+"|"+str(lampStates[item])+"|"+str(lampXYs[item])+"|"+str(lampHexs[item])+"\n"

	with open("python/hueLamps.list","w") as f:
		f.write(hueList)
	print hueList
	print t

#////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

#///////////////////////////////////////////////////////////////////////////////////////////////////////
# MAIN PROGRAM /////////////////////////////////////////////////////////////////////////////////////////
#///////////////////////////////////////////////////////////////////////////////////////////////////////

logging.basicConfig()

# define output pins...
txPin = 18
rPin = 11
gPin = 15
bPin = 13

# set up GPIO...
GPIO.setmode(GPIO.BOARD)

GPIO.setup(txPin,GPIO.OUT)
GPIO.setup(rPin,GPIO.OUT)
GPIO.setup(gPin,GPIO.OUT)
GPIO.setup(bPin,GPIO.OUT)
rPWM = GPIO.PWM(rPin,50)
gPWM = GPIO.PWM(gPin,50)
bPWM = GPIO.PWM(bPin,50)

rPWM.start(0)
gPWM.start(100)
bPWM.start(100)

print "STARTING SERVER..."

application = tornado.web.Application([
	(r'/ws', WSHandler),
	(r'/', MainHandler),
	(r"/(.*)", tornado.web.StaticFileHandler, {"path": "./resources"}),
])

# This is used by scheduleCheck to check each timestamp against the last.
global lastTimeString
lastTimeString = "X"

# Philips Hue Transistion Time in seconds.
global hueTTime
hueTTime = 0.6

# Connect to the Philips Hue
global b
try:
        b = Bridge('192.168.1.39')
        b.connect
except phue.PhueRegistrationException:
        auth = False
        while auth == False:
                print "LINK BUTTON NOT PRESSED! WAITING FOR PRESS..."
                try:
                        b = Bridge('192.168.1.39')
                        b.connect
                        auth = 1
                except:
                        pass
                time.sleep(1)

# Flash red to show the user we're up...
if rgbLed == "ENABLED":
	fade = 0
	while fade < 100:
		fade += 1
		rPWM.ChangeDutyCycle(fade)
		time.sleep(0.005)
else:
	colorWrite("kill")

# Main program -------------------------------------------------------------
def doLoop():
	blipCount = 1
	bloopCount = 1

	while True:
		time.sleep(0.1)
		colorWrite("kill")
		blipCount += 1
		bloopCount += 1
	
		if blipCount > 3:
			blipCount = 0		
			getCPUusage()
			scheduleCheck()
	
		if bloopCount >= 30:
			try:
				getHueLamps()
			except:
				pass
			colorWrite("red")
			print "BLOOP"
			bloopCount = 0
			fixSwitchPositions()
			fixActions()
		
		f = open("python/time.txt","w")
		f.write(str(time.time()))
		f.close()
	
		with open("python/command.list","r") as f:
			command = f.read()
	
		if len(command) > 0:
			f = open("python/command.list","w")
			f.write("")
			f.close()
	
		command = command.split("\n")
		for item in command:
			if len(item) > 1:
				colorWrite("green")
				print "!----------------- RECEIVED COMMAND: " + item
				parseCommand(item)

# Let footer.php know we're alive every 0.5s
def doAlive():
	while True:
		sendAliveSignal()
		time.sleep(0.5)

# LETS GO!
Thread(target = runServer).start()
Thread(target = doLoop).start()
Thread(target = doAlive).start()
