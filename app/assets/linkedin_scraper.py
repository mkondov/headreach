import sys
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
import sys
import logging

logging.basicConfig(filename='linkedin_scraper.log', level=logging.DEBUG)

def isImportant(title):
    if "blog" in title:
        return (True, 1)
    elif "content" in title:
        return (True, 2)
    elif "writer" in title:
        return (True, 3)
    elif "author" in title:
        return (True, 4)
    elif "marketing" in title:
        return (True, 5)
    elif "manager" in title:
        return (True, 6)
    elif "executive" in title:
        return (True, 7)
    elif "founder" in title:
        return (True, 8)
    elif "ceo" in title:
        return (True, 9)
    else:
        return (False, 0)
    

config = SafeConfigParser()
config.read('/home/dogostz/webapps/headreach/config.ini')


company_arg = sys.argv[1]

company_name = company_arg
login_email = config.get('main', 'linkedin_email')
login_password = config.get('main', 'linkedin_password')
assets_path = config.get('main', 'assets_path')


dcap = dict(DesiredCapabilities.PHANTOMJS)
dcap["phantomjs.page.settings.userAgent"] = (
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/53 "
    "(KHTML, like Gecko) Chrome/15.0.87"
)
#print assets_path+'phantomjs'
#driver = webdriver.PhantomJS(executable_path=r''+assets_path+'phantomjs', desired_capabilities=dcap)
driver = webdriver.PhantomJS(assets_path+'phantomjs')
#print assets_path+'phantomjs'
#driver = webdriver.PhantomJS(desired_capabilities=dcap)
#driver = webdriver.Firefox()
# driver.set_preference("http.response.timeout", 10)
# driver.set_preference("dom.max_script_run_time", 10)
driver.implicitly_wait(10)  # seconds
driver.get('https://www.linkedin.com')
wait = WebDriverWait(driver, 3)

for i in range(0,3):
    try:
        
        login_field = driver.find_element_by_id("login-email")
        password_field = driver.find_element_by_id("login-password")
        
        logging.debug('logging in')
        
        # if Not
        # LOGIN PROCEDURE
        login_field.send_keys(login_email)
        password_field.send_keys(login_password)
        login_field.send_keys(Keys.RETURN)
        break
    except StaleElementReferenceException as excp:
        continue

wait.until(expected_conditions.visibility_of_element_located((By.CLASS_NAME, "photo")))
# wait = WebDriverWait(driver, 5)
source = driver.page_source
logging.debug(source)

logging.debug('logged in')
"""
driver.get("https://www.linkedin.com/vsearch/f?trk=federated_advs&adv=true")
# advanced_search_menu_link = driver.find_element_by_id("advs-link")
# advanced_search_menu_link.click()
#wait = WebDriverWait(driver, 2)

source = driver.page_source
print source

def adv_menu_is_active(driver):
    try:
        adv_search_menu = driver.find_element_by_id("advs")
        if adv_search_menu.get_attribute("class")=="active":
            return True
        else:
            return False
    except:
        return False

wait.until(adv_menu_is_active)


for i in range(0,3):
    try:
        company_search_field = driver.find_element_by_id("advs-company")
        # title_search_field = driver.find_element_by_id("advs-title")
        # title_search_field.send_keys("Founder")
        
        #keywords_search_field = driver.find_element_by_id("advs-keywords")
        #keywords_search_field.send_keys("content,blog")
        company_search_field.send_keys(company_name)
        company_search_field.send_keys(Keys.RETURN)
        break
    except StaleElementReferenceException as excp:
        continue

"""
logging.debug('searching')

driver.get("https://www.linkedin.com/vsearch/p?company="+company_name)

# "Executive, Manager, CEO, President"
wait.until(expected_conditions.visibility_of_element_located((By.ID, "results-container")))
# wait = WebDriverWait(driver, 5)

logging.debug('searched')


person_data = []


for i in range(0,3):
    try:
            li_results = driver.find_element_by_id("results").find_elements_by_class_name("people")
            #try_text = li_results[0].find_element_by_class_name("description").text
            
            for r in range(0,len(li_results[0:10])):
                person={}
                person['photo']= li_results[r].find_element_by_class_name("entity-img").get_attribute("src")
                try:
                    person['company'] = li_results[r].find_element_by_class_name("description").text.split("at")[1].strip()
                    person['title'] = li_results[r].find_element_by_class_name("description").text.split("at")[0].strip()
                except:
                    person['title'] = li_results[r].find_element_by_class_name("description").text.split("at")[0].strip()
                    person['company'] = ""
                person['first_name'] = li_results[r].find_element_by_class_name("title").text.split()[0]
                person['last_name'] = li_results[r].find_element_by_class_name("title").text.split()[1]
                person['profile_link'] = li_results[r].find_elements_by_tag_name("a")[0].get_attribute("href")
                person['json_response'] = json.dumps(person)
                person_data.append(person)
                
            
            #most_important_level = 0
            #most_important_element = li_results[0]
            #for li_result in li_results:
            #        title = li_result.find_element_by_class_name("description").text
            #        (important, how) = isImportant(title)
            #        if important > most_important_level:
            #            most_important_element = li_result
            #            most_important_level = important
                
            # try:
            #    first_result = driver.find_element_by_class_name("idx0")
            #    first_result_link = first_result.find_elements_by_tag_name("a")[0].get_attribute("href")
            # except StaleElementReferenceException as excp:
            #    wait = WebDriverWait(driver, 1)
            #    first_result = driver.find_element_by_class_name("idx0")
            #    wait = WebDriverWait(driver, 1)
            #    first_result_link = first_result.find_elements_by_tag_name("a")[0].get_attribute("href")
            #mielement_link = most_important_element.find_elements_by_tag_name("a")[0].get_attribute("href")
            break
    except StaleElementReferenceException as excp:
        continue
    except NoSuchElementException as excp:
        return_array = {
                results: []
                }
        print simplejson.dumps(return_array)
        driver.quit()
        exit()
"""
for r in range(0,len(person_data)):
    
    driver.get(person_data[r]['profile_link'])

    for i in range(0,3):
        try:
            bckg_experience_container = driver.find_element_by_id("background-experience-container")
            bckg_experience = driver.find_element_by_id("background-experience")
            full_name = driver.find_element_by_class_name("full-name")
            title = driver.find_element_by_class_name("title")
            # experience-729598611-view > header > h5 > span
            inner_bckg_divs = bckg_experience.find_elements_by_tag_name("div")
            
            #person = []
            #person['company_name'] = inner_bckg_divs[0].find_element_by_css_selector("header > h5 > span > strong").text
            #page_text = driver.find_element_by_tag_name("body").text
            page_text = driver.page_source
            person_data[r]['email'] = re.findall(r'[\w\.-]+@[\w\.-]+', page_text)
            break
        except StaleElementReferenceException as excp:
            continue
"""
#logging.debug('got person data')
#logging.debug(full_name.text)
#logging.debug(title.text)
logging.debug(person_data)

#first_name = full_name.text.split()[0]
#last_name = full_name.text.split()[1]


return_array = {
                "results" : person_data}


# ret_json=simplejson.loads([full_name.text,title.text,email_match])
print simplejson.dumps(return_array)
# print full_name.text
# print title.text
# print email_match

driver.quit()
