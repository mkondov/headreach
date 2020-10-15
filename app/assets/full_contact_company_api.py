import sys
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
config.read('/home/dogostz/webapps/headreach/app/config.ini')

apiKeyFullContact = config.get('main', 'full_contact_api')

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36',
}

domain_arg = sys.argv[1]

response = requests.get("https://api.fullcontact.com/v2/company/lookup.json?domain="+domain_arg+"&apiKey="+apiKeyFullContact)
data = json.loads(response.content)
#print response.content
social_profiles = []
try:
    for socProfile in data['socialProfiles']:
        social_profiles.append(socProfile['url'])
except:
    social_profiles = []
email_addresses = []
try:
    for email_address in data['organization']['contactInfo']['emailAddresses']:
        email_addresses.append(email_address['value'])
except:
    email_addresses = []
tel_numbers = []
try:
    for tel_number in data['organization']['contactInfo']['phoneNumbers']:
        tel_numbers.append(tel_number['number'])
except:
    tel_numbers = []
try:
    name = data['organization']['name']
except:
    name=""
try:
    website = data['website']
except:
    website=""
    
company_data = []
company_data.append({
                    "social_profiles":social_profiles,
                    "name":name,
                    "website":website,
                    "email_addresses": email_addresses,
                    "tel_numbers":tel_numbers,
                    "json_response":response.content})
print json.dumps(company_data)