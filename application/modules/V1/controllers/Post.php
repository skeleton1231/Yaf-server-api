<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */

class PostController extends  ApiYafControllerAbstract {


    public function createAction(){


        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'post_content', 'files_id','lat','lng')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $feedM = new FeedModel();

        $postFiles = $feedM->serializePostFiles($data['files_id']);
        $time = time();

        $data['lat'] = isset($data['lat']) ? $data['lat'] : 0.00;
        $data['lng'] = isset($data['lng']) ? $data['lng'] : 0.00;

        $feedM->user_id = $userId;
        $feedM->post_content = $data['post_content'];
        $feedM->post_files = $postFiles;
        $feedM->lat = $data['lat'];
        $feedM->lng = $data['lng'];
        $feedM->created = $time;
        $feedM->updated = $time;

        $feedM->saveProperties();

        $feedId = $feedM->CreateM();

        if($feedId){

            $postM = new PostModel();
            $postM->post_id = $feedId;
            $postM->user_id = $userId;
            $postM->post_content = $feedM->post_content;
            $postM->post_files = $feedM->post_files;
            $postM->lat = $feedM->lat;
            $postM->lng = $feedM->lng;
            $postM->created = $feedM->created;
            $postM->updated = $feedM->updated;

            $postM->saveProperties();
            $postM->CreateM();

        }


        $feedInfo = $feedM->getFeeds($feedId);


        $this->send($feedInfo);


    }


    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','post_type','page'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $data['post_type'] = $data['post_type'] ? $data['post_type'] : 1;
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $feedM = new FeedModel();

        $response = $feedM->getFeeds(0,$data['post_type'],$data['page']);

        $this->send($response);


    }


    public function indexAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);


        $feedM = new FeedModel();

        $response = $feedM->getFeeds($data['feed_id']);

        $this->send($response);

    }
}