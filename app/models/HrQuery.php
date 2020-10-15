<?php

namespace app\models;

use Yii;

use TermExtractor\TermExtractor;
use TermExtractor\Filters\DefaultFilter;
use TermExtractor\Tagger;
use PHPHtmlParser\Dom;

// Example
// $hr_query = new HrQuery ();
// $hr_query->date_added = new \DateTime ();
// $hr_query->url = $this->post_param;
// $hr_query->save ();
// $hr_query->processPage ();

// $keywords = $hr_query->results;

/**
 * This is the model class for table "hr_queries".
 *
 * @property integer $id
 * @property string $url
 * @property string $date_added
 */
class HrQuery extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hr_queries';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url'], 'required'],
            [['date_added'], 'safe'],
            [['url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'date_added' => 'Date Added',
        ];
    }

    public function beforeSave($insert) {
        if ($this->isNewRecord) {
            $this->date_added = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }

    public function processPage() {
        $stop_words_text = file_get_contents(Yii::$app->getBasePath().'/vendor/yooper/stop-words/data/stop-words_english_1_en.txt');
        $stop_words = explode("\n", $stop_words_text);
        $stop_words = array_slice($stop_words, 0, count($stop_words) - 1);

        $data = new Dom(); //Load DOM parser
        $data->loadFromUrl($this->url);
        $text = "";
        $contents = array();

        //Get meta description
        $meta = $data->find("meta[name='description']", 0);
        if($meta) {
            $contents['meta description'] = $meta->content;
            $text .= $meta->content . " " . $meta->content . " ";
        }
        //Get title and all tags
        $tags = array('title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'span', 'blockquote',
            'div', 'li', 'em');

        foreach($tags as $tag) {
            $contents[$tag] = "";
            foreach ($data->find($tag) as $phrase) {
                $contents[$tag] .= $phrase->text;
                $text .= $phrase->text . " " . $phrase->text . " ";
            }
        }
        //Check for article text, fallback to all p tags
        if(!empty($data->find('article', 0))) {
            $contents['article'] = "";
            foreach ($data->find('article p') as $phrase) {
                $contents['article'] .= $phrase->text;
                $text .= $phrase->text . " ";
            }
        }
        else if(!empty($data->find('.post', 0))) {
            $contents['.post p'] = "";
            foreach ($data->find('.post p') as $phrase) {
                $contents['.post p'] .= $phrase->text;
                $text .= $phrase->text . " ";
            }
        } else { //If no article found - get all p tags
            $contents['p'] = "";
            foreach ($data->find('p') as $phrase) {
                $contents['p'] .= $phrase->text;
                $text .= $phrase->text . " ";
            }
        }
        foreach($contents as $key => $val) {
            $contents[$key] = $this->sanitizeText($val, $stop_words);
        }
        $text = $this->sanitizeText($text, $stop_words);

        //Load TermExtractor
        $filter = new DefaultFilter($min_occurrence=3, $keep_if_strength=2);
        $tagger = new Tagger('english');
        $tagger->initialize($use_apc=true);

        $extractor = new TermExtractor($tagger, $filter);
        $terms = $extractor->extract($text);

        //Get all terms which have at least 2 words
        $terms_candidates = [];
        foreach ($terms as $term_info) {
            list($term, $occurrence, $word_count) = $term_info;
            if($word_count > 1) {
                $terms_candidates[] = ['term' => $term, 'occurrence' => $occurrence, 'wc' => $word_count];
            }
        }
        //Get 10 most occurred terms
        usort($terms_candidates, function($a, $b) {
            return $a['occurrence'] < $b['occurrence'];
        });
        $terms = array_slice($terms_candidates, 0, 10);

        foreach ($terms as $term) {
            $res = new HrQueryResult();
            $res->phrase = $term['term'];
            $res->score = $term['occurrence'];
            $res->query_id = $this->id;
            $res->tag = $this->getTag($term['term'], $contents);
            $res->save();
        }
    }

    public function getResults() //Relation
    {
        return $this->hasMany(HrQueryResult::className(), ['query_id' => 'id']);
    }

    public function getKeywords()
    {
        $results = $this->results;
        $output = "";
        foreach($results as $res) {
            $output .= "<span title='".$res->tag."'>" . $res->phrase . "</span> (".$res->score.")<br/>";
        }
        return $output;
    }

    //Custom methods

    private function sanitizeText($text, $stop_words) {
        $text = strtolower($text);
        $text = str_replace(['.',',','“','”'], ' ', $text);
        $text = str_replace('  ', ' ', $text);
        foreach ($stop_words as &$word) {
            $word = '/\b' . preg_quote($word, '/') . '\b/';
        }
        $text = preg_replace($stop_words, '', $text);
        return $text;
    }

    private function getTag($text, $contents) {
        foreach($contents as $key => $tag) {
            if($tag != "" && strpos(strtolower($tag), $text) !== false) {
                return $key;
            }
        }
        return "";
    }

}