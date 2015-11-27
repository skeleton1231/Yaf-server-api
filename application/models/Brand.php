<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/11
 * Time: 上午10:58
 */

class BrandModel extends PdoDb{

    static public  $tableBrand  = 'bibi_car_brand_list';
    static public  $tableSeries = 'bibi_car_brand_series';
    static public  $tableModel  = 'bibi_car_series_model';

    public $brand_id;
    public $series_id;
    public $model_id;

    public function __construct($items){

        parent::__construct();

        foreach($items as $key => $item){

            $this->$key = $item;
        }
    }

    public function getBrandInfo(){

        $sql = 'SELECT
                CONCAT(t1.brand_name, t2.brand_series_name, t3.model_name) AS car_name,
                t1.brand_id,
                t1.brand_name,
                t2.brand_series_id,
                t2.brand_series_name AS `series_name`,
                t3.model_id,
                t3.model_name
                FROM
                `'.self::$tableBrand.'` AS t1 LEFT JOIN `'.self::$tableSeries.'` AS t2
                ON t1.brand_id = t2.brand_id
                LEFT JOIN `'.self::$tableModel.'` AS t3
                ON t2.brand_series_id = t3.series_id
                WHERE
                t1.brand_id = '.$this->brand_id.'
                AND t2.brand_series_id = '.$this->series_id.'
                AND t3.model_id = '.$this->model_id.'
                ';

        $info = $this->query($sql);

        return isset($info[0]) ? $info[0] : array();
    }


}