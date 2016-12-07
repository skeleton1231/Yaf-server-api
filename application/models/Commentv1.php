<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:44
 */


class Commentv1Model extends PdoDb {


    public $comment_id;
    public $feed_id;
    public $content;
    public $from_id;
    public $to_id;
    public $file_url = '';
    public $file_type = 1;
    public $created;
    public $father_id;
    public $is_reply;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_comments';
    }

    public function saveProperties(){

        //$this->properties['comment_id'] = $this->comment_id;
        $this->properties['feed_id']    = $this->feed_id;
        $this->properties['content']    = $this->content;
        $this->properties['from_id']    = $this->from_id;
        $this->properties['to_id']      = $this->to_id;
        $this->properties['reply_id']   = $this->reply_id;
        $this->properties['father_id']  = $this->father_id;
        $this->properties['file_url']   = $this->file_url;
        $this->properties['file_type']  = $this->file_type;
        $this->properties['created']    = $this->created;
    }

    public function getComment($feedId=0, $page=1,$userId=0){

        $sql = '
            SELECT
            t1.feed_id,t1.like_num,
            t1.comment_id,t1.content as comment_content,t1.created as comment_created,t1.reply_id as comment_reply_id,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM
            `bibi_comments` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.from_id = t2.user_id
            LEFT JOIN
            `bibi_user_profile` AS t3
            ON
            t1.to_id = t3.user_id
            WHERE 
        ';
     
     if($feedId){

            $sql .= ' t1.reply_id = 0 AND t1.feed_id = '.$feedId.' ';

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;
            

            $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';


            $sqlCnt = '
                SELECT
                COUNT(t1.comment_id) as total
                FROM
                `bibi_comments` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.from_id = t2.user_id
                LEFT JOIN
                `bibi_user_profile` AS t3
                ON
                t1.to_id = t3.user_id
                WHERE
                t1.reply_id = 0 AND
                `feed_id` = '.$feedId.'
            ';

            $total = $this->query($sqlCnt)[0]['total'];
        }
      
        
        $comments = $this->query($sql);
       
        $comments = $this->handleComment($comments,$userId);
        
       
        if($comments){

            $count = count($comments);
            $list['comment_list'] = $comments;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;
        }
        else{
            $list['comment_list']=array();
            $list['has_more']    =2;
            $list['total']       =0;
            return  $list ;
        }

    }
    
    public function getCommentToComment($commentId=0, $feedId=0,$userId){

        $sql = '
             SELECT
            t1.feed_id,t1.like_num,
            t1.comment_id,t1.content as comment_content,t1.created as comment_created,t1.reply_id as comment_reply_id,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM
            `bibi_comments` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.from_id = t2.user_id
            LEFT JOIN
            `bibi_user_profile` AS t3
            ON
            t1.to_id = t3.user_id
            WHERE 
        ';
        $sql .= ' t1.feed_id = '.$feedId.' AND t1.father_id = '.$commentId.' ';
        $number=0;
        $pageSize=2;

        $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

         $sqlCnt = '
                SELECT
                COUNT(t1.comment_id) as total
                FROM
                `bibi_comments` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.from_id = t2.user_id
                LEFT JOIN
                `bibi_user_profile` AS t3
                ON
                t1.to_id = t3.user_id
                WHERE
                t1.father_id = '.$commentId.' AND
                `feed_id` = '.$feedId.'
            ';

        $total = $this->query($sqlCnt)[0]['total'];
       
        $comments = $this->query($sql);
        
        $comments=  $this->handleComments($comments,$userId);
        
        if($comments){
            $list['list']=array();
            $list["list"]=$comments;
            $list['total']=$total;
            return $list;
        }
        else{
            $list['list'] =array();
            $list['total']=0;
            return $list ;
        }

    }

    public function handleComment($comments,$userId){

        foreach($comments as $key =>$value){
                 //是否点赞
                $likeKey ='commentlike_'.$value["feed_id"].'_'.$userId.'_'.$value["comment_id"].'';
                Common::globalLogRecord('like key', $likeKey);
                $isLike = RedisDb::getValue($likeKey);
                $comments[$key]['is_like']  = $isLike ? 1 : 2;


                $comments[$key]["from_user"]=array();
                $comments[$key]["to_user"]=array();
                $comments[$key]['from_user']["user_id"]=$value["comment_from_user_id"];
                $comments[$key]['from_user']["avatar"]=$value["comment_from_avatar"];
                $comments[$key]['from_user']["nickname"]=$value["comment_from_nickname"];
                $comments[$key]['to_user']["user_id"]=$value["comment_to_user_id"];
                $comments[$key]['to_user']["avatar"]=$value["comment_to_avatar"];
                $comments[$key]['to_user']["nickname"]=$value["comment_to_nickname"];
                


                $carM = new CarSellingModel();
                $carM->page = 1;
                $car = $carM->getUsertoCar($value["comment_from_user_id"]);
                if($car){
                   foreach($car as $val){
                    $comments[$key]['brand_info']=$val;
                  } 
                }
                
          /*      if($car && $car['brand_info']){
                     $comments[$key]['brand_info']=$car['brand_info'];
                 }else{
                     $comments[$key]['brand_info']=new stdClass();
                 } 
              */  

               
                unset($comments[$key]["comment_from_user_id"]);
                unset($comments[$key]["comment_from_avatar"]);
                unset($comments[$key]["comment_from_nickname"]);
                unset($comments[$key]["comment_to_user_id"]);
                unset($comments[$key]["comment_to_avatar"]);
                unset($comments[$key]["comment_to_nickname"]);
               
                $comments[$key]["hot_list"]=$this->getCommentToComment($value["comment_id"],$value["feed_id"],$userId);
        }
        
         
        return $comments;
    }


    public function deleteComment(){

        $sql = '
                DELETE FROM `bibi_comments`
                WHERE
                `comment_id` = '.$this->comment_id.'
                AND
                `from_id` = '.$this->currentUser.'
                AND
                `feed_id` = '.$this->feed_id.'
                ';

        $this->execute($sql);
    }


    public function updateCommentNum($feedId, $action='add'){

        $condition = $action == 'add' ? 'comment_num = comment_num + 1' : 'comment_num = comment_num - 1';

            $sql = '
                UPDATE
                `bibi_feed`
                SET
                '.$condition.'
                WHERE
                `feed_id` = '.$feedId.';
            ';

        $this->exec($sql);

    }


    public function getCommentdetail($commentId=0, $feedId=0,$page=1,$userId=0){


         $sql = '
            SELECT
            t1.feed_id,t1.like_num,
            t1.comment_id,t1.content as comment_content,t1.created as comment_created,t1.reply_id as comment_reply_id,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM
            `bibi_comments` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.from_id = t2.user_id
            LEFT JOIN
            `bibi_user_profile` AS t3
            ON
            t1.to_id = t3.user_id
            WHERE 
        ';
            $sql .= ' t1.feed_id = '.$feedId.' AND t1.father_id = '.$commentId.' ';


          $pageSize = 10;
          $number = ($page - 1) * $pageSize;

           $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';


        $sqlCnt = '
                SELECT
                COUNT(t1.comment_id) as total
                FROM
                `bibi_comments` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.from_id = t2.user_id
                LEFT JOIN
                `bibi_user_profile` AS t3
                ON
                t1.to_id = t3.user_id
                WHERE
                `feed_id` = '.$feedId.' AND t1.father_id ='.$commentId.'
            ';

            $total = $this->query($sqlCnt)[0]['total'];

        $sql1='
            SELECT
            t1.feed_id,t1.like_num,
            t1.comment_id,t1.content as comment_content,t1.created as comment_created,t1.reply_id as comment_reply_id,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM
            `bibi_comments` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.from_id = t2.user_id
            LEFT JOIN
            `bibi_user_profile` AS t3
            ON
            t1.to_id = t3.user_id
            WHERE 
        ';
        $sql1 .= ' t1.feed_id = '.$feedId.' AND t1.comment_id = '.$commentId.' ';
       
        $comments_info=$this->query($sql1);
        $comments_father=$comments_info[0];
         
        $comments = $this->query($sql);
       
        $comments = $this->handleComments($comments,$userId);
        
        $count = count($comments);

        


        if($comments){

            $count = count($comments);

            $list["father_info"]=array();
            $list["father_info"]["feed_id"]=$comments_father["feed_id"];
            $list["father_info"]["like_num"]=$comments_father["like_num"];
            $list["father_info"]["comment_id"]=$comments_father["comment_id"];
            $list["father_info"]["comment_content"]=$comments_father["comment_content"];
            $list["father_info"]["comment_created"]=$comments_father["comment_created"];
            
            $list["father_info"]['from_user']["user_id"]=$comments_father["comment_from_user_id"];
            $list["father_info"]['from_user']["avatar"]=$comments_father["comment_from_avatar"];
            $list["father_info"]["from_user"]["nickname"]=$comments_father["comment_from_nickname"];
           
           
            // $carM = new CarSellingModel();
            // $carM->page = 1;
            // $car = $carM->getUsertoCar($comments_father["comment_from_user_id"]);

            // if(@$car['brand_info']){
            //      $list["father_info"]['brand_info']=$car['brand_info'];
            //  }else{
                
            //      $list["father_info"]['brand_info']=new stdClass();
            //  }

          $carM = new CarSellingModel();
            $carM->page = 1;
            $car = $carM->getUsertoCar($comments_father["comment_from_user_id"]);
            if($car){
               foreach($car as $val){
                $comments[$key]['brand_info']=$val;
              } 
            }
            
            
            //是否点赞
            $likeKey ='commentlike_'.$comments_father["feed_id"].'_'.$userId.'_'.$comments_father["comment_id"].'';
            Common::globalLogRecord('like key', $likeKey);
            $isLike = RedisDb::getValue($likeKey);
            $list["father_info"]['is_like']  = $isLike ? 1 : 2;
            
            $list['comment_list']=array();
            $list['comment_list'] = $comments;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;
            
            return $list;
        }
        else{

            return array() ;
        }


    }

    public function handleComments($comments,$userId){
        $items=array();
        foreach($comments as $key =>$value){
        $items[$key]['feed_id']=$value["feed_id"];
        $items[$key]['like_num']=$value["like_num"];
        $items[$key]['comment_id']=$value["comment_id"];

        //是否点赞
        $likeKey ='commentlike_'.$value["feed_id"].'_'.$userId.'_'.$value["comment_id"].'';
        Common::globalLogRecord('like key', $likeKey);
        $isLike = RedisDb::getValue($likeKey);
        $items[$key]['is_like']  = $isLike ? 1 : 2;

        $items[$key]['comment_content']=$value["comment_content"];
        $items[$key]['comment_created']=$value["comment_created"];
        $items[$key]['comment_reply_id']=$value["comment_reply_id"];
        $items[$key]['from_user']=array();

        $items[$key]['from_user']["user_id"]=$value["comment_from_user_id"];
        $items[$key]['from_user']["avatar"]=$value["comment_from_avatar"];
        $items[$key]["from_user"]["nickname"]=$value["comment_from_nickname"];

        $items[$key]["to_user"]["user_id"]=$value["comment_to_user_id"];
        $items[$key]["to_user"]["avatar"]=$value["comment_to_avatar"];
        $items[$key]["to_user"]["nickname"]=$value["comment_to_nickname"];


        }
        return $items;

    }


}