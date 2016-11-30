<?php
/**
 * Created by sublime.
 * User: jpjy
 * Date: 15/10/19
 * Time: 上午11:50
 * note: 文章管理
 */
class ArticleController extends ApiYafControllerAbstract{
     //编辑文章
     public function createAction(){

            $this->required_fields = array_merge(
            $this->required_fields,
            array(
                'sort_id',
                'title',
                'full_title',
                'keyword',
                'content',
            ));

	        $data = $this->get_request_data();

	        $userId = $this->userAuth($data);
	        
	        if (!$data['sort_id'] && !$data['title'] && !$data['full_title'] && !$data['keyword'] && $data['content']) {

             $this->send_error(CAR_DRIVE_INFO_ERROR);
            }        
            
            $ArticleM= new ArticleModel();
            $ArticleContentM=new ArticleCotentModel();
            $time=time();
	        $ArticleM->sort_id       = $data['sort_id'];
	        $ArticleM->title         = $data['title'];
	        $ArticleM->full_title    = $data['full_title'];
	        $ArticleM->author_id     = $userId;;
	        $ArticleM->copyfrom      = $data['copyfrom'];
	        $ArticleM->http_url      = $data['http_url'];
	        $ArticleM->keyword       = $data['keyword'];
	        $ArticleM->created       = $time;
	        $ArticleM->update_time   = $time;
            $ArticleM->saveProperties();
            $ArticleId = $ActicleM->CreateM();

            $ArticleContentM->article_id =$ArticleId;
            $ArticleContentM->content    =$data['content'];
            $ArticleContentM->saveProperties();
            $ArticleId = $ArticleContentM->CreateM();
     }

     //文章详情
     public function indexAction(){
        
        /*
        $this->required_fields = array_merge($this->required_fields, array('session_id', 'article_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }
        */
        $ArticleId=1;
        $userId=389;
        $ArticleModel = new ArticleModel();

        $ArticleT = $ArticleModel::$table;


        //$ArticleId = $data['article_id'];

       // $ArticleModel->currentUser = $userId;

        $ArticleInfo = $ArticleModel->GetArticleInfoById($ArticleId,$userId);
        
        
        $response['Article_info'] = $ArticleInfo;

        $visitArticleM = new ArticleVisitModel();

        $visitArticleM->article_id = $ArticleId;
        
        $visitArticleM->user_id    = $userId;
        $id = $visitArticleM->get();
       

        if(!$id){
            $properties = array();
            $properties['created'] = time();
            $properties['user_id'] = $userId;
            $properties['article_id']  = $ArticleId;

            $ArticleModel->updateByPrimaryKey(
                $ArticleT,
                array('article_id'=>$ArticleId),
                array('hits'=>($ArticleInfo['hits']+1))
            );
            $visitArticleM->insert($visitArticleM->tableName, $properties);
        }
        
        /*
        $title = is_array($carInfo['user_info']) ?
                    $carInfo['user_info']['profile']['nickname'] . '的' . $carInfo['car_name']
                    : $carInfo['car_name'];

        $response['share_title'] = $title;
        //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
        $response['share_url'] = 'http://share.bibicar.cn/car/'.base64_encode($data['device_identifier']).'/'.$articleId;
        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
        $response['share_img'] = isset($carInfo['files'][0]) ? $carInfo['files'][0]['file_url'] : '';
        */
        $this->send($response);


     }

       //获取一级评论
       public function getcommentAction(){
        
        /*
        $this->required_fields = array_merge($this->required_fields, array('session_id', 'article_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }
        */
        $ArticleCommentM = new ArticleCommentModel();
        $ArticleId=1;
        $userId=389;

        //$ArticleId = $data['article_id'];

       // $ArticleModel->currentUser = $userId;

        $Comment = $ArticleCommentM->getComment($ArticleId,1);
        
        
        $response['comment_list'] = $Comment;
       
        
        $this->send($response);


     }
     //获取一级评论下的二级评论
     public function getcomtocomAction(){
         $ArticleCommentM = new ArticleCommentModel();
         $ArticleId=1;
         $CommentId=3;
         $userId=389;
         $page=1;
         $Comment = $ArticleCommentM->getCommentToComment($CommentId,$ArticleId,$page); 
         $this->send($Comment);
         print_r($Comment); 
     }
     //文章列表
     public function listAction(){
        /*
        $this->required_fields = array_merge($this->required_fields,array('session_id','post_type','page'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);
         */
        $articleM = new ArticleModel();
        
        $userId=389;
        $data['post_type']=1;
        $data['page']     =1;
        /*
        $data['post_type'] = $data['post_type'] ? $data['post_type'] : 1;

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        */
        $articleM->currentUser = $userId;
      
        $response = $articleM->getArticles($data['post_type'],$userId,$data['page']);

        $this->send($response);

     }
     //文章点赞
     public function likelistAction(){
         /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'article_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
        $data['article_id']=1;
        $userId=389;
        $time = time();

        $ArticleLikeM = new ArticleLikeModel();

        $like = $ArticleLikeM->getLike($userId,$data['article_id']);
        
        if(!$like){

            $ArticleLikeM = new ArticleLikeModel();
            $ArticleLikeM ->user_id     = $userId;
            $ArticleLikeM ->article_id  = $data['article_id'];
            $ArticleLikeM ->created     = $time;

            $ArticleLikeM ->saveProperties();
            $id =  $ArticleLikeM ->CreateM();

            if($id){

                $ArticleLikeM->updateLikeNum($data['article_id']);

                $key = 'likearticle_'.$data['article_id'].'_'.$userId.'';

                RedisDb::setValue($key,1);
           
                $like =$ArticleLikeM ->getLike(0, $data['article_id']);
                

                $this->send($like);
            }


        }
        else{
            
            $this->send_error(FEED_HAS_LIKED);
        }

     }
     
      public function commentcreateAction(){
        /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'article_id','content')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
        $data['article_id']=1;
        $data['content']   ="123dddiii";
        $data['reply_id']  =7;
        $replyId = @$data['reply_id'] ? $data['reply_id'] : 0;
        $userId=542;
        $time = time();
        if($replyId){
            $ArticleCommentM = new ArticleCommentModel();
            $replyComment = $ArticleCommentM->getCommentdetail($replyId,$data['article_id']);
            
            $toId = isset($replyComment['from_user']['user_id']) ? $replyComment['from_user']['user_id'] : 0;
             
            if($userId == $toId){

                $this->send_error(COMMENT_USER_ERROR);
            }
        }
        else{

                $ArticleModel = new ArticleModel();

                $ArticleId = $data['article_id'];

                $ArticleModel->currentUser = $userId;

                $ArticleInfo = $ArticleModel->GetArticleInfoById($ArticleId,$userId);
                $toId        = $ArticleInfo["author_info"]['user_id'];
        }


        $commentM = new ArticleCommentModel();
        $commentM->from_id    = $userId;
        $commentM->article_id = $data['article_id'];
        $commentM->content    = $data['content'];
        $commentM->to_id      = $toId;
        $commentM->reply_id   = $replyId;
        $commentM->created    = $time;

        $commentM->saveProperties();

        $commentId =$commentM->CreateM();

        $result=$commentM->updateCommentNum($data['article_id']);

        if($replyId){
            $comment = $commentM->getCommentToComment($replyId,$data['article_id']);
        }else{
            $comment = $commentM->getComment($data['article_id'],1);
        }
      

        if($userId != $toId){

            $mh = new MessageHelper;

            $nickname = $comment['from_user']['nickname'];
            $content = ''.$nickname.'评论了你';
            $mh->commentNotify($toId, $content);
        }

        $this->send($comment);



    }

     public function commentdeleteAction(){
         /*
        $this->required_fields = array_merge($this->required_fields,array('session_id','article_id','comment_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $article_id = @$data['article_id'];
        $comment_id = @$data['comment_id'];
       */
        $article_id=¨¥˙˙˜∆µµ≤≤≤∆˜∫∫©ƒœå∑≈çß∆∂µßµ;
        $comment_id=;
        $userId    =;
        $commentM = new ArticleCommentModel();

        $commentM->currentUser = $userId;
        $commentM->article_id = $article_id;
        $commentM->comment_id = $comment_id;

        $commentM->from_id = $userId;

        $commentM->deleteComment();

        $commentM = new ArticleCommentModel();
        $commentM->updateCommentNum($date['article'],"miuns");
    
        $this->send();



    }

    public function commentlikecreateAction(){

         /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'article_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
       
        $data['article_id']=1;
        $data['comment_id']=3;
        $userId=389;
        $time = time();

        $ArticleCommentLikeM = new ArticleCommentLikeModel();

        $like = $ArticleCommentLikeM ->getLike($data['article_id'],$data['comment_id'],$userId);
        
        if(!$like){

            $ArticleLikeM = new ArticleCommentLikeModel();
            $ArticleLikeM ->user_id     = $userId;
            $ArticleLikeM ->article_id  = $data['article_id'];
            $ArticleLikeM ->comment_id  = $data['comment_id'];
            $ArticleLikeM ->created     = $time;

            $ArticleLikeM ->saveProperties();
            $id =  $ArticleLikeM ->CreateM();

            if($id){
                $ArticleLikeM = new ArticleCommentLikeModel();
                $ArticleLikeM->updateLikeNum($data['article_id'],$data['comment_id']);

                $key = 'likearticlecomment_'.$data['article_id'].'_'.$userId.'_'.$data['comment_id'].'';

                RedisDb::setValue($key,1);
           
                $like =$ArticleLikeM ->getLike($data['article_id'],$data['comment_id'] );
                

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
            array('session_id', 'article_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
       
        /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'article_id')
        );

        $data = $this->get_request_data();
        
        $userId = $this->userAuth($data);
        */
       $userId  =389;
       $data['article_id']=1;
       $data['comment_id']=3;
      
        $ArticleLikeM = new ArticleCommentLikeModel();

        $like = $ArticleLikeM->getLike($data['article_id'],$data['comment_id'],$userId);

        $rs = $ArticleLikeM->deleteLike($data['article_id'],$userId,$data['comment_id']);

        if($rs){

            $ArticleLikeM = new ArticleCommentLikeModel();;

            $ArticleLikeM->updateLikeNum($data['article_id'],$data['comment_id'],'minus');

            $like = $ArticleLikeM->getLike($data['article_id'],$data['comment_id']);

            $this->send($like);
        }
        else{

            $this->send_error(FEED_LIKE_HAS_CANCLED);
        }


    }



     public function likecreateAction(){
        /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'article_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
        $data['article_id']=1;
        $userId=389;
        $time = time();

        $ArticleLikeM = new ArticleLikeModel();

        $like = $ArticleLikeM->getLike($userId,$data['article_id']);
        
        if(!$like){

            $ArticleLikeM = new ArticleLikeModel();
            $ArticleLikeM ->user_id     = $userId;
            $ArticleLikeM ->article_id  = $data['article_id'];
            $ArticleLikeM ->created     = $time;

            $ArticleLikeM ->saveProperties();
            $id =  $ArticleLikeM ->CreateM();

            if($id){

                $ArticleLikeM->updateLikeNum($data['article_id']);

                $key = 'likearticle_'.$data['article_id'].'_'.$userId.'';

                RedisDb::setValue($key,1);
           
                $like =$ArticleLikeM ->getLike(0, $data['article_id']);
                

                $this->send($like);
            }


        }
        else{
            
            $this->send_error(FEED_HAS_LIKED);
        }




    }


    public function likedeleteAction(){
        /*
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'article_id')
        );

        $data = $this->get_request_data();
        
        $userId = $this->userAuth($data);
        */
       $userId  =389;
       $data['article_id']=1;
      
        $ArticleLikeM = new ArticleLikeModel();

        $like = $ArticleLikeM->getLike($userId, $data['article_id']);
       

        $rs = $ArticleLikeM->deleteLike($data['article_id'] , $userId);

        if($rs){

            $ArticleLikeM = new ArticleLikeModel();;

            $ArticleLikeM->updateLikeNum($data['article'], 'minus');

            $like = $ArticleLikeM->getLike(0, $data['article_id']);

            $this->send($like);
        }
        else{

            $this->send_error(FEED_LIKE_HAS_CANCLED);
        }


    }

    public function collectcreateAction(){
        //添加收藏的文章
        /*
        $this->required_fields = array_merge($this->required_fields,array('session_id','article_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
        $userId=389;
        $data['article_id']=2;

        $ArticleCollectM              = new ArticleCollectModel();
        $ArticleCollectM->user_id     = $userId;
        $ArticleCollectM->article_id  = $data['article_id'];
        $ArticleCollect = $ArticleCollectM->get();
        
        if($ArticleCollect){

            $this->send_error(FAVORITE_CAR_ALREADY);
        }

        $ArticleM = new ArticleModel();
        $aticleMTable = $ArticleM::$table;
        $ArticleM->currentUser = $userId;
        $article = $ArticleM->GetArticleInfoById($data['article_id']);
        $articleNum = $article['collect_num'] + 1;


        if(!$article){

            $this->send_error(CAR_NOT_EXIST);
        }


        $ArticleCollectM              = new ArticleCollectModel();
        $ArticleCollectM  ->user_id     = $userId;
        $ArticleCollectM  ->article_id  = $data['article_id'];
        $created=time();
        $ArticleCollectM  ->created     = $created;


       
        $ArticleCollectM ->saveProperties();
        
        $id = $ArticleCollectM->CreateM();
        
        if(!$id){
            $this->send_error(FAVORITE_FAIL);
        }
        else{

            $ArticleM->updateByPrimaryKey($aticleMTable , array('article_id'=>$data['article_id']),array('collect_num'=>$articleNum));

            $response = array();
            $response['collect_id'] = $id;
            $response['article_info'] = $article;

            $key = 'collect_'.$userId.'_'.$data['article_id'].'';

            RedisDb::setValue($key, $id);

            $this->send($response);
        }

 
    
    }


    public function collectdeleteAction(){
        //删除收藏文章
        $this->required_fields = array_merge($this->required_fields,array('session_id', 'article_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $ArticleCollectM     = new ArticleCollectModel();

        $ArticleCollectM->article_id      = $data['article_id'];
        $ArticleCollectM->user_id         = $userId;

        $key = 'collect_'.$ArticleCollectM->user_id.'_'.$ArticleCollectM->article_id.'';


        $CollectId = RedisDb::getValue($key);
        $ArticleCollectM->collect_id = $CollectId;
        $ArticleCollectM->delete();

        RedisDb::delValue($key);

        $response = array();

        $this->send($response);

    }


     public function collectlistAction(){
         //收藏的文章
         /*
        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $articleM = new ArticleModel();

        $data = $this->get_request_data();

        $data['page']   = $data['page'] ? $data['page'] : 1;
        $articleM->page = $data['page'];

        $userId = $this->userAuth($data);
         */
        $articleM = new ArticleModel();
        $articleM->page = 1;
        $userId=389;
        $articleM->currentUser = $userId;

        $list = $articleM->getUserCollectArticle($userId);

        $response = $list;

        $this->send($response);

    }


    public function visitlistAction(){

        //浏览过的文章
        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $articleM = new ArticleModel();

        $data = $this->get_request_data();

        $data['page'] = $data['page'] ? $data['page'] : 1;
        $articleM->page = $data['page'];

        $userId = $this->userAuth($data);
        
        $articleM->currentUser = $userId;

        $list = $articleM->getUserVisitArticle($userId);

        $response = $list;

        $this->send($response);
    }

      

}

