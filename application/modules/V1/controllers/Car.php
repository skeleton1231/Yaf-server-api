<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class CarController extends ApiYafControllerAbstract
{


    public function createAction()
    {

        $this->required_fields = array_merge(
            $this->required_fields,
            array(
                'session_id',
                'car_no',
                'brand_id',
                'city_id',
                'series_id',
                'files_id',
                'files_type'
            ));

        $this->optional_fields = array('model_id', 'vin_no', 'vin_file');

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $cs = new CarSellingModel();

        $properties = $data;
        unset($properties['device_identifier']);
        unset($properties['session_id']);
        unset($properties['files_id']);
        unset($properties['files_type']);


        $bm = new BrandModel();
        $brandM  = $bm->getBrandModel($data['brand_id']);
        $seriesM = $bm->getSeriesModel($data['brand_id'],$data['series_id']);
        $modelM  =  $bm->getModelModel($data['series_id'], $data['model_id']);


        if(!is_array($brandM)){

            $this->send_error(CAR_BRAND_ERROR);
        }

        if(!is_array($seriesM)){
            $this->send_error(CAR_SERIES_ERROR);
        }

        if(!is_array($modelM)){

            $this->send_error(CAR_MODEL_ERROR);
        }


        $properties['car_name'] = $brandM['brand_name'] . ' ' . $seriesM['series_name'] . ' ' . $modelM['model_name'];
        $properties['car_name'] = trim($properties['car_name']);



        if (isset($properties['vin_file'])) {

            $vinFile = new FileModel();
            $vinFile = $vinFile->Get($properties['vin_file']);
            $properties['vin_file'] = $vinFile;

        }

        $properties['car_type'] = PLATFORM_USER_OWNER_CAR;
        $time = time();
        $properties['created'] = $time;
        $properties['updated'] = $time;
        $properties['user_id'] = $userId;
        $properties['verify_status'] = CAR_NOT_AUTH;
        $properties['files'] = serialize($cs->dealFilesWithString($data['files_id'], $data['files_type']));
        $properties['hash'] = uniqid();

        $cs->properties = $properties;

        $id = $cs->CreateM();


        if ($id) {

            //插入文件
            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($id, $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $carInfo = $cs->GetCarInfoById($properties['hash']);

            $response['car_info'] = $carInfo;

            $this->send($response);

        } else {

            $this->send_error(CAR_ADDED_ERROR);
        }


    }

    public function updateAction()
    {


    }

    public function deleteAction()
    {


    }

    public function indexAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(isset($data['session_id'])){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }

        $carModel = new CarSellingModel();

        $carT = $carModel::$table;


        $carId = $data['car_id'];

        $carModel::$visit_user_id = $userId;

        $carInfo = $carModel->GetCarInfoById($carId);


        $response['car_info'] = $carInfo;


        //同款车
        $response['related_price_car_list'] = $carModel->relatedPriceCars($carId,$carInfo['price']);

        //同价车
        $response['related_style_car_list'] = $carModel->relatedStyleCars(
            $carId,
            $carInfo['brand_info']['brand_id'] ,
            $carInfo['series_info']['series_id']
        );


        $visitCarM = new VisitCarModel();
        $visitCarM->car_id  = $carId;
        $visitCarM->user_id = $userId;

        $id = $visitCarM->get();

        if(!$id){

            $properties = array();
            $properties['created'] = time();
            $properties['user_id'] = $userId;
            $properties['car_id']  = $carId;

            $carModel->updateByPrimaryKey(
                $carT,
                array('hash'=>$carId),
                array('visit_num'=>($carInfo['visit_num']+1))
            );

            $visitCarM->insert($visitCarM->tableName, $properties);
        }


        $this->send($response);


    }




    public function listAction(){


        $jsonData = require APPPATH .'/configs/JsonData.php';

        $this->optional_fields = array('keyword','order_id','brand_id','series_id');
        //$this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $data['order_id'] = $data['order_id'] ? $data['order_id'] : 0 ;
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        $data['brand_id'] = $data['brand_id'] ? $data['brand_id'] : 0 ;
        $data['series_id'] = $data['series_id'] ? $data['series_id'] : 0 ;


        $carM = new CarSellingModel();
        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 ';

        if($data['keyword']){
            $carM->keyword = $data['keyword'];
            $where .= ' AND t1.car_name LIKE "%'.$carM->keyword.'%" ';
        }

        if($data['brand_id']){

            $where .= ' AND t1.brand_id = '.$data['brand_id'].' ';
        }

        if($data['series_id']){

            $where .= ' AND t1.series_id = '.$data['series_id'].' ';
        }

        if($data['source'] == 1){

            $where .= ' AND t1.car_type = 1';
        }


        $carM->where = $where;

        if(isset($jsonData['order_info'][$data['order_id']])) {

           // $carM->order  = ' ORDER BY t1.car_type ASC , ';
            $carM->order = $jsonData['order_info'][$data['order_id']];

        }

        $carM->page = $data['page'];

        if(isset($data['session_id'])){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }


        $carM::$visit_user_id = $userId;

        $lists = $carM->getCarList();

        //$response = array();
        $response = $lists;
        $response['order_id'] = $data['order_id'];

        if($data['city_id']){

            $jsonData['city_info']['city_id'] = $data['city_id'];
            $jsonData['city_info']['city_lat'] = $data['city_lat'];
            $jsonData['city_info']['city_lng'] = $data['city_lng'];

        }

        $response['city_info'] = $jsonData['city_info'];
        $response['keyword']   = $data['keyword'];
        $bm = new BrandModel();
        $response['brand_info'] = $bm->getBrandModel($data['brand_id']);
        $response['series_info'] = $bm->getSeriesModel($data['brand_id'],$data['series_id']);

        $this->send($response);

    }




}
