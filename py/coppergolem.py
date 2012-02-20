#!/usr/bin/env python3
import http.client, urllib.parse
from golem import Golem
from http import cookies
from re import findall
from functools import reduce
#import gzip, zlib

class CopperGolem (Golem):
  def __init__(self):
    self.logininfo = []
    self.baseheaders = {"User-Agent":"Mozilla/5.0 (Windows; en-US; XP) Gecko/20101028 Firefox/3.5.15", "Accept": "*/*", "Accept-Language":"en-us,en;q=0.5",
                        "Accept-Encodingg": "gzip,deflate", "Accept-Charset": "ISO-8859-1,utf-8;q=0.7,*;q=0.7"}
    self.cookies = cookies.SimpleCookie()
    self.referer = ''
        
  def _act(self, url, method, params={}):
    headers = self.baseheaders.copy()
    #headers['Referer'] = self.referer
    headers['Cookie'] = self.cookies.output(header='', sep=';')
    print("sending headers: " + str(headers))
    self.referer = url
    if(url[:7] == 'http://'):
      url = url[7:]
      safe = False
    elif (url[:8] == 'https://'):
      url = url[8:]
      safe = True
    else:
      self.error("I don't like the protocol, I don't know how to handle this.\n" + url)
    (baseurl, extendedurl) = url.split('/', 1)
    extendedurl = '/' + extendedurl
    params = urllib.parse.urlencode(params)
    conn = http.client.HTTPConnection(baseurl) if not safe else http.client.HTTPSConnection(baseurl)
    conn.request(method, extendedurl, params, headers)
    print(params)
    resp = conn.getresponse()
    return self._processResponse(resp)

  def _processResponse(self, resp):
    data = resp.read()
    headers = resp.getheaders()
    print ("received headers " + str(headers)) 
    #if headers['Content-Encoding'] in ('gzip', 'x-gzip'):
      #data = gzip.decompress(data)
    #elif headers['Content-Encoding'] == 'deflate':
      #data = zlib.decompress(data)
    data = bytes.decode(data)
    for (k, v) in headers:
      if k == 'Set-Cookie':
        self.cookies.load(v)
    return (headers, data)

  def post(self, url, params):
    realparams = urllib.parse.urlencode(params)
    return self._act(url, 'POST', params)

  def get(self, url):
    return self._act(url, 'GET')

  #def find(self, pattern, stringList):
  #  return reduce(lambda x, y: x + findall(pattern, y), stringList, [])
    

 
