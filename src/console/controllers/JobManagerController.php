<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/07
 * Time: 7:53 PM
 */

namespace console\controllers;


use backend\modules\loan\models\LoanRepaymentSchedule;
use backend\modules\saving\models\Account;
use backend\modules\saving\models\AutomatedInternalTransferBreakdown;
use backend\modules\saving\models\FixedDepositBreakdown;
use backend\modules\saving\models\WithdrawNotice;

/**
 * Runs all the system jobs (daemon and cronjobs)
 *
 * @author Fred <mconyango@gmail.com>
 */
class JobManagerController extends BaseController
{
    public function actionGeneral()
    {
        $this->startJob('generalCron');
    }

    protected function generalCron()
    {
        WithdrawNotice::checkDueDate();
        LoanRepaymentSchedule::checkDueDate();
    }

    public function actionNotification()
    {
        $this->startJob('notificationCron');
    }

    protected function notificationCron()
    {
        \backend\modules\conf\models\Notif::createNotifications();
    }

    public function actionPostInterest()
    {
        $this->startJob('postInterest');
    }

    protected function postInterest()
    {
        \backend\modules\loan\models\LoanRepaymentSchedule::postInterest();
    }

    public function actionCheckDormancy()
    {
        $this->startJob('checkDormancy');
    }

    protected  function checkDormancy()
    {
        Account::checkIfAccountCanBeMadeDormant();
    }

    public function actionFixedAccountEarnInterest()
    {
        $this->startJob('fixedAccountEarnInterest');
    }

    /**
     * @throws \Exception
     */
    protected  function fixedAccountEarnInterest()
    {
        FixedDepositBreakdown::topUpAccountWithInterestLessTax();
    }

    public function actionAutomatedInternalTransfer()
    {
        $this->startJob('automatedInternalTransfer');
    }

    /**
     * @throws \Exception
     */
    protected function automatedInternalTransfer()
    {
        AutomatedInternalTransferBreakdown::triggerAutomatedInternalTransfer();
    }

    public function actionPostPenalties()
    {
        $this->startJob('postPenalties');
    }

    protected function postPenalties()
    {
        \backend\modules\loan\models\LoanRepaymentSchedule::postPenalties();
    }

    public function actionRenewSubscriptions()
    {
        $this->startJob('renewSubscriptions');
    }

    protected function renewSubscriptions()
    {
        \backend\modules\subscription\models\OrgSubscription::renewAll();
    }

    public function actionExpireSubscriptions()
    {
        $this->startJob('expireSubscriptions');
    }

    protected function expireSubscriptions()
    {
        \backend\modules\subscription\models\OrgSubscription::expireAll();
    }
}