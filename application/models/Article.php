<?php

/**
 * Created by Sublime.
 * User: jpjy
 * Date: 16/10/21
 * Time: 下午1:30
 * note: 文章表
 */
class ArticleModel extends PdoDb
{


    public $article_id;
    public $sort_id;
    public $title ;
    public $full_title;
    public $author_id;
    public $copyfrom ;
    public $http_url ;
    public $keyword;
    public $hits;
    public $like_num;
    public $post_num;
    public $collect_num;
    public $ontop;
    public $is_hot;
    public $is_delect;
    public $created;
    public $update_time;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_article';
    }

    public function saveProperties()
    {

        //$this->properties['feed_id']        = $this->feed_id;
        $this->properties['sort_id'] = $this->sort_id;
        $this->properties['title'] = $this->title;
        $this->properties['full_title'] = $this->full_title;
        $this->properties['author_id'] = $this->author_id;
        $this->properties['copyfrom'] = $this->copyfrom;
        $this->properties['http_url'] = $this->http_url;
        $this->properties['keyword'] = $this->keyword;
        $this->properties['created'] = $this->created;
        $this->properties['update_time'] = $this->update_time;

    }

    public function GetArticleInfoById($article_id,$userId=0)
    {


        $sql = '
            SELECT
            t1.*,
            t2.content,
            t3.avatar as author_avatar,t3.nickname as author_nickname
            FROM `' . self::$table . '`AS t1
            LEFT JOIN `bibi_article_content` AS t2
            ON t1.article_id = t2.article_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t1.author_id = t3.user_id
            WHERE t1.article_id = "' . $article_id . '"
        ';


        $article = @$this->query($sql)[0];
        
        if (!$article) {

            return array();
        }

        $article = $this->handlerArticle($article,$userId);

        return $article;

    }

    public function handlerArticle($article,$userId){

        if($article['author_id']){

            $article['author_info'] = array();
            $article['author_info']['user_id']  = $article['author_id'];
            

            $article['author_info']['avatar']     =$article['author_avatar'];
            $article['author_info']['nickname']   = $article['author_nickname'];
            

        }
        else{
            $article['user_info'] = new stdClass();
        }
        unset($article['author_avatar']);
        unset($article['author_nickname']);
        
       
        if($article['content']){
            $content=json_decode($article['content']);
            $array=$this->object_array($content);

            $article['content_info']=$array;
            

        }
       
        unset($article['content']);


        $article['comment_list'] =array();
        $comment = $this->getArticleComment($article["article_id"]);
        $article['comment_list']=$comment;

        $article["like_list"]  =array();
        $article["like_list"]  =$this->getArticlelike($article['article_id']);

        if($userId){
            $user=$this->getcollectbyuser($article["article_id"],$userId);
            if(!$user){
                $article['is_collect']=0;
            }else{
                $article['is_collect']=1;
            }

        }

        return $article;

    }
    public function object_array($array) { 

            if(is_object($array)) { 
                $array = (array)$array; 
             } if(is_array($array)) { 
                 foreach($array as $key=>$value) { 
                     $array[$key] = $this->object_array($value); 
                     } 
             } 
             return $array; 
    }

    public function getArticleComment($article_id)
    {
       
        $sql = '
            SELECT
            t1.*,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM `bibi_article_comments`AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON t1.from_id = t2.user_id
            LEFT JOIN
            `bibi_user_profile` AS t3
            ON t1.to_id = t3.user_id
            WHERE t1.article_id = "' . $article_id . '"
        ';


        $comments = @$this->query($sql);
       
        if (!$comments) {

            return array();
        }
         
        $list=array();
        foreach($comments as $key =>$value){
               $list[$key]['reply_id']=0;
               if($value['reply_id']){
                    $list[$key]['reply_id']=$value['reply_id'];
                    $list[$key]['created']=$value['created'];
                    $list[$key]["to_user_info"]=array();
                    $list[$key]["to_user_info"]['user_id']=$value['comment_to_user_id'];
                    $list[$key]["to_user_info"]['profile']['avatar']=$value['comment_to_avatar'];
                    $list[$key]["to_user_info"]['profile']['nickname']=$value['comment_to_nickname'];

               }else{
                    $list[$key]['to_user_info'] = new stdClass();
               }
               $list[$key]['created']=$value['created'];
               $list[$key]["from_user_info"]=array();
               $list[$key]["from_user_info"]['user_id']=$value['comment_from_user_id'];
               $list[$key]["from_user_info"]['profile']['avatar']=$value['comment_from_avatar'];
               $list[$key]["from_user_info"]['profile']['nickname']=$value['comment_from_nickname'];

        }
        return $list;
    }


    public function getArticlelike($article_id)
    {
       
        $sql = '
            SELECT
            t1.article_id,t1.user_id,
            t2.avatar,t2.nickname
            FROM `bibi_article_likes`AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON t1.user_id = t2.user_id
            WHERE t1.article_id = "'. $article_id .'"
        ';


        $likes = @$this->query($sql);
       
        if (!$likes) {

            return array();
        }
         
        $list=array();
        foreach($likes as $key =>$value){
                $list[$key]=$value;
        }
       
        return $list;
    }

    public function getcollectbyuser($article_id,$userId){

         $sql = '
            SELECT
            article_id,user_id
            FROM `bibi_article_collect`
            WHERE article_id = "'. $article_id .'"AND user_id="'.$userId.'"
        ';
        $likes = @$this->query($sql);  
        return $likes;
    }




    public function getArticles($type = 0, $userId = 0 ,$page = 1)
    {

        $sql = '
            SELECT
            t1.*,
            t2.avatar as author_avatar,t2.nickname as author_nickname
            FROM `bibi_article`AS t1
            LEFT JOIN `bibi_user_profile` AS t2
            ON t1.author_id = t2.user_id
                ';


            $pageSize = 10;
            $number = ($page - 1) * $pageSize;
           
            switch ($type) {

                //like_num DESC, comment_num DESC, feed_id DESC

                case 1:
                    //推荐
                    $sqlHot = '
                        SELECT
                        t1.article_id
                        FROM
                        `bibi_article` AS t1
                        ORDER BY
                        created DESC
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';


                    $sqlHotCnt = '
                        SELECT
                        COUNT(t1.article_id) AS total
                        FROM
                        `bibi_article` AS t1
                        ORDER BY
                        created DESC
                    ';


                    $total = $this->query($sqlHotCnt)[0]['total'];

                    $result = @$this->query($sqlHot);
                    $result = $this->implodeArrayByKey('article_id', $result);

                    $sql .= ' WHERE t1.article_id in (' . $result . ') ORDER BY t1.`like_num` DESC , t1.`created` DESC'; //ORDER BY t3.comment_id DESC

                    break;

                case 2:
                    //说客
                    $sqlLatest = '
                        SELECT
                        t1.article_id
                        FROM
                        `bibi_article` AS t1
                        ORDER BY created DESC
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';



                    $sqlLatestCnt = '
                        SELECT
                        COUNT(t1.article) AS total
                        FROM
                        `bibi_article` AS t1
                        ORDER BY created  DESC
                    ';

                    $total = $this->query($sqlLatestCnt)[0]['total'];

                    $result = @$this->query($sqlLatest);
                    $result = $this->implodeArrayByKey('article_id', $result);

                    $sql .= ' WHERE t1.article_id in (' . $result . ') ORDER BY `article` DESC ';

                    break;
            }


        

        $articles = $this->query($sql);
        
        $articles = $this->handleArticles($articles,$userId);
        
        if(!$articles){

             return isset($articles[0]) ? $articles[0] : array() ;
        }
        else{
             $count = count($articles);
            $list['$article_list']=array();
            $list['$article_list'] = $articles;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;
           
        }



    }

    public function getUserCollectArticle($userId){

        $pageSize = 10;

        $sql = '
            SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_article` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.author_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                LEFT JOIN `bibi_article_collect` AS t4
                ON t1.article_id = t4.article_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $sqlCnt = '
            SELECT
            count(*) AS total
            FROM `bibi_article` AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.author_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            LEFT JOIN `bibi_article_collect` AS t4
            ON t1.article_id = t4.article_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $articles = $this->query($sql);


        $articles=$this->handleArticles($articles);

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($articles);

        $list['article_list'] =$articles;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;

    }


    public function getUserVisitArticle($userId){

        $pageSize = 10;

        $sql = '
            SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_article` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.author_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                LEFT JOIN `bibi_article_visit` AS t4
                ON t1.article_id = t4.article_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $sqlCnt = '
            SELECT
            count(*) AS total
            FROM `bibi_article` AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.author_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            LEFT JOIN `bibi_article_visit` AS t4
            ON t1.article_id = t4.article_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $articles = $this->query($sql);


        $articles=$this->handleArticles($articles);

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($articles);

        $list['article_list'] =$articles;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;

    }



    public function handleArticles($articles,$userId = 0){

       
        foreach ($articles as $k => $article) {

            if($userId){
               
                $user=$this->getcollectbyuser($article["article_id"],$userId);
                
                if(!$user){
                    $articles[$k]['is_collect']=0;
                }else{
                    $articles[$k]['is_collect']=1;
                }
           
               }
          
           
            

        }
        
        
        return $articles;
    }






}