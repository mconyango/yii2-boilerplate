<?php

namespace api\modules\v1\forms;

use common\models\Model;

class ProvideEmail extends Model
{
    public $email;

    public function rules()
    {
        return [
            [['email'], 'required'],
            ['email', 'email'],
        ];
    }
}