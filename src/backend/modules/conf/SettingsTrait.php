<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-06 18:52
 * Time: 18:52
 */

namespace backend\modules\conf;


use Yii;

trait SettingsTrait
{
    protected $_toBeSave = [];
    protected $_toBeDelete = [];
    protected $_catToBeDel = [];
    protected $_cacheFlush = [];
    protected $_items = [];

    /**
     * Setting::set()
     *
     * @param string $category
     * @param mixed $key
     * @param string $value
     * @param bool $toDatabase
     * @param null $account_id
     */
    public function set($category, $key, $value = '', $toDatabase = true, $account_id = null)
    {
        $qualifiedCategory = static::getQualifiedCategory($category, $account_id);
        if (is_array($key)) {
            foreach ($key as $k => $v)
                $this->set($qualifiedCategory, $k, $v, $toDatabase);
        } else {
            if ($toDatabase)
                $this->_toBeSave [$qualifiedCategory] [$key] = $value;
            $this->_items [$qualifiedCategory] [$key] = $value;
        }
    }

    /**
     * Setting::get()
     *
     * @param string $category
     * @param string $key
     * @param string $default
     * @param null|int $org_id
     * @param bool $strict
     * @return null|string
     * @throws \yii\db\Exception
     */
    public function get($category, $key = '', $default = '', $org_id = null, $strict = true)
    {
        if ($strict) {
            $qualifiedCategory = static::getQualifiedCategory($category, $org_id);
        } else {
            $qualifiedCategory = $category;
        }

        if (!isset ($this->_items [$qualifiedCategory])) {
            $this->load($qualifiedCategory);
        }
        if (empty ($key) && empty ($default) && !empty ($qualifiedCategory)) {
            $val = isset ($this->_items [$qualifiedCategory]) ? $this->_items [$qualifiedCategory] : null;
        } elseif (isset ($this->_items [$qualifiedCategory] [$key])) {
            $val = $this->_items [$qualifiedCategory] [$key];
        } else {
            $val = !empty ($default) ? $default : null;
        }

        if (null === $val && $strict && $category !== $qualifiedCategory) {
            return $this->get($category, $key, $default, $org_id, false);
        }
        return $val;
    }

    /**
     * Setting::delete()
     *
     * @param string $category
     * @param string $key
     */
    public function delete($category = 'system', $key = '')
    {
        $qualifiedCategory = static::getQualifiedCategory($category);
        if (!empty ($qualifiedCategory) && empty ($key)) {
            $this->_catToBeDel [] = $qualifiedCategory;
            return;
        }
        if (is_array($key)) {
            foreach ($key as $k)
                $this->delete($qualifiedCategory, $k);
        } else {
            if (isset ($this->_items [$qualifiedCategory] [$key])) {
                unset ($this->_items [$qualifiedCategory] [$key]);
                $this->_toBeDelete [$qualifiedCategory] [] = $key;
            }
        }
    }

    /**
     * @param $category
     * @return string
     */
    public static function getCacheKey($category)
    {
        return $category . '-settings';
    }

    /**
     * Setting::load()
     *
     * @param mixed $category
     * @return array|mixed
     * @throws \yii\db\Exception
     */
    protected function load($category)
    {
        $cache_key = static::getCacheKey($category);
        $items = Yii::$app->cache->get($cache_key);
        if (!$items) {
            $result = Yii::$app->getDb()->createCommand(
                "SELECT * FROM {{%" . $this->settingTable . "}} WHERE [[category]]=:cat", [
                ':cat' => $category,
            ])->queryAll();
            if (empty ($result)) {
                $this->set($category, '{empty}', '{empty}', false);
                return false;
            }
            $items = [];
            foreach ($result as $row)
                $items [$row ['key']] = @unserialize($row ['value']);
            Yii::$app->cache->add($cache_key, $items, 60);
        }
        $this->set($category, $items, '', false);
        return $items;
    }

    /**
     * Setting::toArray()
     * @return array
     */
    public function toArray()
    {
        return $this->_items;
    }

    /**
     * Setting::addDbItem()
     *
     * @param string $category
     * @param mixed $key
     * @param mixed $value
     * @throws \yii\db\Exception
     */
    private function addDbItem($category, $key, $value)
    {
        $result = Yii::$app->getDb()->createCommand(
            "SELECT * FROM {{%" . $this->settingTable . "}} WHERE [[category]]=:cat AND [[key]]=:key LIMIT 1", [
            ':cat' => $category,
            ':key' => $key
        ])->queryOne();
        $_value = @serialize($value);
        if (!$result) {
            Yii::$app->getDb()->createCommand(
                "INSERT INTO {{%" . $this->settingTable . "}} ([[category]], [[key]], [[value]]) VALUES(:cat,:key,:value)",
                [
                    ':cat' => $category,
                    ':key' => $key,
                    ':value' => $_value
                ])->execute();
        } else {
            Yii::$app->getDb()->createCommand(
                "UPDATE {{%" . $this->settingTable . "}} SET [[value]]=:value WHERE [[category]]=:cat AND [[key]]=:key",
                [
                    ':cat' => $category,
                    ':key' => $key,
                    ':value' => $_value
                ])->execute();
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    public function commit()
    {
        $this->_cacheFlush = [];
        if (count($this->_catToBeDel) > 0) {
            foreach ($this->_catToBeDel as $catName) {
                Yii::$app->getDb()->createCommand(
                    "DELETE FROM {{%" . $this->settingTable . "}} WHERE [[category]]=:cat", [
                    ':cat' => $catName
                ])->execute();
                $this->_cacheFlush [] = $catName;
                if (isset ($this->_toBeDelete [$catName]))
                    unset ($this->_toBeDelete [$catName]);
                if (isset ($this->_toBeSave [$catName]))
                    unset ($this->_toBeSave [$catName]);
            }
        }
        if (count($this->_toBeDelete) > 0) {
            foreach ($this->_toBeDelete as $catName => $keys) {
                $params = [];
                $i = 0;
                foreach ($keys as $v) {
                    if (isset ($this->_toBeSave [$catName] [$v]))
                        unset ($this->_toBeSave [$catName] [$v]);
                    $params [':p' . $i] = $v;
                    ++$i;
                }
                $names = implode(',', array_keys($params));
                $command = Yii::$app->getDb()->createCommand(
                    "DELETE FROM {{%" . $this->settingTable . "}} WHERE [[category]]=:cat AND [[key]] IN ($names)", [
                    ':cat' => $catName
                ]);
                foreach ($params as $key => $value)
                    $command->bindParam($key, $value);
                $command->execute();
                $this->_cacheFlush [] = $catName;
            }
        }
        /** @FIXME: Switch to batch mode... * */
        if (count($this->_toBeSave) > 0) {
            foreach ($this->_toBeSave as $catName => $keyValues) {
                foreach ($keyValues as $k => $v)
                    $this->addDbItem($catName, $k, $v);
                $this->_cacheFlush [] = $catName;
            }
        }
        if (count($this->_cacheFlush) > 0) {
            foreach ($this->_cacheFlush as $catName)
                Yii::$app->cache->delete(static::getCacheKey($catName));
        }
    }

    /**
     * @param string $category
     * @param null|int $account_id
     * @return string
     */
    public static function getQualifiedCategory($category, $account_id = null)
    {
        if (Utils::isWebApp() && !Yii::$app->user->getIsGuest() && Session::isOrganization()) {
            $account_id = Session::accountId();
        }
        if (!empty($account_id)) {
            $category .= '_org_' . $account_id;
        }
        return $category;
    }

}