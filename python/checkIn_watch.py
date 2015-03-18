import time
import os
import datetime
import sys
from ConfigParser import ConfigParser
<<<<<<< HEAD

print "CHECK IN WATCHDOG"

os.chdir("/var/www/python")
config = ConfigParser()
config.read('/var/www/config/settings.ini')
=======
import requests

print "CHECK IN WATCHDOG"

os.chdir("/websites/electropi/www/python")
config = ConfigParser()
config.read('/websites/electropi/www/config/settings.ini')
>>>>>>> cf616fd2afe1db88117e4778cf5e653ba568bb8e
global deviceInterval
deviceInterval = config.get("SETTINGS","deviceInterval").strip('"')
deviceTimeout = config.get("SETTINGS","deviceTimeout").strip('"')
deviceInterval = int(deviceInterval) + int(deviceTimeout)
global skipBeat
skipBeat = 0
<<<<<<< HEAD
=======
count = 5
>>>>>>> cf616fd2afe1db88117e4778cf5e653ba568bb8e

def last_line(in_file, block_size=1024, ignore_ending_newline=False):
    suffix = ""
    in_file.seek(0, os.SEEK_END)
    in_file_length = in_file.tell()
    seek_offset = 0

    while(-seek_offset < in_file_length):
        # Read from end.
        seek_offset -= block_size
        if -seek_offset > in_file_length:
            # Limit if we ran out of file (can't seek backward from start).
            block_size -= -seek_offset - in_file_length
            if block_size == 0:
                break
            seek_offset = -in_file_length
        in_file.seek(seek_offset, os.SEEK_END)
        buf = in_file.read(block_size)

        # Search for line end.
        if ignore_ending_newline and seek_offset == -block_size and buf[-1] == '\n':
            buf = buf[:-1]
        pos = buf.rfind('\n')
        if pos != -1:
            # Found line end.
            return buf[pos+1:] + suffix

        suffix = buf + suffix

    # One-line file.
    return suffix

def parseRequest(log):
        log = log.split(",")
        deviceNick = log[1]
        timeSum = log[3]
        global deviceList
        deviceList[deviceNick] = timeSum

def readLastLogs():
	with open("../requestLogs.csv") as f:
		lines = f.read()
	lines = lines.split("\n")
	for item in lines:
		if len(item)>1:
			if item[:4] != "TYPE":
				item = item.split(",")
				deviceNick = item[1]
				timeSum = item[3]
				global deviceList
				deviceList[deviceNick] = timeSum
				deviceSkips[deviceNick] = 0
				deviceStates[deviceNick] = 0

def getTimeSum():
	now = datetime.datetime.now()
	midnight = now.replace(hour=0, minute=0, second=0, microsecond=0)
	seconds = (now - midnight).seconds
	return seconds

def checkDeviceTimes():
	print "\n\n\n"
	global deviceList
	global deviceInterval
	global skipBeat

	print deviceInterval

	for item in deviceList:
		global firstRun
		print "----------------"
		device = item
		logTime = deviceList[item]
		skipBeat = deviceSkips[item]
		print skipBeat
		ourTime = getTimeSum()
		print "LOG TIME:",str(logTime),"PI TIME:",str(ourTime)
		if int(logTime) < int(ourTime) - 60:
			print "X"
			if int(ourTime) < int(deviceInterval):
				newTime = 86400+ourTime
				if logTime < ourTime-60:
					logIs = "GOOD"
				else:
					logIs = "BAD"
			else:
				logIs = "BAD"
		else:
			if int(logTime) < int(ourTime):
				print "ON TIME"
				logIs = "GOOD"
			else:
				logIs = "BAD"

		if logIs == "GOOD":
			deviceSkips[item] = 0
			print device,": PRESENT"
                        if deviceStates[item] != "PRESENT":
				if firstRun == 0:
	                                deviceStates[item] = "PRESENT"
					print "DOING ACTION FOR",str(device),"FOUND!"

					with open("command.list","a") as f:
<<<<<<< HEAD
						#f.write("ACTION:IZTGSVHDYO\n") --------------------------------------------------------------------------------------------------------------!
=======
						f.write("ACTION:IZTGSVHDYO\n")
>>>>>>> cf616fd2afe1db88117e4778cf5e653ba568bb8e
				else:
					firstRun = 0
		if logIs == "BAD":
			if deviceSkips[item] < 10:
				print "SKIP"
				deviceSkips[item] += 1
			else:
				deviceSkips[item] = 0
				print device,": ABSENT"
	                        if deviceStates[item] != "ABSENT" and firstRun == 0:
					if firstRun == 0:
		                                deviceStates[item] = "ABSENT"
						print "DOING ACTION FOR",str(device),"LOST!"

						with open("command.list","a") as f:
<<<<<<< HEAD
                                                	#f.write("ACTION:KWSEXXUGYU\n") ------------------------------------------------------------------------------------------------------!
=======
                                                	f.write("ACTION:KWSEXXUGYU\n")
>>>>>>> cf616fd2afe1db88117e4778cf5e653ba568bb8e
					else:
						firstRun = 0
		print "----------------"

def checkRequestsLog():
	global lastRequest
	readLine = last_line(open("../requestLogs.csv"),1024,True)
	if readLine != lastRequest:
		lastRequest = readLine
		parseRequest(lastRequest)
		print readLine

def populateDeviceStates():
	global deviceStates

def restart_program():
<<<<<<< HEAD
	os.system("sudo python /var/www/python/checkIn_watch.py")
=======
	os.system("sudo python /websites/electropi/www/python/checkIn_watch.py")
>>>>>>> cf616fd2afe1db88117e4778cf5e653ba568bb8e
	sys.exit()

global lastRequest
lastRequest = last_line(open("../requestLogs.csv"),1024,True)
global deviceList
deviceList = {}
global deviceSkips
deviceSkips = {}
global deviceStates
deviceStates = {}
global firstRun
firstRun = 1

readLastLogs()

while True:
	try:
		checkRequestsLog() # See if new connections are made...
		checkDeviceTimes() # See if old connections have expired...
	except:
		restart_program()

<<<<<<< HEAD
	config.read('/var/www/config/settings.ini')
=======
	config.read('/websites/electropi/www/config/settings.ini')
>>>>>>> cf616fd2afe1db88117e4778cf5e653ba568bb8e
	global deviceInterval
	deviceInterval = config.get("SETTINGS","deviceInterval").strip('"')
	deviceTimeout = config.get("SETTINGS","deviceTimeout").strip('"')
	deviceInterval = int(deviceInterval) + int(deviceTimeout)
	global firstRun
	firstRun = 0
<<<<<<< HEAD
=======
	count += 1
	if count >= 5:
		count = 0
		response = requests.get('http://127.0.0.1/checkIn.php?type=android&deviceNickname=epi')
>>>>>>> cf616fd2afe1db88117e4778cf5e653ba568bb8e
	time.sleep(1)      # Do this once per second.
