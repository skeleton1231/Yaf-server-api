<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/6/18
 * Time: 01:27
 */
class DreamCarController extends ApiYafControllerAbstract
{

    public function createAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','series_id', 'brand_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $dreamCarModel = new DreamCarModel();

        $insert = array();
        $insert['user_id'] = $userId;
        $insert['brand_id'] = $data['brand_id'];
        $insert['series_id'] = $data['series_id'];

        $dc_id = $dreamCarModel->add($insert);

        $brandModel = new BrandModel();

        $response = array();

        $response['dc_id'] = $dc_id;
        $response['brand_info']  = $brandModel->getBrandModel($insert['brand_id']);
        $response['series_info'] = $brandModel->getSeriesModel($insert['brand_id'],$insert['series_id']);

        $this->send($response);

    }

    public function updateAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','series_id', 'brand_id','dc_id')
        );

        $data = $this->get_request_data();

        $this->userAuth($data);

        $dreamCarModel = new DreamCarModel();

        $dreamCarModel->where(array('dc_id'=>$data['dc_id']))->save(array(
            'brand_id'=>$data['brand_id'],
            'series_id'=>$data['series_id'],
        ));

        $brandModel = new BrandModel();

        $response = array();

        $response['dc_id'] = $data['dc_id'];
        $response['brand_info']  = $brandModel->getBrandModel($data['brand_id']);
        $response['series_info'] = $brandModel->getSeriesModel($data['brand_id'],$data['series_id']);

        $this->send($response);

    }

    public function listAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','user_id')
        );

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);

        $userId = $data['user_id'];

        $dreamCarModel = new DreamCarModel();
        $dreamCars = $dreamCarModel->where(array('user_id'=>$userId))->select();

        $brandModel = new BrandModel();

        $items = array();

        foreach ($dreamCars as $k => $dreamCar){

            $item = array();
            $item['dc_id'] = $dreamCar['dc_id'];
            $item['brand_info']  = $brandModel->getBrandModel($dreamCar['brand_id']);
            $item['series_info'] = $brandModel->getSeriesModel($dreamCar['brand_id'],$dreamCar['series_id']);
            $items[] = $item;
        }

        $this->send($items);
    }


}