<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/11
 * Time: 下午12:21
 */

class LikeController extends ApiYafControllerAbstract {

    public function createAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $time = time();

        $likeM = new LikeModel();

        $like = $likeM->getLike($userId, $data['feed_id']);

        if(!$like){

            $likeM = new LikeModel();
            $likeM->user_id = $userId;
            $likeM->feed_id = $data['feed_id'];
            $likeM->created = $time;

            $likeM->saveProperties();
            $likeM->CreateM();

            $feedM = new FeedModel();
            $feedM->updateLikeNum($data['feed_id']);


            $key = 'like_'.$data['feed_id'].'_'.$userId.'';

            RedisDb::setValue($key,1);

        }


        $like = $likeM->getLike($userId, $data['feed_id']);
//
//        $feedM = new FeedModel();
//        $feed = $feedM->getFeeds($data['feed_id']);

        $this->send($like['user_info']);

    }

    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;


        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $likeM = new LikeModel();
        $likeM->currentUser = $userId;
        $likes = $likeM->getLike(0,$data['feed_id'],$data['page']);


        $this->send($likes);

    }

    public function tomeAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id', 'page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $userId = $this->userAuth($data);

        $likeM = new LikeModel();
        $likeM->currentUser = $userId;

        $likes = $likeM->getLikeToMe($data['page']);

        $this->send($likes);
    }

    public function deleteAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $likeM = new LikeModel();

        $like = $likeM->getLike($userId, $data['feed_id']);

        $likeM->deleteLike($data['feed_id'] , $userId);
//
//        $feedM = new FeedModel();
//        $feed = $feedM->getFeeds($data['feed_id']);

        $this->send($like['user_info']);

    }


}