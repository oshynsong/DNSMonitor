###########################################################
### @author OshynSong                                   ###
### @time   2014-12                                     ###
### @desc   Use Aprior algorithm to mine association    ###
###         rules, where data comes from DNS lookup log ###
###########################################################

import os
import sys
reload(sys)
sys.setdefaultencoding('utf8')

import ip

WHOLE_DATA_SET = []
L = [None]


def loadDataSet(fp):
    if os.path.exists(fp):
        try:
            dnsDataSet = []
            fh = open(fp, 'r')
            for line in fh:
                terms = line.split('|')
                dns = terms[3:-1]
                for i in range(0, len(dns)):
                    dns[i] = ip.ipToLong(dns[i].strip())
                dnsDataSet.append(dns)
                WHOLE_DATA_SET.append({terms[0]:terms[3:-1]})
        except Exception, msg:
            print 'An unexcepted error occur: ', msg
        finally:
            fh.close()
        return dnsDataSet
    else:
        print "The DNS lookup log file does not exists!"
        return None

def findC1(dataSet):
    ''' Find the candicate 1 set '''
    C1 = []
    for trans in dataSet:
        for item in trans:
            if not [item] in C1:
                C1.append([item])
    C1.sort()
    return map(frozenset, C1)

def findLk(dataSet, Ck, minSup):
    '''Generate the Lk set from the Ck, search the whole dataset'''
    supCnt = {}
    for trans in dataSet:
        for can in Ck:
            if can.issubset(trans):
                if not supCnt.has_key(can):
                    supCnt[can] = 1
                else:
                    supCnt[can] += 1
    totalNum = float(len(dataSet))
    retList = []
    supportData = {}
    for key in supCnt:
        support = supCnt[key] / totalNum
        if support >= minSup:
            retList.insert(0, key)
        supportData[key] = support
    return retList, supportData

def apriorGen(Lk, k):
    ''' Generate C[k] from L[k-1] using the connection step'''
    Ck = []
    lenLk = len(Lk)
    for i in range(0, lenLk - 1):
        for j in range(i + 1, lenLk):
            L1 = list(Lk[i])[:k-2]
            L2 = list(Lk[j])[:k-2]
            L1.sort()
            L2.sort()
            if L1 == L2:
                c = Lk[i] | Lk[j]
                if hasInfrequentSubset(c) == True:
                    del c
                else:
                    Ck.append(c)
    return Ck

def hasInfrequentSubset(c):
    k = len(c)
    Lk = L[k-1];
    ret = False
    listLk = []
    for a in Lk:
        listLk.append(list(a))
    listC = list(c)
    
    for i in range(0, k):
        tmp = listC[:i] + listC[i+1:]
        if tmp in listLk:
            pass
        else:
            ret = True
            break
    return ret
            
def aprior (dataSet, minSup = 0.5):
    C1 = findC1(dataSet)
    D = map(set, dataSet)
    L1, supportData = findLk(D, C1, minSup)
    L.append(L1)
    k = 2
    while (len(L[k-1]) > 0):
        Ck = apriorGen(L[k-1], k)
        Lk, supK = findLk(D, Ck, minSup)
        supportData.update(supK)
        L.append(Lk)
        k += 1
    return supportData

def genStrongRules(freqItemsets):
    if len(freqItemsets) < 1:
        return None
    ###for fi in freqItemsets:
        

if __name__ == "__main__":
    ### print "Please import the module to other file to use!"
    ## locRaw, loc = location('124.16.76.182')
    ##print ip.ipToLong('159.226.12.1')
    ##print ip.longToIP(2682391553L)
    ##exit()

    ds = loadDataSet('CSession_01')
##    print len(ds)
##    C1 = findC1(ds)
##    print len(C1);print C1[0][0]
##    fh = open('CSession_01_ip', 'a+')
##    for i in range(len(C1)):
##        fh.write(str(C1[i][0]))
##        fh.write('\n')
##    fh.close()

        
##    print len(ds)
##    sup = aprior(ds, 0.001)
##    print L
    
##    ds = [[1,2,3,5], [1,3,5,7], [3,4,9], [2,4], [2,3,4,5]]
##    print ds
##    C1 = findC1(ds)
##    L1, sup = findLk(ds, C1, 0.6); L.append(L1)
##    C2 = apriorGen(L1, 2)
##    L2, sup = findLk(ds, C2, 0.6);
    sup = aprior(ds, 0.001)
##    fh = open('freq_01', 'a+')
    for i in range(1, len(L) - 1):
        lk = list(L[i])
##        for item in lk:
##        fh.write(str(list(L[i])))
        print lk
    #fh.close()
