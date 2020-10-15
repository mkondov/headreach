<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * KeywordInputForm is the model behind the login form.
 */
class KeywordInputForm extends Model
{
    public $keyword;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['keyword'], 'required'],
            // password is validated by validatePassword()
            ['keyword', 'validateKeyword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateKeyword($attribute, $params)
    {
        if (!$this->hasErrors()) {
           // $user = $this->getUser();

          //  if (!$user || !$user->validatePassword($this->password)) {
          //     $this->addError($attribute, 'Incorrect username or password.');
          //  }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

   
}
