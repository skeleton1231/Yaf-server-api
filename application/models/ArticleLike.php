<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class ArticleLikeModel extends PdoDb {

    public $like_id;
    public $article_id;
    public $user_id;
    public $created;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_article_likes';
    }

    public function saveProperties(){

        $this->properties['article_id'] = $this->article_id;
        $this->properties['user_id'] = $this->user_id;
        $this->properties['created'] = $this->created;

    }

    public function getLike($userId=0 , $articleId=0, $page=1){


        $sql = '
                SELECT
                t1.like_id,t1.created,t1.article_id,
                t2.user_id AS like_user_id, t2.avatar AS like_avatar, t2.nickname AS like_nickname
                FROM
                `bibi_article_likes` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                WHERE
                t1.article_id = '.$articleId.'
        ';
        if($userId){

            $sql .= ' AND t1.user_id = '.$userId.' ';
        }
        else{

        $sqlCnt = '
                SELECT
                COUNT(t1.like_id) AS total
                FROM
                `bibi_article_likes` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                WHERE
                t1.article_id = '.$articleId.'
            ';

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;

            $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

            $total = $this->query($sqlCnt)[0]['total'];
        }
          
            $likes = $this->query($sql);

            

        if(!$userId){

            $count = count($likes);



            $list['like_list'] = $likes;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;

        }
        else{

            return isset($likes[0]) ? $likes[0] : array() ;

        }


    }


     public function updateLikeNum($articleId, $action='add'){

        $condition = $action == 'add' ? 'like_num = like_num + 1' : 'like_num = like_num - 1';

        $sql = '
            UPDATE
            `bibi_article`
            SET
            '.$condition.'
            WHERE
            `article_id` = '.$articleId.'
            ;
        ';

        $this->exec($sql);

    }


    public function handleLike($likes){

        $feedM = new FeedModel();
        $items = array();

        foreach($likes as $k => $item){

            $like = $feedM->getFeedLike($item);
            $items[] = $like;
        }

        return $items;
    }


    public function deleteLike($articleId, $userId){

        $key = 'articlelike_'.$articleId.'_'.$userId.'';

        $sql = 'DELETE FROM `bibi_article_likes` WHERE `article_id`='.$articleId.' AND `user_id`='.$userId.'';

        $rs = $this->execute($sql);

        RedisDb::delValue($key);

        return $rs;
    }

}


