<?php
use backend\modules\conf\models\Notif;
use backend\modules\conf\models\NotifTypes;
use common\helpers\DateUtils;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?php
if (!empty($data)): ?>
    <ul class="notification-body">
        <?php foreach ($data as $row):
            $notif = Notif::processTemplate($row['notif_type_id'], $row['item_id']);
            if (!$notif) {
                continue;
            }
            ?>
            <li id="notif_<?= $row['id'] ?>" class="notif-item<?= !$row['is_read'] ? ' unread' : '' ?>"
                data-mark-as-read-url="<?= Url::to(['/conf/notif/mark-as-read', 'id' => $row['id']]) ?>">
                <a href="<?= $notif['url'] ?>" style="color: #333;">
                    <span class="padding-10">
                        <em class="badge padding-5 no-border-radius bg-color-blueLight pull-left margin-right-5">
                            <i class="fa <?= NotifTypes::getIcon($row['notif_type_id']) ?> fa-fw fa-2x"></i>
                        </em>
                        <span>
                            <span class="notif-text">
                                <b><?= Html::decode($notif['subject']) ?></b>:  <?= Html::decode($notif['message']) ?>
                            </span>
                            <time class="pull-right font-xs text-muted">
                                <i><?= DateUtils::formatToLocalDate($row['created_at']) ?></i></time>
                        </span>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="alert alert-transparent text-center">
        <h4><?= Lang::t('You have no notifications at the moment') ?></h4>
        <i class="fa fa-bell fa-2x fa-border"></i>
    </div>
<?php endif; ?>