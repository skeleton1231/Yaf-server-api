<?php

/**
 * Created by Sublime.
 * User: jpjy
 * Date: 16/10/21
 * Time: 下午1:30
 * note: 文章表
 */
class Feedv1Model extends PdoDb
{


    public $feed_id;
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
        self::$table = 'bibi_feeds';
    }

    public function saveProperties()
    {

        //$this->properties['feed_id']        = $this->feed_id;
        $this->properties['sort_id'] = $this->sort_id;
        $this->properties['title'] = $this->title;
        $this->properties['full_title'] = $this->full_title;
        $this->properties['user_id'] = $this->user_id;
        $this->properties['copyfrom'] = $this->copyfrom;
        $this->properties['http_url'] = $this->http_url;
        $this->properties['keyword'] = $this->keyword;
        $this->properties['created'] = $this->created;
        $this->properties['update_time'] = $this->update_time;

    }

    public function GetFeedInfoById($feed_id,$userId=0)
    {


        $sql = '
            SELECT
            t1.feed_id,t1.feed_type,t1.user_id,t1.grade_id,t1.post_content,t1.post_files,t1.visit_num,t1.comment_num,t1.like_num,t1.collect_num,t1.created,t1.image_url,feed_from,html_url,
            t2.avatar,t2.nickname 
            FROM `' . self::$table . '`AS t1
            LEFT JOIN `bibi_user_profile` AS t2
            ON t1.user_id = t2.user_id
            WHERE t1.feed_id = "' . $feed_id . '"
        ';

        
        $feed = @$this->query($sql)[0];
       
        if (!$feed) {

            return array();
        }
       
        $feed = $this->handlerFeed($feed,$userId);
       
        return $feed;

    }

    public function handlerFeed($feed,$userId){
       
        $items = array();

        $items['feed_id']      = $feed['feed_id'];
        $items['feed_type']    = $feed['feed_type'];
        $items['title']        = $feed['post_content'];
        $items['collect_num']  = $feed['collect_num'];
        $items['comment_num']  = $feed['comment_num'];
        $items['like_num']     = $feed['like_num'];
        $items['visit_num']    = $feed['visit_num'];
        $items['created']      = $feed['created'];
        $items['feed_from']    = $feed['feed_from'];
        $items['html_url']     = $feed['html_url'];


        if ($feed['image_url']) {
                $arr=array();
                $arr=explode(";", $feed["image_url"]);
               
                $items['image_url'] =$arr;

            }
        else{

            $items['image_url'] = array();
        }


      

             $collectKey = 'collect_'.$userId.'_'.$feed['feed_id'].'';

            Common::globalLogRecord('collect key', $collectKey);

            $isCollect = RedisDb::getValue($collectKey);

            $items['is_collect']  = $isCollect ? 1 : 2;

        /*    $user=$this->getcollectbyuser($feed["feed_id"],$userId);
           
            if(!$user){
                $items['is_collect']=2;
            }else{
                $items['is_collect']=1;
            }
       */
        
         

                $likeKey= 'like_'.$feed['feed_id'].'_'.$userId;

                Common::globalLogRecord('like key', $likeKey);


                $isLike = RedisDb::getValue($likeKey);

                $items['is_like']  = $isLike ? 1 : 2;
      
        
        if($feed['user_id']){

            $items['post_user_info'] = array();
            $items['post_user_info']['user_id'] = $feed['user_id'];
            $items['post_user_info']['profile']['avatar'] = $feed['avatar'];
            $items['post_user_info']['profile']['nickname'] = $feed['nickname'];
        }
        else{
            $items['post_user_info'] = new stdClass();
        }
       
        if($feed['post_files']){
           // print_r($feed['post_files']);
            $content=unserialize($feed['post_files']);
            if($content){
                 $array=$this->object_array($content);
            
                 $items['content_info']=$array;
            }else{
                $items['content_info'] = new stdClass();
            }
           
        }else{
           $items['content_info'] = new stdClass();
        }

    
        return $items;

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

    public function getFeedComment($feed_id)
    {
       
        $sql = '
            SELECT
            t1.*,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM `bibi_comments`AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON t1.from_id = t2.user_id
            LEFT JOIN
            `bibi_user_profile` AS t3
            ON t1.to_id = t3.user_id
            WHERE t1.feed_id = "' . $feed_id . '"
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

    public function getcollectbyuser($feed_id,$userId){

         $sql = '
            SELECT
            feed_id,user_id
            FROM `bibi_feeds_collect`
            WHERE feed_id = "'. $feed_id .'"AND user_id="'.$userId.'"
        ';
        $collect = $this->query($sql);  
       
        return $collect;
    }




    public function getFeeds($type=1,$page = 1,$userId = 0)
    {
         
        $sql = '
            SELECT
            t1.feed_id,t1.feed_type,t1.user_id,t1.grade_id,t1.post_content,t1.image_url,t1.visit_num,t1.comment_num,t1.like_num,t1.collect_num,t1.feed_from,t1.created,
            t2.avatar ,t2.nickname 
            FROM `bibi_feeds`AS t1
            LEFT JOIN `bibi_user_profile` AS t2
            ON t1.user_id = t2.user_id
                ';
        $pageSize = 10;
        $number = ($page - 1) * $pageSize;

          switch ($type) {

                case 1:
                    //推荐消息
                    $sqlHot = '
                        SELECT
                        t1.feed_id
                        FROM
                        `bibi_feeds` AS t1
                        WHERE t1.feed_type <> 1
                        ORDER BY
                        like_num DESC,feed_id
                        LIMIT ' .$number. ' , ' . $pageSize . '
                    ';
                    $sqlHotCnt = '
                        SELECT
                        COUNT(t1.feed_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        WHERE t1.feed_type <> 1
                        ORDER BY
                        like_num DESC,feed_id
                    ';


                    $total = $this->query($sqlHotCnt)[0]['total'];
                     
                    $result = @$this->query($sqlHot);
                   
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' .$result. ') ORDER BY t1.`like_num` DESC , t1.`created` DESC'; //ORDER BY t3.comment_id DESC

                    break;

                case 2:
                    //分类下的话题
                    $sqlLatest = '
                        SELECT
                        t1.feed_id
                        FROM
                        `bibi_feeds` AS t1
                        ORDER BY created DESC
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';



                    $sqlLatestCnt = '
                        SELECT
                        COUNT(t1.feeds_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        ORDER BY created  DESC
                    ';

                    $total = $this->query($sqlLatestCnt)[0]['total'];

                    $result = @$this->query($sqlLatest);
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' . $result . ') ORDER BY t1.`created` DESC ';

                    break;
                case 3:
                    //标签下的话题
                    $sqlLatest = '
                        SELECT
                        t1.feed_id
                        FROM
                        `bibi_feeds` AS t1
                        ORDER BY created DESC
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';

                    $sqlLatestCnt = '
                        SELECT
                        COUNT(t1.feeds_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        ORDER BY created  DESC
                    ';

                    $total = $this->query($sqlLatestCnt)[0]['total'];

                    $result = @$this->query($sqlLatest);
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' . $result . ') ORDER BY t1.`created` DESC ';

                    break;
            }


        
       
        $feeds = $this->query($sql);
       
        $feeds = $this->handleFeeds($feeds,$userId);
       
        if(!$feeds){

             return isset($feeds[0]) ? $feeds[0] : array() ;
        }
        else{
            $items['list'] = array();
            $items['list'] = $feeds;
            $count = count($feeds);
            $items['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $items['total'] = $total;

            return $items;
           
        }



    }


    public function handleFeeds($feeds,$userId = 0){

        $items= array();

        foreach ($feeds as $k => $feed) {
            $items[$k]=array();
            if ($feed['user_id']) {

                $items[$k]['post_user_info'] = array();
                $items[$k]['post_user_info']['user_id'] = $feed['user_id'];
                $items[$k]['post_user_info']['profile']['avatar'] = $feed['avatar'];
                $items[$k]['post_user_info']['profile']['nickname'] = $feed['nickname'];

            } else {

                $items[$k]['post_user_info'] = new stdClass();
            }

            $items[$k]['feed_id'] = $feed['feed_id'];
            $items[$k]['feed_type'] = $feed['feed_type'];
            $items[$k]['post_content'] = $feed['post_content'];
            $items[$k]['comment_num'] = $feed['comment_num'];
            $items[$k]['collect_num'] = $feed['collect_num'];
            $items[$k]['like_num'] = $feed['like_num'];
            $items[$k]['created'] = $feed['created'];

            

                $collectKey = 'collect_'.$userId.'_'.$feed['feed_id'].'';

                Common::globalLogRecord('collect key', $collectKey);


                $isCollect = RedisDb::getValue($collectKey);

                $items[$k]['is_collect']  = $isCollect ? 1 : 2;

        
          

                $likeKey= 'like_'.$feed['feed_id'].'_'.$userId;

                Common::globalLogRecord('like key', $likeKey);


                $isLike = RedisDb::getValue($likeKey);

                $items[$k]['is_like']  = $isLike ? 1 : 2;
      
          

            if ($feed['image_url']) {
                $arr=array();
                $arr=explode(";", $feed["image_url"]);
               
                $items[$k]['image_url'] =$arr;

            }
            else{

                $items[$k]['image_url'] = array();
            }

        }
        return $items;
    }

    public function getUserCollectFeed($userId){

        $pageSize = 10;

        $sql = '
            SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_feeds` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                LEFT JOIN `bibi_feeds_collect` AS t4
                ON t1.feed_id = t4.feed_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $sqlCnt = '
            SELECT
            count(*) AS total
            FROM `bibi_feeds` AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            LEFT JOIN `bibi_feeds_collect` AS t4
            ON t1.feed_id = t4.feed_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $feeds = $this->query($sql);


        $feeds=$this->handleFeeds($feeds,$userId);

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($feeds);
        $list['feed_list']=array();
        $list['feed_list'] =$feeds;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;

    }


    public function getUserVisitFeed($userId){

        $pageSize = 10;

        $sql = '
            SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_feeds` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                LEFT JOIN `bibi_feeds_visit` AS t4
                ON t1.feed_id = t4.feed_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $sqlCnt = '
            SELECT
            count(*) AS total
            FROM `bibi_feeds` AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            LEFT JOIN `bibi_feeds_visit` AS t4
            ON t1.feed_id = t4.feed_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $feeds = $this->query($sql);


        $feeds=$this->handleFeeds($feeds);

        $total = @$this->query($sqlCnt)[0]['total'];


        if(!$feeds){

             return isset($feeds[0]) ? $feeds[0] : array() ;
        }
        else{
             $count = count($feeds);
            $list['feed_list']=array();
            $list['feed_list'] = $feeds;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;
           
        }

    }



    






}