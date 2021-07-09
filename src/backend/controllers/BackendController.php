<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-16 8:43 PM
 */

namespace backend\controllers;


use backend\modules\auth\Acl;
use backend\modules\auth\models\Users;
use common\controllers\Controller;
use common\helpers\Utils;
use Yii;

class BackendController extends Controller
{
    /**
     * @var
     */
    public $resource;

    /**
     * @var
     */
    public $resourceLabel;

    /**
     * @var bool
     */
    public $enableDefaultAcl = false;

    /**
     * The help module name. Should be almost/similar to the resource name
     * Where the resource does not have a 'readable' name, then this will change
     * For instance a resource like AUTH_USERS, should have a help module name of 'User Management', etc
     * @var string
     */
    public $helpModuleName;

    /**
     * Enables displaying/hiding of the help link
     *
     * @var bool
     */
    public $enableHelpLink = true;

    /**
     * Enables displaying of the help link at the top of the page
     * Essentially, this one would take the user to general help, as opposed to the one
     * above which narrows down per module
     *
     * @var bool
     */
    public $displayTopHelpLink = true;

    /**
     * Hide the whole navbar, if necessary
     *
     * @var bool
     */
    public $hideNavMenu = false;

    /**
     * Should be called before any action the require ACL
     * @param string $action
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function hasPrivilege($action = NULL)
    {
        if (NULL === $action)
            $action = Acl::ACTION_VIEW;

        Acl::hasPrivilege($this->resource, $action);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $allowed_routes = ['auth/user/change-password', 'error/index'];
            if (!Yii::$app->request->isAjax && !Yii::$app->user->isGuest && Users::isRequirePasswordChange() && !in_array($this->route, $allowed_routes)) {
                return $this->redirect(['/auth/user/change-password']);
            }

            // allow overrides
            if ($this->enableDefaultAcl) {
                // check permissions, for all default existing actions
                switch ($action->id) {
                    case "index":
                    case "view":
                        $this->hasPrivilege(Acl::ACTION_VIEW);
                        break;
                    case "create":
                        $this->hasPrivilege(Acl::ACTION_CREATE);
                        break;
                    case "update":
                        $this->hasPrivilege(Acl::ACTION_UPDATE);
                        break;
                    case "delete":
                        $this->hasPrivilege(Acl::ACTION_DELETE);
                        break;
                    default:
                        // do nothing.A custom implementation is required
                        break;
                }
            }
            //set default page titles
            if (empty($this->pageTitle) && !empty($this->resourceLabel)) {
                switch ($action->id) {
                    case "index":
                        $this->pageTitle = 'Manage ' . Utils::pluralize($this->resourceLabel);
                        break;
                    case "view":
                        $this->pageTitle = 'View ' . $this->resourceLabel;
                        break;
                    case "create":
                        $this->pageTitle = 'Create ' . $this->resourceLabel;
                        break;
                    case "update":
                        $this->pageTitle = 'Update ' . $this->resourceLabel;
                        break;
                    case "delete":
                        $this->pageTitle = 'Delete ' . $this->resourceLabel;
                        break;
                }
            }
            return $this->checkSubscription();
        }
        return false;
    }

    public function checkSubscription()
    {
        if (in_array($this->route, ['dashboard/default/status', 'error/index'])) {
            return true;
        }
        return true;
    }
}