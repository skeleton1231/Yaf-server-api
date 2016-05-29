<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/19
 * Time: 下午10:39
 */

class VisitcarController extends ApiYafControllerAbstract {


    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $carM = new CarSellingModel();

        $data = $this->get_request_data();

        $data['page'] = $data['page'] ? $data['page'] : 1;
        $carM->page = $data['page'];

        $userId = $this->userAuth($data);

        $carM->currentUser = $userId;

        $list = $carM->getUserVisitCar($userId);

        $response = $list;

        $this->send($response);

    }

} 