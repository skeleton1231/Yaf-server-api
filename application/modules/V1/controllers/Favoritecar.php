<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/6
 * Time: 上午12:01
 */

class FavoritecarController extends ApiYafControllerAbstract{


    public function createAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $favCarM = new FavoriteCarModel();
        $favCarM->user_id = $userId;
        $favCarM->car_id  = $data['car_id'];
        $favCar = $favCarM->get();

        if($favCar){

            $this->send_error(FAVORITE_CAR_ALREADY);
        }

        $carM = new CarSellingModel();
        $carMTable = $carM::$table;
        $carM::$visit_user_id = $userId;
        $car = $carM->GetCarInfoById($data['car_id']);
        $favNum = $car['fav_num'] + 1;


        if(!$car){

            $this->send_error(CAR_NOT_EXIST);
        }

        $properties = array();
        $properties['user_id'] = $userId;
        $properties['car_id']  = $data['car_id'];
        $created = time();
        $properties['created'] = $created;


        $favCarM->properties = $properties;

        $id = $favCarM->CreateM();

        if(!$id){
            $this->send_error(FAVORITE_FAIL);
        }
        else{

            $carM->updateByPrimaryKey($carMTable, array('hash'=>$car['car_id']),array('fav_num'=>$favNum));

            $response = array();
            $response['favorite_id'] = $id;
            $response['car_info'] = $car;

            $key = 'favorite_'.$userId.'_'.$data['car_id'].'';

            RedisDb::setValue($key, $id);

            $this->send($response);
        }


    }


    public function deleteAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','favorite_id','car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $favCarM = new FavoriteCarModel();
        $favCarM->favorite_id = $data['favorite_id'];
        $favCarM->car_id      = $data['car_id'];
        $favCarM->user_id     = $userId;
        $favCarM->delete();

        $response = array();

        $this->send($response);

    }


    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $carM = new CarSellingModel();

        $data = $this->get_request_data();

        $data['page'] = $data['page'] ? $data['page'] : 1;
        $carM->page = $data['page'];

        $userId = $this->userAuth($data);

        $carM::$visit_user_id = $userId;

        $list = $carM->getUserFavoriteCar($userId);

        $response = $list;

        $this->send($response);

    }



}