<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/7
 * Time: 下午9:09
 */

class CommentController extends ApiYafControllerAbstract {


    public function createAction(){

        $time = time();

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id','content')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $commentM = new CommentModel();
        $commentM->currentUser = $userId;
        $feedM = new FeedModel();
        $feedM->currentUser = $userId;

        $replyId = @$data['reply_id'] ? $data['reply_id'] : 0;


        if($replyId){

            $replyComment = $commentM->getComment($replyId, $data['feed_id']);

            $toId = isset($replyComment['from_user_info']['user_id']) ? $replyComment['from_user_info']['user_id'] : 0;

            if($userId == $toId){

                $this->send_error(COMMENT_USER_ERROR);
            }
        }
        else{

            $feed = $feedM->getFeeds($data['feed_id']);
            $toId = $feed['post_user_info']['user_id'];
        }

        $commentM = new CommentModel();
        $commentM->user_id = $userId;
        $commentM->feed_id = $data['feed_id'];
        $commentM->content = $data['content'];
        $commentM->from_id = $userId;
        $commentM->to_id   = $toId;
        $commentM->reply_id = $replyId;
        $commentM->created = $time;

        $commentM->saveProperties();

        $commentId = $commentM->CreateM();

        $feedM->updateCommentNum($data['feed_id']);

        $comment = $commentM->getComment($commentId , $data['feed_id']);


//        $feedM = new FeedModel();
//        $feedM->currentUser = $userId;
//        $feed = $feedM->getFeeds($data['feed_id']);

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

    public function tomeAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id', 'page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $userId = $this->userAuth($data);

        $commentM = new CommentModel();
        $comments = $commentM->getComment(0,0,$data['page'],$userId);

        $this->send($comments);

    }

    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','comment_id'));


        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $feed_id = @$data['feed_id'];
        $comment_id = @$data['comment_id'];

        if($comment_id){

            $commentM = new CommentModel();

            $commentM->currentUser = $userId;
            $commentM->feed_id = $feed_id;
            $commentM->comment_id = $comment_id;

            $commentM->from_id = $userId;

            $commentM->deleteComment();
        }
        else{

            $feedM = new FeedModel();
            $feedM->currentUser = $userId;

            $feedM->deleteFeed($feed_id);
        }


        $this->send();



    }
}