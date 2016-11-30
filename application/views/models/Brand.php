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

    public function __construct(){

        parent::__construct();

//        foreach($items as $key => $item){
//
//            $this->$key = $item;
//        }
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

    public function getBrandModel($brandId){

        $key = 'brandModel.'.$brandId.'';

        $brandM = RedisDb::getValue($key);

        if(!$brandM){

            $sql = 'SELECT `brand_id`, `brand_name`, `abbre`, `brand_url` FROM `bibi_car_brand_list` WHERE `brand_id` = "'.$brandId.'" ';

            $brandM = $this->query($sql);

            if(isset($brandM[0])){

                RedisDb::setValue($key, serialize($brandM[0]));

                return $brandM[0];
            }
            else{

                return new stdClass;
            }

        }
        else{

            return unserialize($brandM);
        }



    }

    public function getSeriesModel($brandId, $seriesId){


        $key = 'seriesModel.'.$seriesId.'';

        $series = RedisDb::getValue($key);

        if(!$series){

            $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name`  FROM `bibi_car_brand_series` WHERE `brand_id` = ' . $brandId . ' AND `brand_series_id` = '.$seriesId.' ';

            $series = $this->query($sql);

            if(isset($series[0])){

                $info = $series[0];
                $info['brand_id'] = $brandId;

                RedisDb::setValue($key, serialize($info));

                return $info;

            }
            else{

                return new stdClass();
            }

        }
        else{

            return unserialize($series);

        }


    }

    public function getModelModel($seriesId, $modelId)
    {

        $key = 'modelModel.'.$modelId.'';

        $model = RedisDb::getValue($key);

        if(!$model){

            $sql = 'SELECT `model_id` , `model_name` FROM `bibi_car_series_model` WHERE  `series_id` = '.$seriesId.' AND `model_id`='.$modelId.' ';

            $model = $this->query($sql);

            if(isset($model[0])){

                $info = $model[0];
                $info['series_id'] = $seriesId;

                RedisDb::setValue($key, serialize($info));

                return $info;
            }
            else{

                return new stdClass();
            }

        }
        else{

            return unserialize($model);
        }

    }




}