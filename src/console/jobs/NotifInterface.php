<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 5:21 PM
 */

namespace console\jobs;


use backend\modules\conf\models\NotifTypes;

interface NotifInterface
{
    public static function createSystemNotifications();

    /**
     * @param string $notif_type_id
     * @param string $item_id
     * @return mixed
     */
    public static function createManualNotifications($notif_type_id, $item_id);

    /**
     *
     * @param string $template
     * @param string $item_id
     * @param string $notif_type_id
     *
     * @return array
     */
    public static function processInternalTemplate($template, $item_id, $notif_type_id);

    /**
     * @param NotifTypes $notifType
     * @param string $itemId
     * @return array
     */
    public static function processEmailTemplate($notifType, $itemId);

    /**
     * @param NotifTypes $notifType
     * @param string $itemId
     * @return array
     */
    public static function processSmsTemplate($notifType, $itemId);

}