<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/09
 * Time: 2:28 AM
 */

namespace common\widgets\highchart;


interface HighChartInterface
{
    /**
     * @param string $graphType @link http://api.highcharts.com/highcharts#chart.type
     * <pre>
     *  X,Y axis based graphs:
     *      array(
     *                  array(
     *                              "name"=>"Males",
     *                              "condition"=>"`gender`=:gender",
     *                              "params"=>array(":gender"=>"male"))),
     *                  array(
     *                              "name"=>"Females",
     *                              "condition"=>"`gender`=:gender",
     *                              "params"=>array(":gender"=>"female")))
     *           )
     * </pre>
     * <pre>
     * Series for PIE CHART
     *  array(
     *          array(
     *                 'data' => array(
     *                   array(
     *                     'name' =>"Males",
     *                      'condition' => '`gender`=:gender',
     *                      'params' => array(':gender' =>"Male"),
     *                   ),
     *                 array(
     *                      'name' => "Females",
     *                      'condition' => '`gender`=:gender',
     *                      'params' => array(':gender' =>"Females"),
     *              ),
     *      )
     *   )
     * );
     * </pre>
     * @param array $queryOptions e.g array('filters'=>array(),'condition'=>'','date_field'=>'date_created','sum'=>null,'dateRange'=>'From - To'),
     * @return array series.
     */
    public static function highChartOptions($graphType, $queryOptions);
}