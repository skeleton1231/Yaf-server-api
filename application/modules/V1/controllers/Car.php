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

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_no', 'brand_id', 'series_id', 'files_id', 'files_type'));

        $this->optional_fields = array('model_id', 'vin_no', 'vin_file');

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);


        $userM = new UserModel();
        $userInfo = $userM->getAllInfoById($userId);

        if ($userInfo['has_car'] == 1) {
            $this->send_error(CAR_ALREADY_ADDED);
        }

        $carInfo = array();
        $cs = new CarSellingModel();

        $properties = $data;
        unset($properties['device_identifier']);
        unset($properties['session_id']);
        unset($properties['files_id']);
        unset($properties['files_type']);

//        if(isset($properties['vin_file'])){
//
//            $vinFile = new FileModel();
//            $vinFile = $vinFile->Get($properties['vin_file']);
//            $properties['vin_file']    =  $vinFile;
//
//        }
//
//        if(isset($properties['vin_no'])){
//
//           $vinNo = $properties['vin_no'];
//        }
//
//        if(!$vinNo  && !$vinFile){
//
//            $this->send_error(CAR_DRIVE_INFO_ERROR);
//        }


        $properties['car_type'] = PLATFORM_USER_OWNER_CAR;
        $time = time();
        $properties['created'] = $time;
        $properties['updated'] = $time;
        $properties['user_id'] = $userId;
        $properties['status'] = CAR_NOT_AUTH;
        $properties['files'] = serialize($cs->dealFilesWithString($data['files_id'], $data['files_type']));


        $cs->properties = $carInfo;

        $id = $cs->CreateM();


        if ($id) {
            //更新用户状态
            $where = array('user_id' => $userId);
            $update = array('has_car' => 1);
            $userM->update($where, $update);

            //插入文件
            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($id, $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $carInfo = $cs->GetCarInfoById($id);

            $this->send($carInfo);
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

        $userId = $this->userAuth($data);

        $carModel = new CarSellingModel();

        $carId = $data['car_id'];

        $carInfo = $carModel->GetCarInfoById($carId);

        $this->send($carInfo);
    }

    public function publishAction()
    {

        $required_fields = array(

            'session_id',
            'brand_id',
            'series_id',
            'model_id',
            'price',
            'board_time',
            'mileage',
            'car_status',
            'city_id',
            'car_color',
            'car_no',
            'vin_no',
            'vin_file',
            'contact_name',
            'contact_address',
            'maintain',
            'is_transfer',
            'insurance_due_time',
            'check_expiration_time',
            'files_id',
            'files_type',
            'car_intro',
            'exchange_time'

        );

        $this->required_fields = array_merge($this->required_fields, $required_fields);

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        if (!$data['vin_no'] && !$data['drive_file']) {

            $this->send_error(CAR_DRIVE_INFO_ERROR);
        }

        $properties = $data;
        $properties['car_type'] = PLATFORM_USER_SELLING_CAR;
        unset($properties['device_identifier']);
        unset($properties['session_id']);
        unset($properties['files_id']);
        unset($properties['files_type']);


        if (isset($properties['vin_file'])) {

            $vinFile = new FileModel();
            $vinFile = $vinFile->Get($properties['vin_file']);
            $properties['vin_file'] = $vinFile;

        }

        if (isset($properties['vin_no'])) {

            $vinNo = $properties['vin_no'];
        }

        if (!$vinNo && !$vinFile) {

            $this->send_error(CAR_DRIVE_INFO_ERROR);
        }


        $cs = new CarSellingModel();

        $filesInfo = $cs->dealFilesWithString($data['files_id'], $data['files_type']);

        $time = time();
        $properties['created'] = $time;
        $properties['updated'] = $time;
        $properties['files'] = serialize($filesInfo);
        $properties['hash'] = uniqid();

        $cs->properties = $properties;
        $carId = $cs->CreateM();

        if ($carId) {

            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($carId, $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $carInfo = $cs->GetCarInfoById($properties['hash']);
            $this->send($carInfo);

        } else {

            $this->send_error(CAR_ADDED_ERROR);

        }

    }


}
