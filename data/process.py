#encoding=utf-8
#########################################
##@author OshynSong                    ##
##@time   2014-12                      ##
#########################################

import sys
import os
import re
import json
import time
import urllib,urllib2
import StringIO
import gzip

import ip

BD_URI = 'http://api.map.baidu.com/location/ip?'
BD_AK = '90yBxQAHrZTc2tk4PvUEetOA'
BD_COOR = 'bd09ll'

GOOGLE_URI = 'http://ditu.google.cn/maps/api/geocode/json?address='

WHOLE_DATA_SET = []

REQUESTHeader = {
    'Accept':'application/json',
    #'Accept-Encoding':'gzip',##可能会压缩，需要处理
    'Accept-Language':'zh-CN,zh;q=0.8',
    'Cache-Control':'max-age=0',
    'Connection':'keep-alive',
    'Host':'ditu.google.cn',
    'Cookie':'NID=67=gGa_FK4huDCou5flsiIqrGne2uxPVZKIn31mjgt3BOIlcKCv92evgFzhp-mByy81Wx94TUlkPzgVv6SEMXIw-yevGktczmI5vbYulyyDLYeZhlh66EJOkiKbSrzMgyc7; PREF=ID=20c62f396220bcb3:U=7e0ebc20880c9d36:NW=1:TM=1399025401:LM=1415364271:S=7zXXBjdaEidV_jHv',
    'Referer':'http://www.google.com',
    'User-Agent':'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.104 Safari/537.36',
    'X-Client-Data':'CIe2yQEIpLbJAQiptskBCMG2yQEInobKAQjxiMoBCMOUygE=',
    }

def loadDataSet(fp):
    if os.path.exists(fp):
        try:
            fh = open(fp, 'r')
            for line in fh:
                terms = line.split('|')
                WHOLE_DATA_SET.append({terms[0]:terms[3:-1]})
        except Exception, msg:
            print 'An unexcepted error occur: ', msg
        finally:
            fh.close()
    else:
        print "The DNS lookup log file does not exists!"

def ipToPointByBaidu(ip):
    url = BD_URI + 'ak=' + BD_AK + '&ip=' + ip.strip() + '&coor=' + BD_COOR
    try:        
        req = urllib2.Request(url)
        response = urllib2.urlopen(req)
        con = response.read()
        rtnObj = json.loads(con)
    except Exception, msg:
        print msg
        return None
    return rtnObj

def ipToPointByGoogle(ipStr):
    try:
        locRaw,loc = ip.location(ipStr)
        url = GOOGLE_URI + urllib.quote(loc[2].encode('utf-8'))
        req = urllib2.Request(url)
        for h in REQUESTHeader:
            req.add_header(h, REQUESTHeader[h])
        res = urllib2.urlopen(req)
        con = res.read()
        #print con.decode('utf-8')
        rtnObj = json.loads(con)
        rtnObj['results'][0]['formatted_address'] = loc[2]
    except Exception, msg:
        print msg
        return None
    return rtnObj

def ipToPointFile(ipFile, ipPointFile):
    try:
        iph = open(ipFile, 'r')
        ipph = open(ipPointFile, 'a+')
        for i in iph:
            print 'process %s' % (i)
            ipph.write(i.strip() + ':{')
            
##            obj = ipToPointByGoogle(i)
##            if obj['status'] != 'OK':
##                ipph.write('}\n')
##                continue
##            r1 = obj['results'][0]
##            addr = r1['formatted_address'].encode('utf-8')
##            loc = r1['geometry']['location']
##            pointStr = '"addr":"%s","x":%f,"y":%f' % (addr, loc['lng'], loc['lat'])
##            ipph.write(pointStr)
##            ipph.write('}\n')
##            print r1['formatted_address']

            obj = ipToPointByBaidu(i)
            if obj['status'] != 0:
                ipph.write('}\n')
                continue
            addr = obj['address']
            point = obj['content']['point'];
            x = float(point['x']); y = float(point['y'])
            pointStr = '"addr":"%s","x":%f,"y":%f' % (addr, x, y)
            ipph.write(pointStr.encode('utf-8'))
            ipph.write('}\n')
            print obj['address']
            
            time.sleep(1)
    except Exception , msg:
        print msg
    finally:
        iph.close()
        ipph.close()
        print 'finished.'

def process():
    ipDict = {}
    iph = open('./CSession_01_ip_points', 'r')
    for i in iph:
        k = i[0:i.index(':')]
        v = i[i.index(':')+1:]
        v = eval(v.decode('utf-8'))
        #print k,v.decode('utf-8');break;
        if v.get('addr', False) == False:
            ipDict[k] = {"addr":"美国","x":-95.712891,"y":37.090240}
            continue
        ipDict[k] = v
    iph.close()
    #print len(ipDict);
    #print ipDict['125.39.136.74']['addr'].encode('utf-8').decode('utf-8');exit()
    try:
##        fh = open('CSession_01_points', 'a+')
##        dfh = open('CSession_01_csv', 'r')
##        for t in dfh:
##            arr = t.split('|')
##            fh.write(str(arr[0]));fh.write('#')
##            v = arr[1].split(',')
##            fh.write('{')
##            index = 0
##            for i in range(len(v)-1):
##                index += 1
##                print v[i]
##                if not ipDict.has_key(v[i]): continue
##                obj = ipDict[v[i]]
##                fh.write('"' + v[i] + '":')
##                s = '{"addr":"' + obj['addr'].encode('utf-8') + '"'
##                s += ',"x":' + str(obj['x'])
##                s += ',"y":' + str(obj['y'])
##                s += '}'
##                fh.write(s)
##                if index < len(v)-1:
##                    fh.write(',')
##            fh.write('}\n')
        fh = open('freq_01_raw', 'r')
        dfh = open('freq_01', 'a+')
        for l in fh:
            s = '{'
            ipArr = l.split('|')
            for i in range(len(ipArr)-1):
                if not ipDict.has_key(ipArr[i]): continue
                obj = ipDict[ipArr[i]]
                s += '"' + ipArr[i] + '":{"addr":"' + obj['addr'].encode('utf-8') + '"'
                s += ',"x":' + str(obj['x'])
                s += ',"y":' + str(obj['y']) + '}'
                if i < len(ipArr) - 2:
                    s += ','
            s += '}'
            dfh.write(s + '\n')
    except Exception , msg:
        print msg
    finally:
        fh.close()
        dfh.close()
    
if __name__ == "__main__":
    #loadDataSet('CSession_01')

    ##Test ipToPointByBaidu
    ##obj = ipToPointByBaidu('124.16.79.77');print obj

    ##Test ipToPointByGoogle
    ##obj = ipToPointByGoogle('124.16.79.77')
    ##print obj['results'][0]['formatted_address']
    ##print obj['results'][0]['geometry']['location']

    #ipToPointFile('CSession_01_ip', 'CSession_01_ip_points')
    
    ##print obj['address']
    ##print str(obj['content']['point'])
    process()
