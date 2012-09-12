#!/usr/bin/env python
#
# timeclock-rfid-reader
#

import os
import time
import MySQLdb

# main method
def main():
    os.system('stty -F /dev/ttyUSB0 speed 2400') #Sets Baud rate
    readHardware() #Reads from ttyUSB0

def readHardware():
    lastReadTime = 0
    lastCardID = 0
    while (1):
        f = open('/dev/ttyUSB0')
        f.readline()
        cardID = f.read(10) #Reads 10 characters and sets it to cardID
        if time.time() - lastReadTime > 10 or lastCardID != cardID: #Checks for a clock during the last 10 seconds and if its the same card
            accessDatabase(cardID)
            lastReadTime = time.time()
            lastCardID = cardID

def accessDatabase(cardID):
    try: #Connects to the database
        conn = MySQLdb.connect (host = "localhost", user = "root", passwd = "k4b00mk4b00m", db = "timeclock")
    except: #Exits if database doesn't exist
        print "Database not found"
        exit()

    #Initalizes variables
    clockedIn = False
    identifiedUser = False

    #Creates cursor and checks for user's name in the database
    cursor = conn.cursor()
    cursor.execute("SELECT name FROM users WHERE rfid = '"+ cardID +"'")
    row = cursor.fetchone()
    if cursor.rowcount != None:
        dbUser = (row[0])
        identifiedUser = True

    #Catches unknown IDs
    if identifiedUser == False:
        print("System doesn't recognize ID: " + cardID)
    else:
        #Finds if user exists in the record and their current status
        cursor.execute("SELECT name FROM record WHERE name = '" + dbUser + "' AND clockin != '' AND clockout = 0")
        row = cursor.fetchone()
        if cursor.rowcount > 0:
	    clockedIn = True

        #Selects the users status and does the opposite action
        #Clocks in a user
        if clockedIn == False:
            cursor.execute("INSERT INTO record (name, clockin) VALUES('" + dbUser + "', '" + str(int(time.time())) + "')")
            cursor.execute("DELETE FROM latestaction WHERE id > 0")
            cursor.execute("INSERT INTO latestaction(name, time, action) VALUES('" + dbUser + "', '" + str(int(time.time())) + "', 'clockin')")
            print (dbUser + " clocked in at " + str(int(time.time())))

        #Clocks out the user
        else:
            cursor.execute("UPDATE record SET clockout = '" + str(int(time.time())) + "' WHERE name = '" + dbUser + "' AND clockin != '' AND clockout = 0")
            cursor.execute("DELETE FROM latestaction WHERE id > 0")
            cursor.execute("INSERT INTO latestaction(name, time, action) VALUES('" + dbUser + "', '" + str(int(time.time())) + "', 'clockout')")
            print (dbUser + " clocked out at " + str(int(time.time())))

    #Closes database communication
    cursor.close()

# allow use as a module or standalone script
if __name__ == "__main__":
    main()
