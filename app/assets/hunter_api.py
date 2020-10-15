import sys
import requests
import json
import urllib2
from bs4 import BeautifulSoup
import csv
import os
import datetime
import urlparse

apiKeyFullContact = "3237bd1c9127e124"
apiKeyEmailHunter = "7c03901d9fe9ca20d6ffe155fad8200aca183987"

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36',
}

def custom_urlparse(url):
    if "http" not in url:
        return url
    if "www" in urlparse.urlparse(url)[1]:
        return urlparse.urlparse(url)[1][4:]
    else:
        return urlparse.urlparse(url)[1]

#opener = urllib2.build_opener()
#opener.addheaders = [('User-agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36')]

response = requests.get("http://cozy.bg/kalo/test.php",headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36',
})
article_html = response.content

soup = BeautifulSoup(article_html,"html.parser")
#article = soup.find_all("div",class_="entry-content")

links= soup.find_all("a")
for link in links:
    print "NEW LINK !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    #print link.attrs["href"]
    #new_link = opener.open(link.attrs["href"])
    new_link = link.attrs['href']
    domain = custom_urlparse(new_link)
    #print domain
    #print "https://api.fullcontact.com/v2/company/lookup.json?domain="+domain+"&apiKey="+apiKeyFullContact
    responseFC = requests.get("https://api.fullcontact.com/v2/company/lookup.json?domain="+domain+"&apiKey="+apiKeyFullContact)
    #print responseFC
    data = json.loads(responseFC.content)
    print "COMPANY SOCIAL PROFILES !"
    for socProfile in data['socialProfiles']:
            print socProfile['url']
    #exit()
    responseEH = requests.get("https://api.emailhunter.co/v1/search?domain="+domain+"&api_key="+apiKeyEmailHunter)
    eh_data = json.loads(responseEH.content)
    print "WEBSITE EMAILS !"
    counter = 0
    for email in eh_data['emails']:
        if email['type']=='personal':
            counter=counter+1
            if counter==4:
                break
            print email['value']
            email['value']="bart@fullcontact.com"
            responseFCP = requests.get("https://api.fullcontact.com/v2/person.json?email="+email['value']+"&apiKey="+apiKeyFullContact)
            fcp_data = json.loads(responseFCP.content)
            print "PERSON DATA !"
            try:
                print fcp_data['contactInfo']['fullName']
            except:
                print "no contactInfo found"
            try:
                for sProfile in fcp_data['socialProfiles']:
                    print sProfile['url']
            except:
                print "no socialProfiles found"
        
    #https://api.fullcontact.com/v2/company/lookup.json?domain=mailchimp.com&apiKey=3237bd1c9127e124
    #https://api.emailhunter.co/v1/search?domain=stripe.com&api_key=7c03901d9fe9ca20d6ffe155fad8200aca183987
    #https://api.fullcontact.com/v2/person.json?email=support@optimizepress.com&apiKey=3237bd1c9127e124
    