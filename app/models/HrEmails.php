<?php

namespace app\models;

class HrEmails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hr_emails';
    }

    public function extendDataWithEmails( $influencers ) {
        $emails_model = new HrEmails();

        foreach ($influencers as $i => $influencer) {

            $inf_id = ( isset($influencer['influencer_id']) ? $influencer['influencer_id'] : $influencer['id'] );

            $emails = $emails_model->findAll(array(
                'influencer_id' => $inf_id
            ));

            if ( empty($emails) ) {
                $influencers[$i]['emails_data'] = false;
            } else {
                $tmp_email_data = array();

                foreach ($emails as $email) {
                    $tmp_email_data[] = array(
                        'email' => $email->email,
                        'score' => $email->score,
                        'source' => $email->source,
                        'api_response' => $email->api_response,
                        'validation_data' => $email->validation_data,
                        'is_smtp_valid' => $email->is_smtp_valid,
                    );
                }

                $influencers[$i]['emails_data'] = $tmp_email_data;
                unset($tmp_email_data);
            }

        }

        return $influencers;
    }

    public function extendDataWithEmailsSingle( $influencer ) {
        $emails_model = new HrEmails();

        $emails = $emails_model->findAll(array(
            'influencer_id' => $influencer['id']
        ));

        if ( empty($emails) ) {
            $influencer->emails_data = false;
            return $influencer;
        }

        $tmp_email_data = array();

        foreach ($emails as $email) {
            $tmp_email_data[] = array(
                'email' => $email->email,
                'score' => $email->score,
                'source' => $email->source,
                'api_response' => $email->api_response,
                'validation_data' => $email->validation_data,
                'is_smtp_valid' => $email->is_smtp_valid,
            );
        }

        $influencer->emails_data = $tmp_email_data;

        return $influencer;
    }

    /**
     * @inheritdoc
     */
    // public function rules()
    // {
    //     return [
    //         [['url'], 'required'],
    //         [['date_added'], 'safe'],
    //         [['url'], 'string', 'max' => 255],
    //     ];
    // }

}