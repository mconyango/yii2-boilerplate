<?php

namespace api\modules\v1\forms;

use yii\base\Model;

class LoginForm extends Model
{
    public $username;

    public $password;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
        ];
    }
}