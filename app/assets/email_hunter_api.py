import sys
import tldextract
import requests
import json
import urllib2
from bs4 import BeautifulSoup
import csv
import os
import datetime
import urlparse
from requests.packages.urllib3.exceptions import InsecureRequestWarning
from requests.packages.urllib3.exceptions import InsecurePlatformWarning
from requests.packages.urllib3.exceptions import SNIMissingWarning
from ConfigParser import SafeConfigParser

requests.packages.urllib3.disable_warnings(InsecureRequestWarning)
requests.packages.urllib3.disable_warnings(InsecurePlatformWarning)
requests.packages.urllib3.disable_warnings(SNIMissingWarning)

config = SafeConfigParser()
config.read('/home/dogostz/webapps/headreach/config.ini')

apiKeyEmailHunter = config.get('main', 'email_hunter_api')

domain_arg = sys.argv[1]
ext_domain = tldextract.extract(domain_arg)
domain_arg = ext_domain.domain+"."+ext_domain.suffix

response = requests.get("https://api.emailhunter.co/v1/search?domain="+domain_arg+"&api_key="+apiKeyEmailHunter,verify=False)
#print response.content
data = json.loads(response.content)
counter = 0
person_data = []
names = []
social_profiles = []
emails= []
try:
    for email in data['emails']:
        if email['type']=='personal':
            emails.append(email['value'])
except:
    emails= []
print json.dumps(emails)