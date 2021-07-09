<?php

/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/23
 * Time: 10:05 PM
 */

namespace common\controllers;


use Yii;


class Controller extends \yii\web\Controller
{
    //user flash messages
    const FLASH_SUCCESS = 'success';
    const FLASH_ERROR = 'error';
    const FLASH_WARNING = 'warning';
    const FLASH_INFO = 'info';

    /**
     * @var string
     */
    public $moduleKey;

    /**
     * @var string
     */
    public $activeMenu;

    /**
     * @var string
     */
    public $activeSubMenu;

    /**
     * @var string
     */
    public $pageTitle;


    public function init()
    {
        parent::init();
    }

    /**
     * @param $menu
     * @return bool
     */
    public function isMenuActive($menu)
    {
        return ($this->activeMenu === $menu);
    }

    /**
     * @param $menu
     * @return bool
     */
    public function isSubMenuActive($menu)
    {
        return ($this->activeSubMenu === $menu);
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @param string $title
     */
    public function setPageTitle($title)
    {
        $this->pageTitle = $title;
    }
}
