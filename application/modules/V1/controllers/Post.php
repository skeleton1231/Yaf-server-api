<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */

class PostController extends ApiYafControllerAbstract {


    public function createAction(){


        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'post_content','files_id')
        );

        $data = $this->get_request_data();


        if(!$data['files_id']){

            $this->send_error(POST_IMAGE_ERROR);
        }

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

        $userM = new UserModel();
        $userM->updateGeoById($userId, $data['lat'], $data['lng']);

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

        $feedM->currentUser = $userId;

        $response = $feedM->getFeeds(0,$data['post_type'],$data['page']);

        $this->send($response);


    }

    public function publishAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id', 'page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $data['post_type'] = 5;

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $feedM = new FeedModel();

        $objId = $this->getAccessId($data, $userId);

        $feedM->currentUser = $objId;

        $response = $feedM->getFeeds(0,$data['post_type'],$data['page']);

        $myFeeds = $response['feed_list'];

        foreach($myFeeds as $k => $myFeed){


            $myFeeds[$k]['post_content'] = strlen($myFeeds[$k]['post_content']) > 30
                                          ? mb_substr($myFeeds[$k]['post_content'], 0 , 30) . '...'
                                          : $myFeeds[$k]['post_content'];
        }


        $response['feed_list'] = $myFeeds;

        $this->send($response);

    }


    public function indexAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();

        $page = $data['page'] ? ($data['page']+1) : 1;

        $sess = new SessionModel();

        $userId = $sess->Get($data);

        $feedM = new FeedModel();

        $feedM->currentUser = $userId;

        $feed = $feedM->getFeeds($data['feed_id']);

        $comments = $feed['comment_list'];

        $feed['comment_list'] = array();

        foreach($comments as $k => $comment){

            if($k < 10){

                $feed['comment_list'][] = $comment;
            }

        }

        $commentList= array();

        $num = 10;
        $n = 0;
        $commentTotal = $feed['comment_num'];

        $getNum = $num*$page;

        if($getNum > 10){

            $start = ($page-1)*10;

            $end = $page*10-1;
            $end = $end > $commentTotal ? ($commentTotal-1) : $end;

            for($i=$start; $i<=$end; $i++){

                if(isset($comments[$i])){
                    $commentList[] = $comments[$i];

                }
            }

        }

        $count = count($feed['comment_list']) + count($commentList);

        $response = array();

        $response['feed_info'] = $feed;
        $response['comment_list'] = $commentList;

        $response['has_more'] = ($commentTotal - $count > 0) && ($getNum <= $commentTotal) ?  1 : 2;

        $this->send($response);

    }

    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $feedM = new FeedModel();
        $feedM->currentUser = $userId;

        $feedM->deleteFeed($data['feed_id']);

        $this->send();

    }
}