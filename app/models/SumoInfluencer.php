<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sumo_influencers".
 *
 * @property integer $id
 * @property string $sumo_id
 * @property integer $num_following
 * @property integer $pagerank
 * @property string $location
 * @property double $reply_ratio
 * @property double $retweet_ratio
 * @property string $person_type
 * @property string $twitter_id_str
 * @property double $avg_retweets
 * @property string $image
 * @property string $url
 * @property double $url_share_ratio
 * @property integer $domain_authority
 * @property integer $page_authority
 * @property string $username
 * @property string $bio
 * @property string $name
 * @property integer $num_followers
 * @property string $highlight
 */
class SumoInfluencer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sumo_influencers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sumo_id', 'num_following', 'pagerank', 'location', 'reply_ratio', 'retweet_ratio', 'person_type', 'twitter_id_str', 'avg_retweets', 'image', 'url', 'url_share_ratio', 'domain_authority', 'page_authority', 'username', 'bio', 'name', 'num_followers'], 'required'],
            [['sumo_id', 'num_following', 'pagerank', 'domain_authority', 'page_authority', 'num_followers'], 'integer'],
            [['reply_ratio', 'retweet_ratio', 'avg_retweets', 'url_share_ratio'], 'number'],
            [['person_type', 'bio'], 'string'],
            [['location', 'image', 'url', 'username', 'name', 'highlight'], 'string', 'max' => 255],
            [['twitter_id_str'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sumo_id' => 'Sumo ID',
            'num_following' => 'Number of Following',
            'pagerank' => 'Pagerank',
            'location' => 'Location',
            'reply_ratio' => 'Reply Ratio',
            'retweet_ratio' => 'Retweet Ratio',
            'person_type' => 'Person Type',
            'twitter_id_str' => 'Twitter Id',
            'avg_retweets' => 'Avg Retweets',
            'image' => 'Image',
            'url' => 'Url',
            'url_share_ratio' => 'Url Share Ratio',
            'domain_authority' => 'Domain Authority',
            'page_authority' => 'Page Authority',
            'username' => 'Username',
            'bio' => 'Bio',
            'name' => 'Name',
            'num_followers' => 'Number of Followers',
            'highlight' => 'Highlight',
        ];
    }
}
