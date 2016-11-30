<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:44
 */


class ArticleCommentModel extends PdoDb {


    public $comment_id;
    public $article_id;
    public $content;
    public $from_id;
    public $to_id;
    public $file_url = '';
    public $file_type = 1;
    public $created;
    public $is_reply;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_article_comments';
    }

    public function saveProperties(){

        //$this->properties['comment_id'] = $this->comment_id;
        $this->properties['article_id'] = $this->article_id;
        $this->properties['content']    = $this->content;
        $this->properties['from_id']    = $this->from_id;
        $this->properties['to_id']      = $this->to_id;
        $this->properties['reply_id']   = $this->reply_id;
        $this->properties['created']    = $this->created;
    }

    public function getComment($articleId=0, $page=1){

        $sql = '
            SELECT
            t1.article_id,t1.like_num,
            t1.comment_id,t1.content as comment_content,t1.created as comment_created,t1.reply_id as comment_reply_id,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM
            `bibi_article_comments` AS t1
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
     
     if($articleId){

            $sql .= ' t1.reply_id = 0 AND t1.article_id = '.$articleId.' ';

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;
            

            $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';


            $sqlCnt = '
                SELECT
                COUNT(t1.comment_id) as total
                FROM
                `bibi_article_comments` AS t1
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
                `article_id` = '.$articleId.'
            ';

            $total = $this->query($sqlCnt)[0]['total'];
        }
      

        $comments = $this->query($sql);
        
        $comments = $this->handleComment($comments);


        if($comments){

            $count = count($comments);
            $list['comment_list'] = $comments;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;
        }
        else{

            return  array() ;
        }

    }
    
    public function getCommentToComment($commentId=0, $articleId=0, $page=1,$userId = 0){

        $sql = '
             SELECT
            t1.article_id,t1.like_num,
            t1.comment_id,t1.content as comment_content,t1.created as comment_created,t1.reply_id as comment_reply_id,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM
            `bibi_article_comments` AS t1
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
            $sql .= ' t1.article_id = '.$articleId.' AND t1.reply_id = '.$commentId.' ';
            $pageSize = 10;
            $number = ($page - 1) * $pageSize;

           $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';


            $sqlCnt = '
                SELECT
                COUNT(t1.comment_id) as total
                FROM
                `bibi_article_comments` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.from_id = t2.user_id
                LEFT JOIN
                `bibi_user_profile` AS t3
                ON
                t1.to_id = t3.user_id
                WHERE
                `article_id` = '.$articleId.' AND t1.reply_id ='.$commentId.'
            ';

            $total = $this->query($sqlCnt)[0]['total'];
        


        $comments = $this->query($sql);
       
        $comments = $this->handleComment($comments);


        if($comments){

            $count = count($comments);
            $list['comment_list'] = $comments;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;
        }
        else{

            return array() ;
        }

    }

    public function handleComment($comments){

        foreach($comments as $key =>$value){
                $comments[$key]["from_user"]=array();
                $comments[$key]["to_user"]=array();
                $comments[$key]['from_user']["user_id"]=$value["comment_from_user_id"];
                $comments[$key]['from_user']["avatar"]=$value["comment_from_avatar"];
                $comments[$key]['from_user']["nickname"]=$value["comment_from_nickname"];
                $comments[$key]['to_user']["user_id"]=$value["comment_to_user_id"];
                $comments[$key]['to_user']["avatar"]=$value["comment_to_avatar"];
                $comments[$key]['to_user']["nickname"]=$value["comment_to_nickname"];
                unset($comments[$key]["comment_from_user_id"]);
                unset($comments[$key]["comment_from_avatar"]);
                unset($comments[$key]["comment_from_nickname"]);
                unset($comments[$key]["comment_to_user_id"]);
                unset($comments[$key]["comment_to_avatar"]);
                unset($comments[$key]["comment_to_nickname"]);
        }

        return $comments;
    }


    public function deleteComment(){

        $sql = '
                DELETE FROM `bibi_article_comments`
                WHERE
                `comment_id` = '.$this->comment_id.'
                AND
                `from_id` = '.$this->currentUser.'
                AND
                `article_id` = '.$this->article_id.'
                ';

        $this->execute($sql);
    }


    public function updateCommentNum($articleId, $action='add'){

        $condition = $action == 'add' ? 'post_num = post_num + 1' : 'post_num = post_num - 1';

            $sql = '
                UPDATE
                `bibi_article`
                SET
                '.$condition.'
                WHERE
                `article_id` = '.$articleId.';
            ';

        $this->exec($sql);

    }


    public function getCommentdetail($commentId=0, $articleId=0){

         $sql = '
             SELECT
            t1.article_id,t1.like_num,
            t1.comment_id,t1.content as comment_content,t1.created as comment_created,t1.reply_id as comment_reply_id,
            t2.user_id AS comment_from_user_id, t2.avatar AS comment_from_avatar, t2.nickname AS comment_from_nickname,
            t3.user_id AS comment_to_user_id, t3.avatar AS comment_to_avatar, t3.nickname AS comment_to_nickname
            FROM
            `bibi_article_comments` AS t1
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
            $sql .= ' t1.article_id = '.$articleId.' AND t1.comment_id = '.$commentId.' ';

        $comments = $this->query($sql);
       
        $comments = $this->handleComment($comments);


        if($comments){

           

            return $comments[0];
        }
        else{

            return array() ;
        }


    }


}