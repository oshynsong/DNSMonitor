##################################################
### @author OshynSong                          ###
### @time   2014-12                            ###
### @desc   Get the ip location by chinaz.com  ###
##################################################

import re
import requests
import sys
reload(sys)
sys.setdefaultencoding('utf8')

IP_LOCATION_URL = 'http://ip.chinaz.com/IP/?jdfwkey=jqtri3&IP='

def isValidV4(ipAddr):
    addrList = ipAddr.strip().split('.')
    if len(addrList) != 4:
        return False
    for a in addrList:
        try:
            intA = int(a)
        except:
            print "Invalid character!"
            return False
        if intA <= 255 and intA >= 0:
            pass
        else:
            return False
    return True

def ipToLong(ip):
    if isValidV4(ip) == False:
        print 'Ip address is invalid!'
        return None
    else:
        retLong = 0L
        addrList = ip.strip().split('.')
        for i in range(0, len(addrList)):
            intA = int(addrList[i])
            retLong += intA * (256 ** (3 - i))
        return retLong

def longToIP(l):
    if l < 0:
        print 'Not valid ip long format!'
        return None
    retStr = ''
    for i in range(4):
        lp = l // (256**(3-i))
        l = l - lp * (256**(3-i))
        retStr += str(lp)
        if i < 3:
            retStr += '.'
    return retStr

def location(ipv4):
    if isValidV4(ipv4) == False:
        print 'Ip address is invalid!'
        return None
    try:
        r = requests.get(IP_LOCATION_URL + ipv4);
        content = r.content.decode('utf-8')
        pn = re.compile(ur'<span\s+id="status"[^>]*?>\s*<strong[^>]*?>[^:]+?:([\s\S]*?)</strong>')
        m = pn.search(content)
        locRaw = m.group(1).strip()
        loc = locRaw.split('==>>')
        for i in range(0, len(loc)):
            loc[i] = loc[i].strip()
    except:
        print "Get location failed, please try again later!"
    finally:
        del r
    return locRaw,loc

if __name__=="__main__":
    ### print "Please import the module to other file to use!"
    locRaw, loc = location('124.16.76.182')
    print 'In string:\t', locRaw
    print 'In list:\t', loc
