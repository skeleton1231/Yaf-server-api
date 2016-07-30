<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/11
 * Time: 下午12:21
 */

class FeedrelatedController extends ApiYafControllerAbstract {


   public function  indexAction(){
     $feedrelatedM = new FeedrelatedModel();
     $data['feed_id']='409';
     $data['user_id']='319';
     $data['share'] ='1';
     $data['likes'] ='1';
     $data['create_time']=time();
     $feedrelatedM->savefeed($data);
   }
    public function listAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $feedrelatedM = new FeedrelatedModel();
        $feedrelatedM->currentUser = $userId;
        $lists =$feedrelatedM->getFeeds($data);
        $this->send($feeds);

    }





}