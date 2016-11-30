<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */

class PostController extends ApiYafControllerAbstract {

      public function collectcreateAction(){
        //添加收藏的文章
        /*
        $this->required_fields = array_merge($this->required_fields,array('session_id','article_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        */
        $userId=389;
        $data['feed_id']=2;

        $FeedCollectM              = new FeedCollectModel();
        $FeedCollectM->user_id     = $userId;
        $FeedCollectM->feed_id  = $data['feed_id'];
        $FeedCollect = $FeedCollectM->get();
        
        if($FeedCollect){

            $this->send_error(FAVORITE_CAR_ALREADY);
        }

        $feedM = new feedModel();
        $feedMTable = $feedM::$table;
        $feedM->currentUser = $userId;
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

}