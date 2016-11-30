<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午4:05
 */
class CarController extends ApiYafControllerAbstract
{


    public function brandAction()
    {

//
//        $brandList = RedisDb::getValue('brand_list');
//
//        if (!$brandList) {
//
//
//            $sql = 'SELECT `brand_id`, `brand_name`, `abbre`, `brand_url` FROM `bibi_car_brand_list` ORDER BY `abbre` ASC';
//
//            $pdo = new PdoDb;
//            $list = $pdo->query($sql);
//
//            $brandList = array();
//
//            foreach ($list as $key => $item) {
//
//                $alpha = $item['abbre'];
//                unset($item['abbre']);
//                $brandList[$alpha][] = $item;
//
//            }
//
//            RedisDb::setValue('brand_list', serialize($brandList));
//
//        } else {
//
//            $brandList = unserialize($brandList, true);
//
//        }

        $sql = 'SELECT `brand_id`, `brand_name`, `abbre`, `brand_url` FROM `bibi_car_brand_list` WHERE is_hot = 1 ORDER BY `abbre` ASC';

        $pdo = new PdoDb;
        $list = $pdo->query($sql);

        $brandList = array();

        foreach ($list as $key => $item) {

            $alpha = $item['abbre'];
            unset($item['abbre']);
            $brandList[$alpha][] = $item;

        }

        $response = array();
        $response['brand_list'] = $brandList;


        $this->send($response);

    }


    public function seriesAction($brand_id)
    {

        if (!$brand_id) {

            $this->send_error(NOT_ENOUGH_ARGS);
        }

        $key = 'brand_series_model_' . $brand_id . '';

        //$brandInfo = RedisDb::getValue($key);

        //if(!$brandInfo){

        $pdo = new PdoDb;

        $sql = 'SELECT * FROM `bibi_car_brand_list` WHERE `brand_id` = ' . $brand_id . '';
        $brand = @$pdo->query($sql)[0];

        $brandInfo = array();
        //$brandInfo['brand_id'] = $brand['brand_id'];
        //$brandInfo['brand_name'] = $brand['brand_name'];
        //$brandInfo['brand_url'] = $brand['brand_url'];
        //$brandInfo['abbre'] = $brand['abbre'];

        $brandInfo['series'] = array();

        $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name`  FROM `bibi_car_brand_series` WHERE `brand_id` = ' . $brand_id . '';

        $series = $pdo->query($sql);

        $info = array();

        foreach($series as $serie){

            $serie['brand_id'] = $brand['brand_id'];
            $info[] = $serie;
        }

        //$brandInfo['series'] = $series;


//
//            $sql = 'SELECT DISTINCT `makename` FROM `bibi_car_brand_series` WHERE `brand_id` = '.$brand_id.'';
//            $makenames = $pdo->query($sql);
//
//
//            foreach($makenames as $k => $makename){
//
//                $mk = $makename['makename'];
//                $brandInfo['series'][$k]['makename'] = $mk;
//                $brandInfo['series'][$k]['serielist'] = array();
//
//                $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name` FROM `bibi_car_brand_series` WHERE `makename` = "'.$mk.'" ';
//
//                $series = $pdo->query($sql);
//
//                foreach($series as $k1 => $serie){
//
//                    $series_id = $serie['series_id'];
//                    $sql = 'SELECT DISTINCT model_year FROM `bibi_car_series_model` WHERE `series_id` = '.$series_id.'';
//                    $model_years = $pdo->query($sql);
//
//                    foreach($model_years as $k2 => $model_year){
//
//                        $year = $model_year['model_year'];
//                        $serie['model'][$k2]['year'] = $year;
//                        $serie['model'][$k2]['modellist'] = array();
//                        $sql = 'SELECT `model_id` , `model_name` FROM `bibi_car_series_model` WHERE `model_year` = "'.$year.'" AND `series_id` = '.$series_id.'';
//                        $models = $pdo->query($sql);
//                        foreach($models as $k3 => $model){
//
//                            $serie['model'][$k2]['modellist'][] = $model;
//                        }
//                    }
//
//                    $brandInfo['series'][$k]['serielist'][] = $serie;
//
//                }
//
//
//            }


        //$brandInfo = json_encode($brandInfo);

//            RedisDb::setValue($key, serialize($brandInfo));
//        }
//        else{
//
//            $brandInfo = unserialize($brandInfo);
//
//        }

        // $brandInfo = json_decode($brandInfo, true);

        $response = array();
        $response['series_list'] = $info;

        $this->send($response);


    }

    public  function  modelAction($series_id){

        $sql = 'SELECT `model_id` , `model_name` FROM `bibi_car_series_model` WHERE  `series_id` = '.$series_id.'';

        $pdo = new PdoDb;

        $info = array();

        $models = $pdo->query($sql);

        foreach($models as $k => $model){

            $model['series_id'] = $series_id;
            $info[]  = $model;

        }

        $response = array();
        $response['model_list'] = $info;

        $this->send($info);
    }

}