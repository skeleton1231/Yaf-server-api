<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/12
 * Time: 下午12:30
 */
class PublishcarController extends ApiYafControllerAbstract
{


    public $car_info_fields = array(

        'brand_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'series_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'model_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'price' => CAR_PRICE_ERROR,
        'board_time' => CAR_BOARD_TIME_ERROR,
        'mileage' => CAR_MILEAGE_ERROR,
        'car_status' => CAR_STATUS_ERROR,
        'city_id' => CAR_CITY_ERROR,
        'car_color' => CAR_COLOR_ERROR,
        'car_no' => CAR_NO_ERROR,
        'contact_name' => CAR_CONTACT_NAME_ERROR,
        'contact_address' => CAR_CONTACT_ADDRESS_ERROR,
        'maintain' => CAR_MAINTAIN_ERROR,
        'is_transfer' => CAR_IS_TRANSFER_ERROR,
        'insurance_due_time' => CAR_INSURANCE_DUE_TIME_ERROR,
        'check_expiration_time' => CAR_EXPIRATION_TIME_ERROR,
        'car_intro' => CAR_INTRO_ERROR,
        'exchange_time' => CAR_EXCHANGE_TIME_ERROR,

    );

    public $vin_fields = array('vin_no', 'vin_file');

    public function publishProgress($data,$userId,$cs,$car_type=PLATFORM_USER_SELLING_CAR,$act='insert'){

//        if ($data['action']) {
//
//            $this->submitCheck($data, $this->car_info_fields);
//
//        }

        if (!$data['vin_no'] && !$data['vin_file'] && $act == 'insert') {

            $this->send_error(CAR_DRIVE_INFO_ERROR);
        }


        $properties = $data;
        $properties['car_type'] = $car_type;
        unset($properties['device_identifier']);
        unset($properties['session_id']);
        unset($properties['files_id']);
        unset($properties['files_type']);

        $properties['user_id'] = $userId;

        $bm = new BrandModel();
        $brandM = $bm->getBrandModel($data['brand_id']);
        $seriesM = $bm->getSeriesModel($data['brand_id'], $data['series_id']);
        $modelM = $bm->getModelModel($data['series_id'], $data['model_id']);


        if (!is_array($brandM)) {

            $this->send_error(CAR_BRAND_ERROR);
        }

        if (!is_array($seriesM)) {
            $this->send_error(CAR_SERIES_ERROR);
        }

        if (!is_array($modelM)) {

            $this->send_error(CAR_MODEL_ERROR);
        }


        $properties['car_name'] = $brandM['brand_name'] . ' ' . $seriesM['series_name'] . ' ' . $modelM['model_name'];
        $properties['car_name'] = trim($properties['car_name']);


//        if (isset($properties['vin_file'])) {
//
//            $vinFile = new FileModel();
//            $vinFile = $vinFile->Get($properties['vin_file']);
//            $properties['vin_file'] = $vinFile;
//
//        }

//        if (isset($properties['vin_no'])) {
//
//            $vinNo = $properties['vin_no'];
//        }

//        if (!$vinNo && !$vinFile && $act == 'insert') {
//
//            $this->send_error(CAR_DRIVE_INFO_ERROR);
//        }


        $filesInfo = $cs->dealFilesWithString($data['files_id'], $data['files_type']);

        $time = time();
        if($act == 'insert'){

            $properties['created'] = $time;
            $properties['updated'] = $time;
        }
        else{

            $properties['updated'] = $time;
        }

        $properties['files'] = $filesInfo ? serialize($filesInfo) : '';

        $properties['verify_status'] = $car_type == PLATFORM_USER_SELLING_CAR ? CAR_VERIFYING : CAR_NOT_AUTH;
        unset($properties['action']);

        return $properties;
    }

    public function updateAction()
    {

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','files_id', 'files_type','car_id','car_type')
            //array_keys($this->car_info_fields),
            //$this->vin_fields
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $cs = new CarSellingModel();

        $properties = $this->publishProgress($data, $userId, $cs, $data['car_type'],'update');

        unset($properties['car_id']);
        unset($properties['created']);
        unset($properties['verify_status']);

        $cs->properties = $properties;

        $rs = $cs->updateByPrimaryKey($cs::$table,array('hash'=>$data['car_id']),$properties);

        if($rs){

            $carInfo = $cs->GetCarInfoById($data['car_id']);

            $ifr = new ItemFilesRelationModel();

            $ifr->DeleteBatch($carInfo['car_id'], ITEM_TYPE_CAR);
            $ifr->CreateBatch($carInfo['car_id'], $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $response['car_info'] = $carInfo;

            $this->send($response);

        } else {

            $this->send_error(CAR_ADDED_ERROR);

        }



    }

    public function createAction()
    {

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'action', 'files_id', 'files_type'),
            array_keys($this->car_info_fields),
            $this->vin_fields
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $cs = new CarSellingModel();

        $properties = $this->publishProgress($data, $userId, $cs);

        $properties['hash'] = uniqid();

        unset($properties['car_id']);

        $cs->properties = $properties;

        $carId = $cs->CreateM();

        if ($carId) {

            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($carId, $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $carInfo = $cs->GetCarInfoById($properties['hash']);

            $response['car_info'] = $carInfo;

            $this->send($response);

        } else {

            $this->send_error(CAR_ADDED_ERROR);

        }

    }

    private function submitCheck($data, $car_info_fields)
    {

        foreach ($car_info_fields as $k => $car_info_error) {

//            echo $k;
//            echo "\n";
//            var_dump($data[$k]);
//            echo mb_strlen($data[$k]);
//            echo "\n";
            if(mb_strlen($data[$k]) == 0){

                $this->send_error($car_info_error);
            }
        }

    }

    public function listAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $carM = new CarSellingModel();

        $data = $this->get_request_data();

        $page = $data['page'] ? ($data['page']+1) : 1;

        $carM->page = $page;

        $userId = $this->userAuth($data);

        $objId = $this->getAccessId($data, $userId);

        $carM->currentUser = $objId;

        $list = $carM->getUserPublishCar($objId);

        $response = $list;

        $this->send($response);
    }
}