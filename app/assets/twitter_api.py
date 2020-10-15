#!/usr/bin/python

import sys
import requests
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
import oauth2
import simplejson,json

requests.packages.urllib3.disable_warnings(InsecureRequestWarning)
requests.packages.urllib3.disable_warnings(InsecurePlatformWarning)
requests.packages.urllib3.disable_warnings(SNIMissingWarning)

config = SafeConfigParser()
config.read('/home/dogostz/webapps/headreach/config.ini')

ACCESS_KEY=config.get('main', 'twitter_access_key')
ACCESS_SECRET=config.get('main', 'twitter_secret')
CONSUMER_KEY=config.get('main', 'twitter_consumer_key')
CONSUMER_SECRET=config.get('main', 'twitter_consumer_secret')


def oauth_req(url, http_method="GET", post_body="", http_headers=None):
    consumer = oauth2.Consumer(key=CONSUMER_KEY, secret=CONSUMER_SECRET)
    token = oauth2.Token(key=ACCESS_KEY, secret=ACCESS_SECRET)
    client = oauth2.Client(consumer, token)
    resp, content = client.request( url, method=http_method, body=post_body, headers=http_headers )
    return content

def extractData(json_data):
    data = json.loads(json_data)
    person_data=[]
    screen_name = ""
    id=""
    location=""
    first_name=""
    last_name=""
    profile_image_url=""
    time_zone=""
    try:
        for status in data['statuses']:
            try:
                screen_name = status['user']['screen_name']
            except NameError:
                screen_name = ""
            try:
                id = status['user']['id_str']
            except NameError:
                id = ""
            try:
                bio = status['user']['description']
            except NameError:
                bio = ""
            try:
                location = status['user']['location']
            except NameError:
                location = ""
            try:
                first_name = status['user']['name'].split()[0]
            except NameError:
                first_name = ""
            try:
                last_name = status['user']['name'].split()[1]
            except NameError:
                last_name = ""
            try:
                profile_image_url = status['user']['profile_image_url']
            except NameError:
                profile_image_url = ""
            try:
                time_zone = status['user']['time_zone']
            except NameError:
                time_zone = ""
            
            person_data.append({"first_name":first_name,
                        "last_name":last_name,
                        "bio": bio,
                        "photo": profile_image_url,
                        "time_zone":time_zone,
                        "location":location,
                        "twitter_id":id,
                        "twitter_handle":screen_name,
                        "json_response": json_data})
    except:
        v=""
    
    return person_data
 
person_data = []

keyword = sys.argv[1]
data_json = oauth_req( 'https://api.twitter.com/1.1/search/tweets.json?q=%20%23'+keyword+'&result_type=popular&count=100' )
person_data.append(extractData(data_json))

data = json.loads(data_json)
next_data=data

#max_id=person_data[0][0]['twitter_id']
while True:
    #if next_data['search_metadata']['next_results']:
    try:
        next_data = oauth_req( 'https://api.twitter.com/1.1/search/tweets.json'+data['search_metadata']['next_results'] )
        person_data.append(extractData(next_data))
        next_data = json.loads(next_data)
        #max_id=person_data[0][0]['twitter_id']
    except KeyError:
        break
    
#next_data = oauth_req( 'https://api.twitter.com/1.1/search/tweets.json?q=%20%23'+keyword+'&result_type=popular&count=100&max_id='+max_id )
#person_data.append(extractData(next_data))
#next_data = json.loads(next_data)

print json.dumps(person_data)
