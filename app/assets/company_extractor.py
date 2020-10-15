import requests
import re
import sys
from BeautifulSoup import BeautifulSoup
from fuzzywuzzy import fuzz

#url='http://wooguru.net'
url_arg = sys.argv[1]
url = url_arg
r = requests.get(url)
html = r.text
soup = BeautifulSoup(html)
title = soup.find("title").text
title = re.sub(r'[^\w\s]','',title)
title = title.strip()
#found_array = soup.findAll(text=re.compile(u"\u00A9"))
#nodes_copyright=found_array[2].split()
#nodes_title= title.split()
#u"\u00A9"
title = soup.find("title").text
if len(title.split())==1:
    print title
    exit()
title = re.sub(r"[\s{}\.\!\?\:]+"," ",title)
cr_symbols = [[u"\&\c\o\p\y\;",u"&copy;"],[u"\u00A9",u"\u00A9"],[u"\(\c\)",u"(c)"],[u"\(\C\)",u"(C)"]]
possible_company_names = []
for symbol in cr_symbols:
    copyright_elements = soup.findAll(text=re.compile(symbol[0]))
    #copyright_elements  = re.sub(r"[\s{}\.\!\?]+"," ",' '.join(copyright_elements))
    if len(copyright_elements)<1:
        continue
    for cr_el in copyright_elements:
        cr_el =  re.sub(r"[\s{}\.\!\?\:]+"," ",cr_el)
        copy_array = cr_el.split(symbol[1])
        copy_words = []
        left_words = []
        right_words = []
        #for ce in copy_array[0]:
        #    left_words+=ce.split()
        #for ce in copy_array[1]:
        #    right_words+=ce.split()
        left_words+=copy_array[0].split()
        right_words+=copy_array[1].split()
        title_array = title.split()
        for t_e in title_array:
            for cr_e in left_words:
                if fuzz.ratio(cr_e.lower(), t_e.lower()) > 80:
                    #print t_e, "left"
                    possible_company_names.append([t_e,0])
        for t_e in title_array:
            for cr_e in right_words:
                if fuzz.ratio(cr_e.lower(), t_e.lower()) > 80:
                    #print t_e, "right"
                    possible_company_names.append([t_e,1])

if len(possible_company_names)==1:
    print possible_company_names[0][0]
else:
    right_names = []
    for pcn in possible_company_names:
        if pcn[1]==1:
            right_names.append(pcn[0])
    i=0
    end = len(right_names)-1
    while i<end:
        if right_names[i]==right_names[i+1]:
            del right_names[i]
            end-=1
        else:
            i+=1
    print ' '.join(right_names)
