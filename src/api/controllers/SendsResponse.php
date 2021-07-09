<?php

namespace api\controllers;

use api\modules\v1\pojo\ResponseObject;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Yii;

trait SendsResponse
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Send an array of data as the response
     *
     * @param $items
     * @param $transformer
     * @return array
     */
    protected function sendItems($items, $transformer)
    {
        // pass false so that the data isn't housed in a data object in the response
        $items = new Collection($items, $transformer, false);
        return $this->manager->createData($items)->toArray();
    }

    /**
     * Send a single item to the response
     *
     * @param $item
     * @param $transformer
     * @return array
     */
    protected function sendItem($item, $transformer)
    {
        $data = new Item($item, $transformer, false);
        return $this->manager->createData($data)->toArray();
    }

    /**
     * Send an error
     *
     * @param $params
     * @return ResponseObject
     */
    protected function sendError($params)
    {
        if (array_has($params, 'code')) {
            Yii::$app->response->setStatusCode(array_pull($params, 'code', 500));
        }
        return $this->sendMessage($params);
    }

    /**
     * Send something to the user
     *
     * @param $params
     * @return ResponseObject
     */
    protected function sendMessage($params)
    {
        return new ResponseObject((array)$params);
    }

}