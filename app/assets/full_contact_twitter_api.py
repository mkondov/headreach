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
config.read('/home/dogostz/webapps/headreach/config.ini')

apiKeyFullContact = config.get('main', 'full_contact_api')

twitter_arg = sys.argv[1]

response = requests.get("https://api.fullcontact.com/v2/person.json?twitter="+twitter_arg+"&apiKey="+apiKeyFullContact)
data = json.loads(response.content)
try:
    for photo in data['photos']:
        try:
            photo_url = photo['url']
            break
        except NameError:
            photo_url = ""
except:
    photo_url=""
try:
    bios=[]
    for d in data['socialProfiles']:
        if 'bio' in d:
            bios.append(d['bio'])
    bios = ".".join(bios)
except:
    v = "v"

try:
     name = data['contactInfo']['fullName']
except:
     name = ""
try:
     title = data['organizations'][0]['title']
except:
     title = ""
try:
     company = data['organizations'][0]['name']
except:
     company = ""
try:
     contact_infos=[]
     chats = data['contactInfo']['chats']
     for chat in chats:
         contact_infos.append(chat['client']+" : "+chat['handle'])
except:
     contact_infos=[]
try:
     social_profiles=[]
     for sProfile in data['socialProfiles']:
         social_profiles.append(sProfile['url'])
except:
     social_profiles=[]

if len(name.split())>1:
    first_name = name.split()[0]
    last_name = name.split()[1]
else:
    first_name = name
    last_name = ""

person_data = []
person_data.append({
                    "first_name":first_name,
                    "last_name":last_name,
                    "bio": bios,
                    "photo": photo_url,
                    "title": title,
                    "company":company,
                    "contact_info":contact_infos,
                    "social":social_profiles,
                    "json_response": response.content})
print json.dumps(person_data)