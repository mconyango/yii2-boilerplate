<?php

namespace api\modules\v1;

use backend\models\Settings;
use backend\models\Settings as S;
use Carbon\Carbon;
use common\helpers\DateUtils;
use Yii;

class Config
{
    /**
     * @var S
     */
    protected $settings;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->settings = $this->getComponent();
    }

    /**
     * @return static
     */
    public static function init()
    {
        return new static;
    }

    /**
     * @return S
     */
    public function getComponent()
    {
        /* @var $settings S */
        $this->settings = Yii::$app->setting;
        return $this->settings;
    }

    /**
     * @return bool
     */
    public function systemIsUsable()
    {
        // check sem
        $today = Carbon::now();
        list($semStartDate, $semStartTime) = $this->getSemesterStart();

        list($semEndDate, $semEndTime) = $this->getSemesterEnd();

        $start = $semStartDate . ' ' . $semStartTime;
        $end = $semEndDate . ' ' . $semEndTime;

        // the start time
        if (DateUtils::isLessThan($today, Carbon::parse($start), false)) {
            return false;
        }

        // the end time
        if (DateUtils::isGreaterThan($today, Carbon::parse($end), false)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getSemesterStart()
    {
        $semStartDate = $this->settings->get(Settings::DEFAULT_CATEGORY, Settings::KEY_REG_START_DATE);
        $semStartTime = $this->settings->get(Settings::DEFAULT_CATEGORY, Settings::KEY_REG_START_TIME);
        return [$semStartDate, $semStartTime];
    }

    /**
     * @return array
     */
    public function getSemesterEnd()
    {
        $semEndDate = $this->settings->get(Settings::DEFAULT_CATEGORY, Settings::KEY_REG_END_DATE);
        $semEndTime = $this->settings->get(Settings::DEFAULT_CATEGORY, Settings::KEY_REG_END_TIME);
        return [$semEndDate, $semEndTime];
    }
}