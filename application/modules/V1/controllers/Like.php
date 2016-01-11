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

            $likeM->user_id = $userId;
            $likeM->feed_id = $data['feed_id'];
            $likeM->created = $time;

            $likeM->saveProperties();
            $likeM->CreateM();
        }

        $feedM = new FeedModel();
        $feed  = $feedM->getFeeds($data['feed_id']);

        $this->send($feed);

    }

    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;


        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $likeM = new LikeModel();
        $likes = $likeM->getLike(0,$data['feed_id'],$data['page']);

        $this->send($likes);

    }

    public function deleteAction(){


    }


}