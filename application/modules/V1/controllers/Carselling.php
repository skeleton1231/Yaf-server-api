<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午6:55
 */
class CarsellingController extends ApiYafControllerAbstract
{

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
            'contact_name',
            'contact_address',
            'maintain',
            'is_transfer',
            'insurance_due_time',
            'check_expiration_time',
            'files_id',
            'car_intro',
            'exchange_time'
        );

        $this->required_fields = array_merge($this->required_fields, $required_fields);

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $properties = $data;
        $properties['car_type'] = 1;
        unset($properties['device_identifier']);
        unset($properties['session_id']);
        unset($properties['files_id']);

        $files_id = $data['files_id'];

        $time = time();
        $properties['created'] = $time;
        $properties['updated'] = $time;

        $cs = new CarSellingModel();
        $cs->properties = $properties;
        $carId = $cs->CreateM();

        if ($carId) {

            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($carId, $files_id, ITEM_TYPE_CAR);

            $carInfo = $cs->GetCarInfoById($carId);

            $this->send($carInfo);

        } else {

            $this->send_error(CAR_ADDED_ERROR);

        }

    }


}