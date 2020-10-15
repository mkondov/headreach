<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hr_queries_results".
 *
 * @property integer $id
 * @property integer $query_id
 * @property string $phrase
 * @property double $score
 * @property string $tag
 *
 * @property HrQueries $query
 */
class HrQueryResult extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hr_queries_results';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['query_id', 'phrase', 'score'], 'required'],
            [['query_id'], 'integer'],
            [['score'], 'number'],
            [['phrase'], 'string', 'max' => 100],
            [['tag'], 'string', 'max' => 10],
            [['query_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrQuery::className(), 'targetAttribute' => ['query_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'query_id' => 'Query ID',
            'phrase' => 'Phrase',
            'score' => 'Score',
            'tag' => 'Tag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return $this->hasOne(HrQuery::className(), ['id' => 'query_id']);
    }
}
