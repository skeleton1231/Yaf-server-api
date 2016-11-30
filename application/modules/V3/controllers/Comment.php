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
        $fatherId = @$data['father_id'] ? $data['father_id'] :0;


        //相关的人
        $feedrelatedM = new FeedrelatedModel();
        $data['feed_id']=$data['feed_id'];
        $data['user_id']=$userId;
        $data['comment'] ='1';
        $data['create_time']=time();
        $feedrelatedM->savefeed($data);


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
        $commentM->father_id =$fatherId;
        $commentM->created = $time;

        $commentM->saveProperties();

        $commentId = $commentM->CreateM();

        if($commentId){
             //sort 热度加分
            $userpro=new UserSortModel();
            $active="comment";
            $type_id=$commentId;
            $fromId=$userId;
            $toId=$toId;
            $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);
        }
        $feedM->updateCommentNum($data['feed_id']);

        $comment = $commentM->getComment($commentId , $data['feed_id']);

        if($userId != $toId){

            $mh = new MessageHelper;

            $nickname = $comment['from_user_info']['profile']['nickname'];
            $content = ''.$nickname.'评论了你';
            $mh->commentNotify($toId, $content);
        }

        $this->send($comment);


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
        $comments = $commentM->getCommenttome(0,0,$data['page'],$userId);

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

            $feedM = new FeedModel();
            $feedM->updateCommentNum($feed_id,"miuns");
        }
        else{

            $feedM = new FeedModel();
            $feedM->currentUser = $userId;

            $feedM->deleteFeed($feed_id);
        }


        $this->send();



    }
    

    public function commentlikecreateAction(){

         /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id','comment_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
       
        $data['feed_id']=1446;
        $data['comment_id']=695;
        $userId=389;
        $time = time();

        $CommentLikeM = new CommentLikeModel();

        $like = $CommentLikeM ->getLike($data['feed_id'],$data['comment_id'],$userId);
        
        if(!$like){

            $CommentLikeM = new CommentLikeModel();
            $CommentLikeM ->user_id     = $userId;
            $CommentLikeM ->feed_id  = $data['feed_id'];
            $CommentLikeM ->comment_id  = $data['comment_id'];
            $CommentLikeM ->created     = $time;

            $CommentLikeM ->saveProperties();
            $id =  $CommentLikeM ->CreateM();

            if($id){
                $CommentLikeM = new CommentLikeModel();
                $CommentLikeM->updateLikeNum($data['feed_id'],$data['comment_id']);

                $key = 'commentlike_'.$data['feed_id'].'_'.$userId.'_'.$data['comment_id'].'';

                RedisDb::setValue($key,1);
           
                $like =$CommentLikeM ->getLike($data['feed_id'],$data['comment_id'] );
                

                $this->send($like);
            }


        }
        else{
            
            $this->send_error(FEED_HAS_LIKED);
        }


    }


    public function commentlikedeleteAction(){
      /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id','comment_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
       
        /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id')
        );

        $data = $this->get_request_data();
        
        $userId = $this->userAuth($data);
      */
       $userId  =389;
       $data['feed_id']=1446;
       $data['comment_id']=695;
      
        $CommentLikeM = new CommentLikeModel();

        $like = $CommentLikeM->getLike($data['feed_id'],$data['comment_id'],$userId);

        $rs = $CommentLikeM->deleteLike($data['feed_id'],$userId,$data['comment_id']);

        if($rs){

            $CommentLikeM = new CommentLikeModel();;

            $CommentLikeM->updateLikeNum($data['feed_id'],$data['comment_id'],'minus');

            $like = $CommentLikeM->getLike($data['feed_id'],$data['comment_id']);

            $this->send($like);
        }
        else{

            $this->send_error(FEED_LIKE_HAS_CANCLED);
        }


    }



}