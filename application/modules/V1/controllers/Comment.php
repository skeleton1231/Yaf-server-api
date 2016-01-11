<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/7
 * Time: 下午9:09
 */

class CommentController extends ApiYafControllerAbstract {


    public function createAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id','content', 'to_id','reply_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $time = time();

        $commentM = new CommentModel();
        $commentM->user_id = $userId;
        $commentM->feed_id = $data['feed_id'];
        $commentM->content = $data['content'];
        $commentM->from_id = $userId;
        $commentM->to_id   = $data['to_id'];
        $commentM->reply_id = isset($data['reply_id']) ? $data['reply_id'] : 0;
        $commentM->created = $time;

        $commentM->saveProperties();
        $commentId = $commentM->CreateM();

        $comment = $commentM->getComment($commentId , $commentM->feed_id);

        $this->send($comment);

//        $feedM = new FeedModel();
//        $feed  = $feedM->getFeeds($data['feed_id']);
//
//        $this->send($feed);

    }

    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;


        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $commentM = new CommentModel();
        $comments = $commentM->getComment(0,$data['feed_id'],$data['page']);

        $this->send($comments);

    }
}