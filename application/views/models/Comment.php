<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:44
 */


class CommentModel extends PdoDb {


    public $comment_id;
    public $feed_id;
    public $content;
    public $from_id;
    public $to_id;
    public $file_url = '';
    public $file_type = 1;
    public $created;
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
        $this->properties['file_url']   = $this->file_url;
        $this->properties['file_type']  = $this->file_type;
        $this->properties['created']    = $this->created;
    }

    public function getComment($commentId=0, $feedId=0, $page=1,$userId = 0){

        $sql = '
            SELECT
            t1.feed_id,
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
            LEFT JOIN
            `bibi_feeds` AS t4
            ON
            t1.feed_id = t4.feed_id
            WHERE
        ';

        $sqlCnt = '
             SELECT
            COUNT(t1.comment_id) AS total
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
            LEFT JOIN
            `bibi_feeds` AS t4
            ON
            t1.feed_id = t4.feed_id
            WHERE

        ';

        if($commentId && $feedId){

            $sql .= ' t1.feed_id = '.$feedId.' AND t1.comment_id = '.$commentId.' ';
        }
        elseif($userId){

            $sql .= ' t1.to_id = '.$userId.' AND t1.from_id != t1.to_id ';

            $sqlCnt .= ' t1.to_id = '.$userId.' AND t1.from_id != t1.to_id ';

            $total = $this->query($sqlCnt)[0]['total'];

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;

            $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

        }
        elseif($feedId){

            $sql .= ' t1.feed_id = '.$feedId.' ';
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
                `feed_id` = '.$feedId.'
            ';

            $total = $this->query($sqlCnt)[0]['total'];
        }


        $comments = $this->query($sql);

        $comments = $this->handleComment($comments);


        if(!$commentId){

            $count = count($comments);
            $list['comment_list'] = $comments;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;
        }
        else{

            return isset($comments[0]) ? $comments[0] : array() ;
        }

    }


    public function handleComment($comments){

        $items = array();

        $feedM = new FeedModel();
        foreach($comments as $item){

            $comment = $feedM->getFeedComment($item);
            $items[] = $comment;

        }

        return $items;
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
}