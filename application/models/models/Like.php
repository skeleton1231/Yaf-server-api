<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class LikeModel extends PdoDb {

    public $like_id;
    public $feed_id;
    public $user_id;
    public $created;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_likes';
    }

    public function saveProperties(){

        $this->properties['feed_id'] = $this->feed_id;
        $this->properties['user_id'] = $this->user_id;
        $this->properties['created'] = $this->created;

    }

    public function getLikeToMe($page=1){

        $sql = '
                SELECT
                t1.like_id,t1.created,t1.feed_id,
                t2.user_id AS like_user_id, t2.avatar AS like_avatar, t2.nickname AS like_nickname,
                t3.post_files
                FROM
                `bibi_likes` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                LEFT JOIN
                `bibi_feeds` AS t3
                ON
                t1.feed_id = t3.feed_id
                WHERE
                t3.user_id = '.$this->currentUser.'
                AND
                t3.user_id != t1.user_id
                ORDER BY t1.created DESC 
        ';

        $sqlCnt = '
                SELECT
                COUNT(t1.like_id) AS total
                FROM
                `bibi_likes` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                LEFT JOIN
                `bibi_feeds` AS t3
                ON
                t1.feed_id = t3.feed_id
                WHERE
                t3.user_id = '.$this->currentUser.'
                AND
                t3.user_id != t1.user_id
        ';

        $pageSize = 10;
        $number = ($page - 1) * $pageSize;

        $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

        $total = $this->query($sqlCnt)[0]['total'];

        $likes = $this->query($sql);

        $likes = $this->handleLike($likes);

        foreach($likes as $key =>$value){

             if ($value['post_files']) {

                $images = unserialize($value['post_files']);

                $postFiles = array();

                foreach ($images as $k => $image) {

                   
                    $likes[$key]['file_id'] = $image['hash'];
                    $likes[$key]['file_url'] = IMAGE_DOMAIN . $image['key'];
                }

            }
            


        }

        $count = count($likes);

        $list = array();

        $list['like_list'] = $likes;
        $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;

    }


    public function getLike($userId=0 , $feedId=0, $page=1){


        $sql = '
                SELECT
                t1.like_id,
                t2.user_id AS like_user_id, t2.avatar AS like_avatar, t2.nickname AS like_nickname
                FROM
                `bibi_likes` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                WHERE
                t1.feed_id = '.$feedId.'
        ';

        if($userId){

            $sql .= ' AND t1.user_id = '.$userId.' ';
        }
        else{

            $sqlCnt = '
                SELECT
                COUNT(t1.like_id) AS total
                FROM
                `bibi_likes` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                WHERE
                t1.feed_id = '.$feedId.'
            ';

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;

            $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

            $total = $this->query($sqlCnt)[0]['total'];

        }


        $likes = $this->query($sql);

        $likes = $this->handleLike($likes);

        if(!$userId){

            $count = count($likes);

            $list = array();

            foreach($likes as $like){

                $item[] = $like['user_info'];
            }

            $list['like_list'] = $item;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;

        }
        else{

            return isset($likes[0]) ? $likes[0] : array() ;

        }


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


    public function deleteLike($feedId, $userId){

        $key = 'like_'.$feedId.'_'.$userId.'';

        $sql = 'DELETE FROM `bibi_likes` WHERE `feed_id`='.$feedId.' AND `user_id`='.$userId.'';

        $rs = $this->execute($sql);

        RedisDb::delValue($key);

        return $rs;
    }

}


