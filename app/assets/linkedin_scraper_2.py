import mechanize
import Cookie
import cookielib
import requests
from bs4 import BeautifulSoup
from simplejson import JSONDecodeError
import sys
from __builtin__ import True
sys.path.append('/home/martin/git/headreach-main/assets/python2.7/site-packages')
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions
from selenium.common.exceptions import TimeoutException, NoSuchElementException, StaleElementReferenceException
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from ConfigParser import SafeConfigParser
import simplejson, json
import re
import sys, os
import logging

cookiejar = cookielib.LWPCookieJar()
logging.basicConfig(filename='linkedin_scraper.log', level=logging.DEBUG)
class UnauthenticatedRequest(Exception):
    pass
def extractResults(page_num,cookies):
    person_data = []
    r = makeJsRequest(page_num,cookiej=cookies)   
    if "Sign Up" in r.content:
        raise UnauthenticatedRequest()
    json = r.json()
    results = json['content']['page']['voltron_unified_search_json']['search']['results']
    for res in results:
            person = {}
            try:
                res = res['person']
            except:
                continue
            # if it's LinkedIn Member
            try:
                #person['photo'] = res['logo_result_base']['media_picture_link_100']
                person['photo'] = 'https://media.licdn.com/mpr/mpr' + res['imageUrl']
            except:
                try:
                    person['photo'] = res['logo_result_base']['genericGhostImage']
                except:
                    person['photo'] = ''
            person['company']=''
            try:
                try:
                    found_array = re.findall("<[^<]+?>.*<[^<]+?>",res['snippets'][0]['heading'])
                    person['company'] = re.sub('<[^<]+?>', '', found_array[0])
                except:
                    pass
                if person['company']=='':
                    try:
                        person['company'] = res['fmt_headline'].split('at')[1]
                    except:
                        pass
                if person['company']=='':
                    try:
                        person['company'] = res['fmt_headline'].split('for')[1]
                    except:
                        pass
                if person['company']=='':
                    try:
                        person['company'] = res['fmt_headline'].split('@')[1]
                    except:
                        pass
            except:
                person['company'] = ''
            try:
                person['industry'] = res['fmt_industry']
            except:
                person['industry'] = ''
            try:
                person['location'] = res['fmt_location']
            except:
                person['location'] = ''
            try:
                person['title'] = res['fmt_headline']
            except:
                person['title'] = ''
            try:
                person['first_name'] = res['firstName']
            except:
                person['first_name'] = ''
            try:
                person['last_name'] = res['lastName']
            except:
                person['last_name'] = ''
            try:
                person['profile_link'] = res['actions']['link_nprofile_view_9']
            except:
                person['profile_link'] = ''
            # try:
            #    person['json_response'] = r.json()
            # except:
            #    person['json_response'] = ''
            if res['isHeadless'] == True:
                continue
            #    person['first_name'], person['last_name'] = extractHeadlessMemberNames(res)
            person_data.append(person)
        
    return person_data   


def makeJsRequest(p_num,cookiej):
    js_url = "https://www.linkedin.com/vsearch/pj?company=" + company_name + "&page_num="+str(p_num)+"&openAdvancedForm=true&companyScope=C&locationType=Y"
    return requests.get(js_url, cookies=cookiej)

def loginWithPhantom():
    result = phantomLogin()
    if result == False:
            print "PhantomJS threw an exception"
            sys.exit()

def loadCookie():
    with open('li_cookie.txt', 'r') as f:
         li_at_cookie = f.read()
         li_at_cookie_expiry = li_at_cookie.split()[0]
         li_at_cookie_value = li_at_cookie.split()[1]
         
         ck = cookielib.Cookie(version=0,
                          name='li_at',
                          value=li_at_cookie_value,
                          domain='www.linkedin.com', domain_specified=True, domain_initial_dot=True, path='/',
                          expires=li_at_cookie_expiry, path_specified=True,
                          port=None, port_specified=False,
                          secure=True, discard=False, comment=None, comment_url=None, rest={'HttpOnly': False}, rfc2109=False)
         return ck

def extractHeadlessMemberNames(res):
    profile_url = res['actions']['link_nprofile_view_9']
    r = requests.get(profile_url, cookies=cookiejar)
    soup = BeautifulSoup(r.content, "lxml")
    title = soup.title.string
    firstName = title.strip("|")[0].strip(" ")[0]
    lastName = title.strip("|")[0].strip(" ")[0]
    return firstName, lastName

def phantomLogin():
    config = SafeConfigParser()
    config.read('/home/dogostz/webapps/headreach/config.ini')
    
    login_email = config.get('main', 'linkedin_email')
    login_password = config.get('main', 'linkedin_password')
    assets_path = config.get('main', 'assets_path')

    dcap = dict(DesiredCapabilities.PHANTOMJS)
    dcap["phantomjs.page.settings.userAgent"] = (
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/53 "
        "(KHTML, like Gecko) Chrome/15.0.87"
    )
    
    driver = webdriver.PhantomJS(assets_path + 'phantomjs')
    try:
        driver.implicitly_wait(3)  # seconds
        driver.get('https://www.linkedin.com')
        wait = WebDriverWait(driver, 3)
        
        for i in range(0, 3):
            try:
                
                login_field = driver.find_element_by_id("login-email")
                password_field = driver.find_element_by_id("login-password")
                
                # if Not
                # LOGIN PROCEDURE
                login_field.send_keys(login_email)
                password_field.send_keys(login_password)
                login_field.send_keys(Keys.RETURN)
                break
            except StaleElementReferenceException as excp:
                continue
            
        source_code = driver.page_source
        if not ".linkedin.com/profile/view?id=" in source_code:
            logging.debug("LinkedIn PhantomJS Login Fail")
            logging.debug(source_code)
            # wait.until(expected_conditions.visibility_of_element_located((By.CLASS_NAME, "photo")))
            # wait = WebDriverWait(driver, 5)
            driver.quit()
            return False
        
        li_at_cookie = driver.get_cookie("li_at")
        with open('li_cookie.txt', 'w') as f:
            f.seek(0)
            cookie_string = str(li_at_cookie['expiry']) + " " + li_at_cookie['value']
            f.write(cookie_string)
            f.truncate()
    
        driver.quit()
        return True
    except:
        driver.quit()
        return False

company_arg = sys.argv[1]
company_name = company_arg

br = mechanize.Browser()
cookiejar = cookielib.LWPCookieJar()


if not os.path.isfile("li_cookie.txt"):
    loginWithPhantom()

ck = loadCookie()
cookiejar.set_cookie(ck)


#br.set_cookiejar(cookiejar)

# cookiejar = cookielib.MozillaCookieJar('/home/martin/cookies4.txt')
# cookiejar.load('/home/martin/cookies.txt', ignore_discard=True, ignore_expires=True)
# cookiejar.load(ignore_discard=True, ignore_expires=True)

# Browser options
br.set_handle_equiv(True)
# br.set_handle_gzip(True) Produces UserWarning
br.set_handle_redirect(True)
br.set_handle_referer(True)
br.set_handle_robots(False)

# Follows refresh 0 but not hangs on refresh > 0
br.set_handle_refresh(mechanize._http.HTTPRefreshProcessor(), max_time=1)

# Want debugging messages?
# br.set_debug_http(True)
# br.set_debug_redirects(True)
# br.set_debug_responses(True)

# User-Agent (this is cheating, ok?)
# br.addheaders = [('User-agent', 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008071615 Fedora/3.0.1-1.fc9 Firefox/3.0.1')]
br.addheaders = [('User-agent', 'Mozilla/5.0 (X11; Linux) AppleWebKit/538.15 (KHTML, like Gecko) Chrome/18.0.1025.133 Safari/538.15 Midori/0.5')]
# br.set_all_readonly(False)    # allow everything to be written to
# br.set_handle_robots(False)   # ignore robots
# br.set_handle_refresh(False)  # can sometimes hang without this
# br.addheaders = "Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0"

# response = br.open('https://www.linkedin.com/nhome/')


# form_list = list(br.forms())
# form = form_list[0]
# form.find_control(id="login-email")
# form.set_value("manolivanov@abv.bg",id="login-email")
# form.find_control(id="login-password")
# form.set_value("manolepederas",id="login-password")
# br.select_form(nr = 0)
# br.submit()
# req = form.click()
# response = br.open(req)
# for f in list(br.forms()):
#    if f.attrs['action']=='/uas/ato-pin-challenge-submit':
#        print "LinkedIn detected suspicious login attempt, requests code"
#        sys.exit()
#    elif f.attrs['action']=='/uas/captcha-v2-submit':
#        print "LinkedIn detected suspicious login attempt, requests captcha"
#        sys.exit()
# print response.read()

results = []
p=1
try:
    max_pages = int(sys.argv[3])
except:
    max_pages = 4

while p < min(max_pages,4):
    try:
        result = extractResults(page_num=p,cookies=cookiejar)
        results.append(result)
        p=p+1
    except UnauthenticatedRequest:
        if p==1:
            loginWithPhantom()
            ck = loadCookie()
            cookiejar.set_cookie(ck)
            continue
        else:
           print "Could not authenticate and set Cookie"
           sys.exit()
        continue
    except JSONDecodeError:
        print "JavaScript request failed, returned NonJson response"
        continue

combined_results=[]
for r in range(0,len(results)):
    for i in range(0,len(results[r])):
        combined_results.append(results[r][i])

        
return_array = {
                "results" : combined_results}       
print simplejson.dumps(return_array)
# print response.read()      # the text of the page

# br.select_form("form1")         # works when form has a name
# br.form = list(br.forms())[0]  # use when form is unnamed

# response = br.submit()
# print response.read()
# br.back()   # go back
