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

             //sort 热度加分
            $userpro=new UserSortModel();
            $active="feedcread";
            $type_id=$feedId;
            $fromId=$userId;
            $toId=0;
            $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);

//            $postM = new PostModel();
//            $postM->post_id = $feedId;
//            $postM->user_id = $userId;
//            $postM->post_content = $feedM->post_content;
//            $postM->post_files = $feedM->post_files;
//            $postM->lat = $feedM->lat;
//            $postM->lng = $feedM->lng;
//            $postM->created = $feedM->created;
//            $postM->updated = $feedM->updated;
//
//            $postM->saveProperties();
//            $postM->CreateM();

            $feedM->updateFeedSourceId($feedId, $feedId);

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
        $feedM = new FeedModel();
        $themeM= new ThemelistModel();
        
        $data['post_type'] = $data['post_type'] ? $data['post_type'] : 1;


        $theme=$themeM->getTheme($data['post_type']);
        
        if($theme){
            $feedM->currenttheme=@$theme['theme'];
            $data['post_type']=7;
        }

        $data['page']  = $data['page'] ? ($data['page']+1) : 1;

        $feedM->currentUser = $userId;
      
        $response = $feedM->getFeeds(0,$data['post_type'],$userId,$data['page']);

        if($theme){
            
            $response["theme_info"]=$theme;
        }else{
            $respose["theme_info"]= new stdClass();
        }

        if($response['feed_list']){

            foreach($response['feed_list'] as $key => $list){
                
            /*     $response['feed_list'][$key]['post_content'] = strlen($response['feed_list'][$key]['post_content']) > 30
                                          ? mb_substr($response['feed_list'][$key]['post_content'], 0 , 30) . '...'
                                          : $response['feed_list'][$key]['post_content'];
             */
                   //相关的人 start
                   $feedrelatedM = new FeedrelatedModel();
                   $date['feed_id']=$response['feed_list'][$key]['feed_id'];
                  // $feedrelatedM->currentUser = $userId;
                   $feeds =$feedrelatedM->getFeeds($date);
                   $response['feed_list'][$key]['feeds']=$feeds;

                if($response['feed_list'][$key]['forward_id'] > 0){

                    $response['feed_list'][$key] = $feedM->forwardHandler($response['feed_list'][$key]);
                }
            }
        }

       $theme=$themeM->getThemes(2);
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

        public function topiclistAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','post_type','page'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $data['post_type'] = $data['post_type'] ? $data['post_type'] : 1;

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $feedM = new FeedModel();

        $feedM->currentUser = $userId;

        $response = $feedM->getFeeds(0,$data['post_type'],$userId,$data['page']);

        if($response['feed_list']){

            foreach($response['feed_list'] as $key => $list){

//                if(isset($list['post_files'][0])){
//
//                    $response['feed_list'][$key]['post_files'] = array();
//                    $response['feed_list'][$key]['post_files'][] = $list['post_files'][0];
//                }
//                
//                
                   //相关的人 start
                   $feedrelatedM = new FeedrelatedModel();
                   $date['feed_id']=$response['feed_list'][$key]['feed_id'];
                  // $feedrelatedM->currentUser = $userId;
                   $feeds =$feedrelatedM->getFeeds($date);
                   $response['feed_list'][$key]['feeds']=$feeds;
                   

                if($response['feed_list'][$key]['forward_id'] > 0){

                    $response['feed_list'][$key] = $feedM->forwardHandler($response['feed_list'][$key]);
                }
            }
        }

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

        $profileM = new ProfileModel();
        $objprofile = $profileM->getProfile($objId);

        $feedM->currentUser = $objId;
        $feedM->loginUser   = $userId;

        $response = $feedM->getFeeds(0,$data['post_type'],$userId, $data['page']);

        $myFeeds = $response['feed_list'];

        foreach($myFeeds as $k => $myFeed){
    
           //相关的人 start
           $feedrelatedM = new FeedrelatedModel();
           $date['feed_id']=$myFeed['feed_id'];
          // $feedrelatedM->currentUser = $objId;
           $feedds =$feedrelatedM->getFeeds($date);
           $myFeeds[$k]['feeds']=$feedds; 

            
            if($myFeeds[$k]['forward_id'] > 0){

                $myFeeds[$k] = $feedM->forwardHandler($myFeeds[$k]);
            }
        }

        $response['feed_list'] = $myFeeds;
        if($myFeeds){
            $response['share_title'] = $myFeeds[0]['post_user_info']['profile']['nickname'] . '的车友圈';
            $response['share_url'] = 'http://share.bibicar.cn/center/'.base64_encode($data['session_id']).'/'.base64_encode($data['device_identifier']).'/'.$myFeeds[0]['post_user_info']['user_id'];
           //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';
           $response['share_txt'] = '更多精彩内容尽在bibi,期待您的加入!';
           $response['share_img'] = $myFeeds[0]['post_user_info']['profile']['avatar'] ;
        }else{
            $response['share_title'] = $objprofile['nickname'] . '的车友圈';
            $response['share_url'] = 'http://share.bibicar.cn/center/'.base64_encode($data['session_id']).'/'.base64_encode($data['device_identifier']).'/'.$objId;
           //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';
           $response['share_txt'] = '更多精彩内容尽在bibi,期待您的加入!';
           $response['share_img'] = $objprofile['avatar'] ;
        }
        

        $this->send($response);

    }

    public function publishtomeAction(){

       
        $this->required_fields = array_merge($this->required_fields,array('session_id', 'user_id','page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
 
       
       // $data['user_id']=310;
       // $data['page']=1;
        $feedM = new FeedModel();

       // $objId = $this->getAccessId($data, $userId);
       
        $respon = $feedM->getfeedstotime($data['user_id'], $data['page']);
        $response['list']=$respon;
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

        /*
         if($userId && $userId!=0 ){
        //相关的人
        $feedrelatedM = new FeedrelatedModel();
        $data['feed_id']=$data['feed_id'];
        $data['user_id']=$userId;
        $data['view'] ='1';
        $data['create_time']=time();
        $feedrelatedM->savefeed($data);
        }
        */

        if($feed['forward_id'] > 0){

           $feed = $feedM->forwardHandler($feed);
        }

        $comments = $feed['comment_list'];

        $feed['comment_list'] = array();

        foreach($comments as $k => $comment){

            if($k < 10){

                $feed['comment_list'][] = $comment;
            }

        }

        $commentList= array();

        $num = 10;
        //$n = 0;
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



        $response['share_title'] = $feed['post_user_info']['profile']['nickname'] . '的车友圈';
        $response['share_url'] = 'http://wap.bibicar.cn/circle/'.$data['feed_id'].'?identity='.base64_encode($data['device_identifier']);
        //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';
       
        $response['share_txt'] = '更多精彩内容尽在bibi,期待您的加入!';
        $response['share_img'] = @$feed['post_files'][0]['file_url'];

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

    public function forwardAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'forward_content','forward_id')
        );

        $data = $this->get_request_data();


        $userId = $this->userAuth($data);

        $feedM = new FeedModel();

        $forwardId = $data['forward_id'];

        $forwardFeed = $feedM->getFeeds($forwardId);

        if(!$forwardFeed){

            $this->send_error(FEED_NOT_EXIST);
        }

        $sourceId = $forwardFeed['source_id'];

        $time = time();

        $data['lat'] = isset($data['lat']) ? $data['lat'] : 0.00;
        $data['lng'] = isset($data['lng']) ? $data['lng'] : 0.00;

        $feedM->user_id = $userId;
        $feedM->post_content = $data['forward_content'];
        //$feedM->post_files = array();
        $feedM->lat = $data['lat'];
        $feedM->lng = $data['lng'];
        $feedM->created = $time;
        $feedM->updated = $time;
        $feedM->source_id = $sourceId;
        $feedM->forward_id = $forwardId;
        $feedM->feed_type = 2;

        $feedM->saveProperties();

        $feedM::$table = 'bibi_feeds';

        $feedId = $feedM->CreateM();

        $forwardUsers = RedisDb::getForwardUsers($forwardId);

        $forwardUserId = $forwardFeed['post_user_info']['user_id'];

        array_push($forwardUsers,$forwardUserId);

        RedisDb::saveForwardUser($feedId, $forwardUsers);

        $feedM->updateForwardNum($forwardId);

        $userM = new UserModel();
        $userM->updateGeoById($userId, $data['lat'], $data['lng']);

        $feedInfo = $feedM->getFeeds($feedId);

        $feedInfo = $feedM->forwardHandler($feedInfo);

        $response = $feedInfo;

        $this->send($response);
    }


        public function shareindexAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();

        $page = @$data['page'] ? (@$data['page']+1) : 1;

         if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }

        $feedM = new FeedModel();

        $feedM->currentUser = $userId;

        $feed = $feedM->getFeeds($data['feed_id']);

       if(!$feed){

            $this->send_error(FEED_NOT_EXIST);
        }

        if($feed['forward_id'] > 0){

           $feed = $feedM->forwardHandler($feed);
        }

        $comments = $feed['comment_list'];

        $feed['comment_list'] = array();

        foreach($comments as $k => $comment){

            if($k < 10){

                $feed['comment_list'][] = $comment;
            }

        }

        $commentList= array();

        $num = 10;
        //$n = 0;
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
         
        //热门话题
        $themeM= new ThemelistModel();
        $theme=$themeM->getThemes(2);
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
                   }else{
                    $banners[$key]['type']=(string)$value["id"];
                   }
                   

              }
           }
         $response['theme_list'] = $banners;

        //最热文章
          $feedv1M = new Feedv1Model();
          $articles=$feedv1M->getFeeds(1,1);
          
          $response['article_list']=$articles;




        $response['has_more'] = ($commentTotal - $count > 0) && ($getNum <= $commentTotal) ?  1 : 2;

        $response['share_title'] = $feed['post_user_info']['profile']['nickname'] . '的车友圈';
        $response['share_url'] = 'http://share.bibicar.cn/circle/'.base64_encode($data['device_identifier']).'/'.$data['feed_id'];
        //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';
       
        $response['share_txt'] = '更多精彩内容尽在bibi,期待您的加入!';
        $response['share_img'] = $feed['post_files'][0]['file_url'];

        $this->send($response);

    }
}