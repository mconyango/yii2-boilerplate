<?php

/* @var $this yii\web\View */
/* @var $activeSub OrgSubscription */

/* @var $inactiveSubs OrgSubscription[] */

use backend\modules\subscription\models\OrgSubscription;
use common\helpers\Lang;
use common\helpers\Url;

$this->title = 'Subscription Status';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="padding-top-10">

        <div class="col-md-2">
            <div class="list-group ">
                <a class="list-group-item disabled" href="#">
                    <?= Lang::t('Subscriptions') ?>
                </a>
                <a class="list-group-item active"
                   href="<?= Url::to(['/dashboard/default/status']) ?>">
                    <?= Lang::t('Subscription Status') ?>
                </a>
            </div>
        </div>
        <div class="col-md-10">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title"><h4>Active Subscription</h4></div>
                </div>
                <div class="panel-body">
                    <div class="col-md-12">
                        <?php if ($activeSub === null): ?>
                            <div class="alert alert-warning" role="alert">
                                <strong>You do not have any active subscription.</strong> Features will be disabled.
                            </div>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subscription Plan</th>
                                    <th>Activated On</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="success">
                                    <td>1</td>
                                    <td><?= $activeSub->pricingPlan->name ?></td>
                                    <td><?= $activeSub->activated_on ?></td>
                                    <td><?= Yii::$app->formatter->asDate($activeSub->start_date, 'Y-M-dd') ?></td>
                                    <td><?= Yii::$app->formatter->asDate($activeSub->end_date, 'Y-M-dd') ?></td>
                                    <td><?= OrgSubscription::decodeStatus($activeSub->status) ?></td>
                                </tr>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <hr>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title"><h4>Subscription History</h4></div>
                </div>
                <div class="panel-body">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Subscription Plan</th>
                                <th>Activated On</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $k = 0;
                            foreach ($inactiveSubs as $k => $sub): $k++; ?>
                                <tr class="active">
                                    <td><?= $k ?></td>
                                    <td><?= $sub->pricingPlan->name ?></td>
                                    <td><?= $sub->activated_on ?></td>
                                    <td><?= Yii::$app->formatter->asDate($sub->start_date, 'Y-M-dd') ?></td>
                                    <td><?= Yii::$app->formatter->asDate($sub->end_date, 'Y-M-dd') ?></td>
                                    <td><?= OrgSubscription::decodeStatus($sub->status) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>