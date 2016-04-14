<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/12
 * Time: 下午8:48
 */


class FriendshipController extends ApiYafControllerAbstract {


    public function createAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','user_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $friendShipM = new FriendShipModel();

        $time = time();

        $friendShip = $friendShipM->getMyFriendShip($userId, $data['user_id']);

        if(!$friendShip){

            $friendShipM->friend_id = $data['user_id'];
            $friendShipM->user_id   = $userId;
            $friendShipM->created   = $time;

            $friendShipM->saveProperties();
            $friendShipM->CreateM();

        }

        $friendShip = $friendShipM->getMyFriendShip($userId, $data['user_id']);

        $this->send($friendShip);

    }

    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','user_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $friendShipM = new FriendShipModel();

        $friendShip = $friendShipM->getMyFriendShip($userId, $data['user_id']);

        $friendShipM->deleteFriendShip($friendShip['friendship_id']);

        $this->send($friendShipM);


    }

    public function listAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','page','action'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $friendShipM = new FriendShipModel();

        $time = time();

        $data['page'] = $data['page'] ? ($data['page']+1) : 1;

        $data['action'] = isset($data['action']) ? $data['action'] : 1;

        //action 1 : 为关注列表 2: 粉丝列表
        if($data['action'] == 1){

            $friendShips = $friendShipM->getFriendShipToMe($userId, 0, $data['page']);

        }
        else{
            $friendShips = $friendShipM->getMyFriendShip($userId, 0, $data['page']);

        }

        $response = $friendShips;

        $this->send($response);


    }




}