import simplejson, json
import re
import sys
import time
import csv
from ConfigParser import SafeConfigParser
import requests

class TokenExpiredException(Exception):
    pass

class EmailNotFoundException(Exception):
    pass

class NoAccessException(Exception):
    pass

class ShitException(Exception):
    pass

def loadToken():
    global oauth_token
    from browsermobproxy import Server
    server = Server("/home/dogostz/webapps/headreach/assets/ffx/browsermob/bin/browsermob-proxy")
    server.start()
    proxy = server.create_proxy()
    
    from selenium.webdriver.firefox.firefox_binary import FirefoxBinary
    from selenium import webdriver
    from selenium.common.exceptions import TimeoutException, NoSuchElementException, StaleElementReferenceException
    from selenium.webdriver.support.wait import WebDriverWait
    from selenium.webdriver.common.keys import Keys
    from selenium.webdriver.common.action_chains import ActionChains
    from selenium.webdriver.common.by import By
    from selenium.webdriver.support import expected_conditions
    
    log_file = open("/home/dogostz/webapps/headreach/assets/ffx/log", 'w')
    binary = FirefoxBinary(firefox_path='/home/dogostz/webapps/headreach/assets/ffx/firefox/firefox-bin', log_file=log_file)
    
    profile = webdriver.FirefoxProfile("/home/dogostz/webapps/headreach/assets//ffx/zqm20ux8.default")
    # Direct = 0, Manual = 1, PAC = 2, AUTODETECT = 4, SYSTEM = 5
    # profile.set_preference("network.proxy.type", 2)
    # profile.set_preference("network.proxy.http", "localhost")
    # profile.set_preference("network.proxy.http_port", proxy.port)
    # profile.set_preference("network.proxy.socks", "localhost")
    # profile.set_preference("network.proxy.socks_port", proxy.port)
    # profile.set_preference("network.proxy.ssl", "localhost")
    # profile.set_preference("network.proxy.ssl_port", proxy.port)
    # profile.set_preference("network.proxy.no_proxies_on", "")
    # test.currentTimeOffsetSeconds  11491200
    # profile.set_preference("browser.fixup.alternate.enabled", False);
    # profile.update_preferences()
    
    
    # profile.set_proxy(proxy.selenium_proxy())
    # profile.update_preferences()
    # time.sleep(2)
    
    for i in range(3):
        try:
            driver = webdriver.Firefox(firefox_binary=binary, firefox_profile=profile)
            break
        except:
            time.sleep(3)
    driver.implicitly_wait(10)
    
    ac = ActionChains(driver)
    # SHIFT+F2 opens dev toolbar
    ac.key_down(Keys.SHIFT).send_keys(Keys.F2).key_up(Keys.SHIFT).perform()
    # command to disable images
    ac = ActionChains(driver)
    ac.send_keys('pref set security.csp.enable false').perform()
    ac = ActionChains(driver)
    ac.send_keys(Keys.ENTER).perform()
    ac = ActionChains(driver)
    ac.send_keys('pref set network.proxy.type 2').perform()
    ac = ActionChains(driver)
    ac.send_keys(Keys.ENTER).perform()
    ac = ActionChains(driver)
    ac.send_keys('pref set network.proxy.http "localhost"').perform()
    ac = ActionChains(driver)
    ac.send_keys(Keys.ENTER).perform()
    ac = ActionChains(driver)
    ac.send_keys('pref set network.proxy.http_port ' + str(proxy.port)).perform()
    ac = ActionChains(driver)
    ac.send_keys(Keys.ENTER).perform()
    ac = ActionChains(driver)
    ac.send_keys('pref set network.proxy.ssl "localhost"').perform()
    ac = ActionChains(driver)
    ac.send_keys(Keys.ENTER).perform()
    ac = ActionChains(driver)
    ac.send_keys('pref set network.proxy.ssl_port ' + str(proxy.port)).perform()
    ac = ActionChains(driver)
    ac.send_keys(Keys.ENTER).perform()
    ac = ActionChains(driver)
    ac.send_keys('pref set network.proxy.no_proxies_on " "').perform()
    ac = ActionChains(driver)
    ac.send_keys(Keys.ENTER).perform()
    ac = ActionChains(driver)
    # disable dev toolbar
    #ac.key_down(Keys.SHIFT).send_keys(Keys.F2).key_up(Keys.SHIFT).perform()
    
    config = SafeConfigParser()
    config.read('../config.ini')
        
    login_email = config.get('main', 'linkedin_email')
    login_password = config.get('main', 'linkedin_password')
    
    # Check if LinkedIn is logged in
    driver.get('https://www.linkedin.com')
    source_code = driver.page_source
    if not ".linkedin.com/profile/view?id=" in source_code:
        for i in range(0, 3):
            try:
                        
                login_field = driver.find_element_by_id("login-email")
                password_field = driver.find_element_by_id("login-password")
                       
                login_field.send_keys(login_email)
                password_field.send_keys(login_password)
                login_field.send_keys(Keys.RETURN)
                break
            except StaleElementReferenceException as excp:
                        continue
            
    
    proxy.new_har("gmail", options={'captureHeaders': True})
    driver.get("http://gmail.com")
    wait = WebDriverWait(driver, 5)
    #print driver.page_source.encode("UTF-8")
    time.sleep(8)
    for i in range(0, 3):
        try:
            logged_in = False
            try:
                wait.until(expected_conditions.visibility_of_element_located((By.CSS_SELECTOR, ".T-I-KE")))
                logged_in = True
            except:
                logged_in = False
            if not logged_in:
                email_input_field = driver.find_element_by_css_selector("#Email")
                # email_input_field.get_attribute("placeholder")
                email_input_placeholder = email_input_field.get_attribute("placeholder")
                # if email_input_placeholder == "Enter your email":
                #     print "Login Screen Reached"
                email_input_field.send_keys("kondov.consult@gmail.com")
                next_button = driver.find_element_by_css_selector("#next")
                next_button.click()
                # NoSuchElementException
                passwd_input = driver.find_element_by_css_selector("#Passwd")
                passwd_input.send_keys("xsdxsdxQWE123")
                driver.find_element_by_css_selector("#signIn")
                signin_button = driver.find_element_by_css_selector("#signIn")
                signin_button.click()
                wait.until(expected_conditions.visibility_of_element_located((By.CSS_SELECTOR, ".T-I-KE")))
            compose_button = driver.find_element_by_css_selector(".T-I-KE")
            compose_button.click()
            time.sleep(2)
            #print driver.page_source.encode("UTF-8")
            # minimize_btn = driver.find_element_by_css_selector("#\:mu")
            try:
                minimize_btn = driver.find_element_by_css_selector("img[class='Hq aUG']")
            except:
                minimize_btn = driver.find_element_by_css_selector("img[class='Hq aUH']")
                minimize_btn.click()
                
            ac = ActionChains(driver)
            ac.key_down(Keys.SHIFT).send_keys(Keys.F2).key_up(Keys.SHIFT).perform()
            ac = ActionChains(driver)
            ac.send_keys('pref set network.proxy.type 1').perform()
            ac = ActionChains(driver)
            ac.send_keys(Keys.ENTER).perform()
            ac = ActionChains(driver)
            # disable dev toolbar
            ac = ActionChains(driver)
            ac.key_down(Keys.SHIFT).send_keys(Keys.F2).key_up(Keys.SHIFT).perform()
                
            # recipient_textarea = driver.find_element_by_css_selector("#\:pw")
            subj_el = driver.find_element_by_css_selector('input[name="subjectbox"]')
            parent = subj_el.find_element_by_xpath("..")
            try:
                recipient_div = parent.find_element_by_xpath("//div[@class='oL aDm']")
                recipient_div.click()
            except:
                pass
            #print driver.page_source.encode("UTF-8")
            recipient_textarea = driver.find_element_by_css_selector("textarea[name='to']")
            recipient_textarea.send_keys("martin.kondov@gmail.com ")
            # subject_textarea = driver.find_element_by_css_selector("#\:pb")
            # subject_textarea.send_keys("Subject line")
            # email_body_textarea = driver.find_element_by_css_selector("#\:ql")
            # email_body_textarea.send_keys("email body")
            # send_email_button = driver.find_element_by_css_selector("#\:p6")
            # send_email_button.click()
            time.sleep(4)
            api_req = None
            for i in proxy.har['log']['entries']:
                if "api.linkedin.com" in i['request']['url']:
                    api_req = i['request']
            oauth_token = ""
            for head in api_req['headers']:
                if "oauth_token" in head['name']:
                    oauth_token = head['value']
                    break
            #print oauth_token
            with open('./oauth_token.txt', 'w+') as f:
                f.seek(0)
                f.write(oauth_token)
                f.truncate()
            
            # driver.page_source.find(re.compile("Your message has been sent"))
            # \:ml
            # Hq aUG / H
            # rapportive-sidebar
            # .name
            # .positions
            # li =rp_positions.find_elements_by_css_selector("li")
            # li[2].find_elements_by_css_selector(".company")
            # driver.find_elements_by_css_selector("span[email='dda@dada.com']")
            # success_message = re.findall("Your message has been sent", driver.page_source)
            break
        except StaleElementReferenceException as excp:
            continue
        except NoSuchElementException as excp:
            continue
    
    
    server.stop()
    driver.quit()
    return oauth_token

def makeRequest(email_arg,headers):
    api_url = "https://api.linkedin.com/v1/people/email="+email_arg+":(first-name,last-name,headline,location,distance,positions,twitter-accounts,im-accounts,phone-numbers,member-url-resources,picture-urls::(original),site-standard-profile-request,public-profile-url,relation-to-viewer:(connections:(person:(first-name,last-name,headline,site-standard-profile-request,picture-urls::(original)))))"
    resp = requests.get(api_url, headers=headers)
    resp_obj = json.loads(resp.content)
    print resp.content
    try:
        if (resp_obj['status']==401) and ("expired" in resp_obj['message']):
            raise TokenExpiredException()
    except KeyError:
        pass
    try:
        if (resp_obj['status']==404):
            raise EmailNotFoundException()
    except KeyError:
        pass
    try:
        if (resp_obj['status']==401):
            raise ShitException()
    except KeyError:
        pass
    try:
        if (resp_obj['status']==403):
            raise NoAccessException()
    except KeyError:
        pass
    #403 = no access
    
    return resp_obj
    
    
def loadSavedToken():
    global oauth_token
    with open("./oauth_token.txt","r") as token_file:
        oauth_token = token_file.read()
    return oauth_token

#oauth_token: e1gdal5lTMNhURRmB3uYJPiehkvgL1kwGzpF
#YOBUs-Q0zL1Otg5czGKIA5qEUt69t2_w-34f
#cookies = {
#    'bcookie': 'v=2&772ce114-da8a-4738-8018-0e3869bbef4a',
#    'SID': 'e36fe62b-43af-4dfe-b04a-d7f8bb8dda41',
#    'VID': 'V_2016_08_17_16_945',
#    '_ga': 'GA1.2.1491474198.1461779373',
#    'liap': 'true',
#    '_lipt': '0_1xf5rPKuyZuoqqAsg_m9OcSBsgin2-IHwra6an4DTwr2r2BkPCBSLdNk1_swB2I8XFnWAm_qfPQhtqfqMFAS3qElevz2Nvso6VWg6XeKTQehH2LimtdEemvLDx3w_COuldMFEo8tpSvS17WYX2ID8i5WoifRCvJWode_PZAUSkzyrIKYOfxM9yvxkj9aNbnkULF03oMlezW6Lc7s8wE9BLxuNHCi5r8MsKmQxl5eVbsq45HGfEoBaDRrCS-X37cPUOJWcPvunVAS2fukl0YThUhZKboirKGxhKTg_ty_XJmmP4OoxDhL8eZBsz5acIdYANVhNdhQ8DZgBsvgcUVOpl4yyXpBJF83DCy3Ybd6L8vU0Gpvwk-oWP5UrFYP77JrbxfB0Eb1758trh0n-rBmQC',
#    'lang': 'v=2&lang=en-us',
#    'lidc': 'b=TB17:g=580:u=208:i=1471619589:t=1471657227:s=AQEromKQTWBtbZjMGau5zUrBQ99zBuV7',
#    'sdsc': '1%3A1SZM1shxDNbLt36wZwCgPgvN58iw%3D',
#}
headers = {
    'DNT': '1',
    'X-Cross-Domain-Origin': 'https://mail.google.com',
    'Accept-Encoding': 'gzip, deflate, sdch',
    'Accept-Language': 'en-US,en;q=0.8,de;q=0.6',
    'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/48.0.2564.82 Chrome/48.0.2564.82 Safari/537.36',
    'Content-type': 'application/json',
    'Accept': '*/*',
    'Referer': 'https://api.linkedin.com/uas/js/xdrpc.html?v=0.0.2000-RC8.57445-1429',
    'X-Requested-With': 'IN.XDCall',
    'x-li-format': 'json',
    'Connection': 'keep-alive',
    'oauth_token': 'e1gdal5lTMNhURRmB3uYJPiehkvgL1kwGzpF',
    'X-HTTP-Method-Override': 'GET',
}
headers={
    'x-li-format': 'json'}
#expired_json = '{  "errorCode": 0,  "message": "[unauthorized]. token expired 16899 seconds ago",  "requestId": "CXR27UYHL7",  "status": 401,  "timestamp": 1471638165869}'


#email_arg = sys.argv[1]
oauth_token = "CDwVhZ8CNNW_ezs3iIJZicYUmajyJLCGI4UG"
with open("/home/dogostz/1_1.csv","r") as csvfile:
    reader=csv.reader(csvfile)
    for row in reader:
        print row
        email_arg = row[0].split(":")[0]
        #oauth_token="e1gdal5lTMNhURRmB3uYJPiehkvgL1kwGzpF"
        try:
            #oauth_token = loadSavedToken()
            #oauth_token = "CDwVhZ8CNNW_ezs3iIJZicYUmajyJLCGI4UG"
            headers["oauth_token"] = oauth_token
            req = makeRequest(email_arg,headers)
        except TokenExpiredException:
            #pass
            #oauth_token = loadToken()
            headers["oauth_token"] = oauth_token
            req = makeRequest(email_arg,headers)
        except IOError:
            #oauth_token = loadToken()
            headers["oauth_token"] = oauth_token
            req = makeRequest(email_arg,headers)
        except NoAccessException:
            print "No Access To Profile"
        except EmailNotFoundException:
            print "Email not found"
        
        
        #print req

       


