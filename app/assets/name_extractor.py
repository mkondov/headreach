import sys
import nltk
import requests
import simplejson

#url = "http://www.salesgravy.com/sales-articles/featured-articles/authors.html"
url_arg = sys.argv[1]
url = url_arg
text = requests.get(url)
text = text.content
def extract_entities(text):
     for sent in nltk.sent_tokenize(text.decode('utf-8')):
        for chunk in nltk.ne_chunk(nltk.pos_tag(nltk.word_tokenize(sent))):
            #if hasattr(chunk, 'node'):
            try:
                print chunk.label(), ' '.join(c[0] for c in chunk.leaves())
            except:
                continue
#extract_entities(text)
def get_human_names(text):
    tokens = nltk.tokenize.word_tokenize(text)
    pos = nltk.pos_tag(tokens)
    sentt = nltk.ne_chunk(pos, binary = False)
    person_list = []
    person = []
    name = ""
    for subtree in sentt.subtrees(filter=lambda t: t.label() == 'PERSON'):
        for leaf in subtree.leaves():
            person.append(leaf[0])
        if len(person) > 1:
            for part in person:
                name += part + ' '
            if name[:-1] not in person_list:
                person_list.append(name[:-1])
            name = ''
        person = []

    return (person_list)
name_results =  get_human_names(text.decode('utf-8'))

return_array = {
                "results" : name_results}       
print simplejson.dumps(return_array)
