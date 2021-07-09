<?php

use backend\modules\conf\settings\SystemSettings;

?>
<div class="page-footer">
    <div class="row" style="margin-left: -13px;margin-right: -13px">
        <div class="col-xs-12 col-sm-6">
            <span>
                <?= SystemSettings::getAppName() ?> | &copy;<?= date('Y'); ?> <?= SystemSettings::getCompanyName() ?>
                <span class="hidden">- Developed By <a href="https://competamillman.co.ke/" target="_blank"><?= 'Competa Millman' ?></a></span>
            </span>

        </div>
        <div class="col-xs-6 col-sm-6 text-right hidden-xs">
            <!-- end div-->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
</div>