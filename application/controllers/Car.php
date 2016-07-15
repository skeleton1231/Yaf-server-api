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


        $pdo = new PdoDb;

        $sql = 'SELECT * FROM `bibi_car_brand_list` WHERE `brand_id` = ' . $brand_id . '';
        $brand = @$pdo->query($sql)[0];

        $brandInfo = array();


        $brandInfo['series'] = array();

        $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name` , `makename`  FROM `bibi_car_brand_series` WHERE `brand_id` = ' . $brand_id . '  ORDER BY `makename` , `series_name` ASC';

        $series = $pdo->query($sql);

        $info = array();

        foreach($series as $serie){

            $serie['brand_id'] = $brand['brand_id'];
            $serie['series_name'] .= ' '.$serie["makename"].'';
            $info[] = $serie;
        }

        $response = array();
        $response['series_list'] = $info;

        $this->send($response);


    }

    public  function  modelAction($series_id){

        $sql = 'SELECT `model_id` , `model_name` FROM `bibi_car_series_model` WHERE  `series_id` = '.$series_id.' ORDER BY `model_name` DESC';

        $pdo = new PdoDb;

        $info = array();

        $models = $pdo->query($sql);

        foreach($models as $k => $model){

            $model['series_id'] = $series_id;
            $info[]  = $model;

        }

        $response = array();
        $response['model_list'] = $info;

        $this->send($response);
    }

}