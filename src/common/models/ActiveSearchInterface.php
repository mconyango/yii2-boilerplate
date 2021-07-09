<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/01
 * Time: 1:50 PM
 */

namespace common\models;


interface ActiveSearchInterface
{

    /**
     * Search params for the active search
     * ```php
     *   return [
     *       ["name","_searchField","AND|OR"],//default is AND only include this param if there is a need for OR condition
     *       'id',
     *       'email'
     *   ];
     * ```
     * @return array
     */
    public function searchParams();
}