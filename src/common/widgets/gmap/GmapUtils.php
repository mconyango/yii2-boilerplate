<?php

namespace common\widgets\gmap;

use backend\modules\conf\settings\GoogleMapSettings;

/**
 * Google map utility functions
 * @author Fred <mconyango@gmail.com>
 */
class GmapUtils
{

    const MAP_TYPE_ROAD = 'ROADMAP';
    const MAP_TYPE_TERRAIN = 'TERRAIN';
    const MAP_TYPE_SATELLITE = 'SATELLITE';
    const MAP_TYPE_HYBRID = 'HYBRID';

    /**
     * @param string $address
     * @return bool|array
     */
    public static function geoCode($address)
    {
        $base_url = 'https://maps.googleapis.com/maps/api/geocode/json?sensor=false&key=' . GoogleMapSettings::getApiKey();
        $request_url = $base_url . "&address=" . urlencode($address);
        $response = static::sendRequest($request_url);
        if ($response->status != 'OK')
            return false;
        return $response->results;
    }

    /**
     * @param string $url
     * @return bool|array
     */
    private static function sendRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $responseBody = json_decode($response);
        $request_info = curl_getinfo($ch);
        curl_close($ch);
        if ($request_info['http_code'] != 200)
            return false;
        return $responseBody;
    }

    /**
     * @return array
     */
    public static function mapTypeOptions()
    {
        return [
            self::MAP_TYPE_ROAD => self::MAP_TYPE_ROAD,
            self::MAP_TYPE_TERRAIN => self::MAP_TYPE_TERRAIN,
            self::MAP_TYPE_SATELLITE => self::MAP_TYPE_SATELLITE,
            self::MAP_TYPE_HYBRID => self::MAP_TYPE_HYBRID,
        ];
    }

    /**
     * @return array
     */
    public static function zoomOptions()
    {
        $options = [];
        for ($i = 4; $i <= 24; $i++) {
            $options[$i] = $i;
        }
        return $options;
    }

}
