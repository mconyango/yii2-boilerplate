<?php

namespace api\modules\v1\forms;

use common\helpers\Lang;
use common\models\Model;

class ResetPassword extends Model
{
    public $password;

    public $repeated_password;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            [
                ['repeated_password'],
                'compare',
                'compareAttribute' => 'password',
                'message' => Lang::t('Passwords do not match.')
            ],
        ];
    }
}