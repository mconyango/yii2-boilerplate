<?php

namespace backend\modules\auth\models;

use backend\modules\auth\Session;
use backend\modules\core\models\Organization;
use backend\modules\core\models\OrganizationDataTrait;
use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;
use yii\web\Application;

/**
 * This is the model class for table "auth_audit_trail".
 *
 * @property integer $id
 * @property integer $action
 * @property string $action_description
 * @property string $url
 * @property string $user_agent
 * @property string $ip_address
 * @property integer $user_id
 * @property  integer org_id
 * @property string $details
 * @property string $created_at
 *
 * @property Users $user
 * @property Organization $org
 */
class AuditTrail extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait, OrganizationDataTrait;

    //actions
    const ACTION_VIEW = 1;
    const ACTION_CREATE = 2;
    const ACTION_UPDATE = 3;
    const ACTION_DELETE = 4;

    public $enableAuditTrail = false;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_audit_trail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action', 'action_description', 'url', 'user_id', 'user_agent'], 'required'],
            [['action', 'user_id', 'org_id'], 'integer'],
            [['details'], 'string'],
            [['action_description', 'url', 'user_agent'], 'string', 'max' => 1000],
            [['ip_address'], 'string', 'max' => 30],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'action' => Lang::t('Action'),
            'action_description' => Lang::t('Action Description'),
            'url' => Lang::t('URL'),
            'ip_address' => Lang::t('Ip Address'),
            'user_agent' => Lang::t('Browser'),
            'user_id' => Lang::t('User'),
            'org_id' => Lang::t('Organization'),
            'details' => Lang::t('Details'),
            'created_at' => Lang::t('Time'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchParams()
    {
        return [
            ['ip_address', 'ip_address'],
            'action',
            'user_id',
            'org_id',
        ];
    }

    /**
     * @param $action
     * @return string
     */
    public static function decodeAction($action)
    {
        $decoded = $action;
        switch ($action) {
            case self::ACTION_VIEW:
                $decoded = 'View';
                break;
            case self::ACTION_CREATE:
                $decoded = 'Create';
                break;
            case self::ACTION_UPDATE:
                $decoded = 'Update';
                break;
            case self::ACTION_DELETE:
                $decoded = 'Delete';
                break;
        }
        return $decoded;
    }

    /**
     * @param bool $tip
     * @return array
     */
    public static function actionOptions($tip = false)
    {
        return Utils::appendDropDownListPrompt([
            self::ACTION_VIEW => static::decodeAction(self::ACTION_VIEW),
            self::ACTION_CREATE => static::decodeAction(self::ACTION_CREATE),
            self::ACTION_UPDATE => static::decodeAction(self::ACTION_UPDATE),
            self::ACTION_DELETE => static::decodeAction(self::ACTION_DELETE),
        ], $tip);
    }

    /**
     * @param ActiveRecord $model
     * @param string $action
     * @return bool
     */
    public static function addAuditTrail($model, $action)
    {
        $changedAttributes = $model->getChangedAttributes();
        if (!Utils::isWebApp())
            return false;
        if (Yii::$app->user->getIsGuest())
            return false;

        if ($action === self::ACTION_UPDATE && empty($changedAttributes)) {
            return false;
        }

        if ($action === self::ACTION_CREATE || $action === self::ACTION_DELETE) {
            foreach ($model->safeAttributes() as $k) {
                if ($model->hasAttribute($k)) {
                    $changedAttributes[$k] = ['old' => null, 'new' => $model->attributes[$k]];
                }
            }
        }

        $actionDescription = static::getActionDescription($model, $action);
        if ($model->getProcess() !== null){
            $processDescription = "Process {$model->getProcess()}";
            $actionDescription = $processDescription . ' - ' . $actionDescription;
        }

        $audit = new AuditTrail();
        $audit->action = $action;
        $audit->ip_address = Yii::$app->request->getUserIP();
        $audit->url = Yii::$app->request->getAbsoluteUrl();
        $audit->user_agent = Yii::$app->request->getUserAgent();
        $audit->user_id = Yii::$app->user->id;
        $audit->details = serialize($changedAttributes);
        $audit->action_description = $actionDescription;

        if (Utils::isWebApp()) {
            if (!empty(Session::accountId())) {
                $audit->org_id = Session::accountId();
            }
        }

        $audit->save();
    }

    /**
     * @param ActiveRecord $model
     * @param string $action
     * @return null|string
     */
    private static function getActionDescription($model, $action)
    {
        $actionDescription = null;
        switch ($action) {
            case self::ACTION_CREATE:
                $actionDescription = $model->actionCreateDescriptionTemplate;
                break;
            case self::ACTION_UPDATE:
                $actionDescription = $model->actionUpdateDescriptionTemplate;
                break;
            case self::ACTION_DELETE:
                $actionDescription = $model->actionDeleteDescriptionTemplate;
                break;

        }

        $actionDescription = strtr($actionDescription, ['{{table}}' => $model->getCleanTableName(), '{{id}}' => $model->primaryKey]);

        return $actionDescription;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

}
