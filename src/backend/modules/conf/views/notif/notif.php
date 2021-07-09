<?php
use common\helpers\Lang;
use yii\helpers\Url;

?>
<div class="pull-right header-activity hiddens-sm hiddens-xs">
   <span id="activity" class="activity-dropdown"
         data-mark-as-seen-url="<?= Url::to(['/conf/notif/mark-as-seen']) ?>"
         data-check-notif-url="<?= Url::to(['/conf/notif/fetch']) ?>">
       <i class="fa fa-bell"></i>
       <b class="badge hidden"></b>
   </span>

    <!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
    <div class="ajax-dropdown">
        <!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
        <div class="clearfix">
            <div class="pull-left">
                <h4><?= Lang::t('Notifications') ?> <span class="total-notif"></span></h4>
            </div>
            <div class="pull-right">
                <a id="mark_all_notif_as_read" href="javascript:void(0);"
                   data-href="<?= Url::to(['/conf/notif/mark-as-read']) ?>"><?= Lang::t('Mark all as read') ?></a>
            </div>
        </div>
        <!-- notification content -->
        <div class="ajax-notifications custom-scroll">
        </div>
        <!-- end notification content -->
        <!-- footer: refresh area -->
    <span>
        <button id="refresh_notif" type="button" data-loading="<i class='fa fa-refresh fa-spin'></i> Loading..."
                class="btn btn-xs btn-default pull-right">
            <i class="fa fa-refresh"></i>
        </button>
    </span>
        <!-- end footer -->
    </div>
</div>