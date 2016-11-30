<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */
//话题发布，讨论
class ThemeController extends ApiYafControllerAbstract {


    public function createAction(){
       
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'theme')
        );
       
        $data = $this->get_request_data();
      /*  $data['theme']   ="#吡吡汽车#";
        $data['user_id'] =489;
        $data['file_id']="FnbV5bjLXlZfqZs-2cScFSMmTUji";
        $userId=$data['user_id'];
        */
        $userId = $this->userAuth($data);

        $time = time();

        $themeM= new ThemelistModel();
        
        $themeM->user_id = $userId;
        $themeM->theme = $data['theme'];

        $themeM->created = $time;
        $themeM->saveProperties();

        $themeId = $themeM->CreateM();
        
        $themeInfo = $themeM->gettheme($themeId);
       
        $this->send($themeId);


    }


    public function listAction(){
       
        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);
      
      


        $data['page']  = $data['page'] ? ($data['page']+1) : 1;
       
        $themeM= new ThemelistModel();

        $themeM->currentUser = $userId;
        
        $response = $themeM->getThemes($userId,$data['page']);

        $banners = array(

            array(
                'imgUrl'=>"http://img.bibicar.cn/theme-sui.jpg",
                'appUrl'=>"#话题#",
                'title' =>"岁月是一场有去无回的旅行，走过的路，错过的人，遇见的事，好的坏的都是风景",
            )

        );
        
        $response['banners'] = $banners;
       print_r($response);exit;
        $this->send($response);

    }

    public function indexAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','theme_id','page'));

        $data = $this->get_request_data();
        $sess = new SessionModel();
        $userId = $sess->Get($data);


        $data['post_type'] = 7;
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
       
        $feedM = new FeedModel();
        $themeM= new ThemelistModel();
        $theme= $themeM->getTheme($data['theme_id']);
       
        $feedM->currentUser = $userId;
 
        $feedM->currenttheme= $theme[0]["theme"];

        $response = $feedM->getFeeds(0,$data['post_type'],$userId,$data['page']);

        if($response['feed_list']){

            foreach($response['feed_list'] as $key => $list){
               
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

        $banners = array(

            array(
                'imgUrl'=>"http://img.bibicar.cn/".$theme[0]["post_file"],
                'appUrl'=>$theme[0]["theme"],
                'title' =>$theme[0]["title"],
            )

        );
        
        $response['banners'] = $banners;
      
        $this->send($response);

    }

    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','Theme_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $themeM= new ThemelistModel();
        $themeM->currentUser = $userId;

        $themeM->deleteTheme($data['Theme_id']);

        $this->send();

    }


}