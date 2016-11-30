<?php
/**
 * Created by sublime.
 * User: jpjy
 * Date: 15/10/19
 * Time: 上午11:50
 * note: 文章管理
 */
class ArticleController extends ApiYafControllerAbstract
{

    //标签列表
    public function themeslistAction(){
       
        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();
        

        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }
       
        
          $type=1;
          $data['page']     = $data['page'] ? ($data['page']+1) : 1;
          $themeM=new ThemelistModel();
          $themes=$themeM->getThemes($type,$data['page'],$type);
          
          if($themes["theme_list"]){
             $items=array();
             $response['list']=array();
             $response['list']=$themes["theme_list"];
             $response['has_more']=$themes['has_more'];
             $response['total']=$themes['total'];
          }

          
       $theme=$themeM->getThemes(2);
       $tags=array();
       //print_r($theme);exit;
       $banners=array();
       if($theme["theme_list"]){
          foreach($theme["theme_list"] as $key =>$value){
               $banners[$key]['imgUrl']=$value["post_file"];
               $banners[$key]['appUrl']=$value["theme"];
               $banners[$key]['title']=$value["title"];
               $banners[$key]['link']="/topic/".$value["id"];
               if($value["is_skip"]==1){
                     $banners[$key]['type']="0";
                 if($value["id"]== 20){
                     $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&identity=".base64_encode($data['device_identifier']);
                 }
               }else{
                $banners[$key]['type']=(string)$value["id"];
               }
               

          }
       }

       
       // print_r($banners);exit;
        $response['banners'] = $banners;

        $this->send($response);
         
    }

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

     //文章列表
    public function listAction(){
            
            $this->required_fields = array_merge($this->required_fields,array('page'));

            $data = $this->get_request_data();
             if(@$data['session_id']){

                    $sess = new SessionModel();
                    $userId = $sess->Get($data);
                }
                else{

                    $userId = 0;
                }
             
            $feedM = new Feedv1Model();
            
            if(@$data['grade_id']){
                $type=2;
                $feedM->grade_id=$data['grade_id'];
            }else if(@$data['tags_id']){
                $type=3;
                $feedM->tags_id =$data['tags_id'];
            }else{
                $type=1;
            }

            /*
            $data['post_type'] = $data['post_type'] ? $data['post_type'] : 1;
             */
            $data['page']     = $data['page'] ? ($data['page']+1) : 1;
           
            $feedM->currentUser = $userId;

     
            $response =$feedM->getFeeds($type,$data['page']);

            $this->send($response);

     }
     //文章详情
    public function indexAction(){
                
                $this->required_fields = array_merge($this->required_fields, array('session_id','feed_id'));

                $data = $this->get_request_data();
                //$userId = $this->userAuth($data);
                if(@$data['session_id']){

                    $sess = new SessionModel();
                    $userId = $sess->Get($data);
                }
                else{

                    $userId = 0;
                }
            
                $FeedId=$data['feed_id'];
            

                $FeedModel = new Feedv1Model();

                $FeedMT = $FeedModel::$table;

                $FeedModel->currentUser = $userId;

                $FeedInfo = $FeedModel->GetFeedInfoById($FeedId,$userId);
                
                
                $response['feed_info'] = $FeedInfo;
                
               
                $visitFeedM = new FeedVisitModel();
                 
                $visitFeedM->feed_id = $FeedId;
                $visitFeedM->user_id = $userId;

                $id = $visitFeedM->get();
              
                if(!$id){
                    $properties = array();
                    $properties['created'] = time();
                    $properties['user_id'] = $userId;
                    $properties['feed_id']  = $FeedId;
                    $num=$FeedInfo['visit_num']+1;

                    $result=$FeedModel->updateByPrimaryKey(
                        $FeedMT,
                        array('feed_id'=>$FeedId),
                        array('visit_num'=>$num)
                    );

                    $visitFeedM->insert($visitFeedM->tableName, $properties);
                }
                
                
                $title = $FeedInfo['title'];
                $response['share_title'] = $title;
                $response['share_url'] = 'http://wap.bibicar.cn/'.$FeedId.'?identity='.base64_encode($data[
                    "device_identifier"]);
                $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
                $response['share_img'] = isset($FeedInfo['image_url'][0]) ? $FeedInfo['image_url'][0] : '';
                $this->send($response);

    }

    //添加评论
    public function commentcreateAction(){

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
                $commentM->father_id = $fatherId;
                $commentM->created = $time;

                $commentM->saveProperties();

                $commentId = $commentM->CreateM();

                if($commentId){
                    //sort 热度加分
                    $userpro=new UserSortModel();
                    $active="articlecomment";
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

    //评论列表
    public function commentlistAction(){

            
            $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

            $data = $this->get_request_data();
            $data['page']     = $data['page'] ? ($data['page']+1) : 1;
           
          
            $sess = new SessionModel();
            $userId = $sess->Get($data);
           
            $commentM = new Commentv1Model();
            $comments = $commentM->getComment($data['feed_id'],$data['page'],$userId);
           
            $this->send($comments);

    }

    //获取一级评论下的二级评论
     public function getcommentAction(){
          
        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','comment_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
       
      
        $sess = new SessionModel();
        $userId = $sess->Get($data);

         $commentM = new Commentv1Model();
         $Comment = $commentM->getCommentdetail($data['comment_id'],$data['feed_id'],$data['page'],$userId);
       
         $this->send($Comment);
         
     }

    
    public function commenttomeAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id', 'page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $userId = $this->userAuth($data);

        $commentM = new CommentModel();
        $comments = $commentM->getCommenttome(0,0,$data['page'],$userId);

        $this->send($comments);

    }

    //删除评论
    public function commentdeleteAction(){

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
    //评论点赞
    public function commentlikecreateAction(){
                
                $this->required_fields = array_merge(
                    $this->required_fields,
                    array('session_id', 'feed_id','comment_id')
                );

                $data = $this->get_request_data();

                $userId = $this->userAuth($data);
                
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
                        
                        //sort 热度加分
                        $userpro=new UserSortModel();
                        $active="articlecomment";
                        $type_id=$commentId;
                        $fromId=$userId;
                        $toId=$toId;
                        $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);
                        
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

    //取消评论点赞
    public function commentlikedeleteAction(){
                
                
                $this->required_fields = array_merge(
                    $this->required_fields,
                    array('session_id', 'feed_id','comment_id')
                );

                $data = $this->get_request_data();

                $userId = $this->userAuth($data);
                
              
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

    //收藏添加
    public function collectcreateAction(){
        //添加收藏的文章
        
        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $FeedCollectM              = new FeedCollectModel();
        $FeedCollectM->user_id     = $userId;
        $FeedCollectM->feed_id     = $data['feed_id'];
        $FeedCollect = $FeedCollectM->get();
      
        if($FeedCollect){

            $this->send_error(FAVORITE_CAR_ALREADY);
        }

        $FeedM = new Feedv1Model();
        $feedMTable = $FeedM::$table;
        $FeedM->currentUser = $userId;
        $feed = $FeedM->GetFeedInfoById($data['feed_id']);
        $feedNum = $feed['collect_num'] + 1;

        if(!$feed){

            $this->send_error(CAR_NOT_EXIST);
        }

        $FeedCollectM              = new FeedCollectModel();
        $FeedCollectM  ->user_id     = $userId;
        $FeedCollectM  ->feed_id  = $data['feed_id'];
        $created=time();
        $FeedCollectM  ->created     = $created;

        $FeedCollectM ->saveProperties();
        
        $id = $FeedCollectM->CreateM();

        if($id){
            
            //sort 热度加分
            $userpro=new UserSortModel();
            $active="articlecomment";
            $type_id=$commentId;
            $fromId=$userId;
            $toId=$toId;
            $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);
        }
        
        if(!$id){
            $this->send_error(FAVORITE_FAIL);
        }
        else{

            $FeedM->updateByPrimaryKey($feedMTable , array('feed_id'=>$data['feed_id']),array('collect_num'=>$feedNum));

            $response = array();
            $response['collect_id'] = $id;

            $feed["is_collect"] =1;
            $response['feed_info'] = $feed;

            $key = 'collect_'.$userId.'_'.$data['feed_id'].'';

            RedisDb::setValue($key,$id);

            $this->send($response);
        }

    }

    //收藏删除
    public function collectdeleteAction(){

            //删除收藏文章
            $this->required_fields = array_merge($this->required_fields,array('session_id', 'feed_id'));

            $data = $this->get_request_data();

            $userId = $this->userAuth($data);
           
            $FeedCollectM     = new FeedCollectModel();

            $FeedCollectM->feed_id      = $data['feed_id'];
            $FeedCollectM->user_id         = $userId;

            $key = 'collect_'.$FeedCollectM->user_id.'_'.$FeedCollectM->feed_id.'';

            $CollectId = RedisDb::getValue($key);
           
            $FeedCollectM->collect_id = $CollectId;
            $FeedCollectM->delete();

            RedisDb::delValue($key);

            $response = array();

            $this->send($response);

    }

    //收藏列表
     public function collectlistAction(){
            
            $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

             $feedM = new Feedv1Model();

            $data = $this->get_request_data();

            $data['page']   = $data['page'] ? $data['page'] : 1;
            $feedM->page = $data['page'];

            $userId = $this->userAuth($data);
             
            $feedM->currentUser = $userId;

            $list = $feedM->getUserCollectFeed($userId);

            $response = $list;

            $this->send($response);

    }


    public function visitlistAction(){

            //浏览过的文章
            $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

            $feedM = new Feedv1Model();

            $data = $this->get_request_data();

            $data['page'] = $data['page'] ? $data['page'] : 1;
            $feedM->page = $data['page'];

            $userId = $this->userAuth($data);
            
            $feedM->currentUser = $userId;

            $list = $feedM->getUserVisitFeed($userId);

            $response = $list;

            $this->send($response);
    }


    public function sharecreateAction(){

            //浏览过的文章
            $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id'));

            $feedM = new Feedv1Model();

            $data = $this->get_request_data();
            $userId = $this->userAuth($data);

             $FeedInfo = $FeedModel->GetFeedInfoById($FeedId,$userId);
                $response['feed_info'] = $FeedInfo;
       
            $shareFeedM = new FeedShareModel();
                 
                $shareFeedM->feed_id = $FeedId;
                $shareFeedM->user_id = $userId;

                $id = $shareFeedM->get();
                 
                if(!$id){
                    $properties = array();
                    $properties['created'] = time();
                    $properties['user_id'] = $userId;
                    $properties['feed_id']  = $FeedId;

                    $FeedModel->updateByPrimaryKey(
                        $FeedMT,
                        array('feed_id'=>$feedId),
                        array('share_num'=>($FeedInfo['share_num']+1))
                    );
                    $shareFeedM->insert($shareFeedM->tableName, $properties);
                }

            $this->send($response);
    }


}

