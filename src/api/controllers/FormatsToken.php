<?php
/**
 * Created by PhpStorm.
 * User: antony
 * Date: 5/8/17
 * Time: 12:37 PM
 */

namespace api\controllers;

trait FormatsToken
{
    /**
     * @param $token
     * @return string
     */
    private function formatToken($token)
    {
        // incase we have a bearer...skip that part
        if (str_contains($token, 'Bearer')) {
            $token = substr($token, strlen('Bearer '));
        }
        return $token;
    }
}