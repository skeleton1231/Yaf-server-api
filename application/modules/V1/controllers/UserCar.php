<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午12:56
 */
class UsercarController extends ApiYafControllerAbstract
{


    public function listAction()
    {


    }


    public function CreateAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_no', 'vin_no', 'brand_id', 'series_id', 'model_id', 'files_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $userM = new UserModel();
        $userInfo = $userM->getInfoById($userId);

        if ($userInfo['has_car'] == 1) {
            $this->send_error(CAR_ALREADY_ADDED);
        }

        $carInfo = array();
        $carInfo['brand_id'] = $data['brand_id'];
        $carInfo['series_id'] = $data['series_id'];
        $carInfo['model_id'] = $data['model_id'];
        $carInfo['car_no'] = $data['car_no'];
        $carInfo['vin_no'] = $data['vin_no'];
        $carInfo['car_type'] = PLATFORM_USER_OWNER_CAR;
        $time = time();
        $carInfo['created'] = $time;
        $carInfo['updated'] = $time;
        $carInfo['user_id'] = $userId;
        $carInfo['status'] = CAR_NOT_AUTH;


        $cs = new CarSellingModel();
        $cs->properties = $carInfo;

        $id = $cs->CreateM();


        if ($id) {
            //更新用户状态
            $where = array('user_id' => $userId);
            $update = array('has_car' => 1);
            $userM->update($where, $update);

            //插入文件
            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($id, $data['files_id'], ITEM_TYPE_CAR);

            //获取多个文件信息
            $fileM = new FileModel();
            $files = $fileM->GetMultiple($data['files_id']);

//            //获取车辆名称
//            $brand = new BrandModel($carInfo);
//            $carName = $brand->getCarInfo();
//            $carInfo['car_name'] = $carName;
//            $carInfo['images'] = $files;
//            $carInfo['car_id'] = $id;

            $carInfo = $cs->GetCarInfoById($id);

            $this->send($carInfo);
        } else {

            $this->send_error(CAR_ADDED_ERROR);
        }


    }

    public function UpdateAction()
    {


    }

    public function DeleteAction()
    {


    }

    public function IndexAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $carModel = new CarSellingModel();

        $carId = $data['car_id'];

        $carInfo = $carModel->GetCarInfoById($carId);

        $this->send($carInfo);
    }
}