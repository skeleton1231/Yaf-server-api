<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class FeedModel extends PdoDb
{


    public $user_id;
    public $feed_id;
    public $feed_type = 1;
    public $post_content;
    public $post_files;
    public $comment_num = 0;
    public $like_num = 0;
    public $image_url;
    public $html_url;
    public $created;
    public $updated;
    public $lat = 0.00;
    public $lng = 0.00;
    public $is_delete = 1;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_feeds';
    }

    public function saveProperties()
    {

        //$this->properties['feed_id']        = $this->feed_id;
        $this->properties['feed_type'] = $this->feed_type;
        $this->properties['user_id'] = $this->user_id;
        $this->properties['post_content'] = $this->post_content;
        $this->properties['post_files'] = $this->post_files;
        $this->properties['comment_num'] = $this->comment_num;
        $this->properties['like_num'] = $this->like_num;
        $this->properties['image_url'] = $this->image_url;
        $this->properties['html_url'] = $this->html_url;
        $this->properties['created'] = $this->created;
        $this->properties['updated'] = $this->updated;
        $this->properties['lat'] = $this->lat;
        $this->properties['lng'] = $this->lng;
        $this->properties['is_delete'] = $this->is_delete;

    }

    public function getFeeds($feedId = 0, $type = 0, $page = 1)
    {

        $action = 'list';

        $sql = '
                 SELECT
                 t1.feed_id,t1.feed_type,t1.created,t1.post_content,t1.post_files,t1.comment_num,t1.like_num,
                 t2.user_id, t2.avatar, t2.nickname,
                 t3.comment_id,t3.content as comment_content,t3.created as comment_created,t3.reply_id as comment_reply_id,
                 t4.user_id AS comment_from_user_id, t4.avatar AS comment_from_avatar, t4.nickname AS comment_from_nickname,
                 t5.user_id AS comment_to_user_id, t5.avatar AS comment_to_avatar, t5.nickname AS comment_to_nickname,
                 t6.like_id,t7.user_id AS like_user_id,t7.avatar AS like_avatar,t7.nickname AS like_nickname
                 FROM `bibi_feeds` AS t1
                 LEFT JOIN
                 `bibi_user_profile` AS t2
                 ON t1.user_id = t2.user_id
                 LEFT JOIN
                 `bibi_comments` AS t3
                 ON t1.feed_id = t3.feed_id
                 LEFT JOIN
                 `bibi_user_profile` AS t4
                 ON t3.from_id = t4.user_id
                 LEFT JOIN
                 `bibi_user_profile` AS t5
                 ON t3.to_id = t5.user_id
                 LEFT JOIN
                 `bibi_likes` AS t6
                 ON t1.feed_id = t6.feed_id
                 LEFT JOIN
                 `bibi_user_profile` AS t7
                 ON t6.user_id = t7.user_id
                ';

        if ($feedId) {

            $sql .= ' WHERE t1.feed_id = ' . $feedId . ' '; //ORDER BY t3.comment_id DESC

            $action = 'detail';

        } else {

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;


            switch ($type) {

                //like_num DESC, comment_num DESC, feed_id DESC

                case 1:
                    //热门消息
                    $sqlHot = '
                        SELECT
                        t1.feed_id
                        FROM
                        `bibi_feeds` AS t1
                        ORDER BY
                        like_num DESC, comment_num DESC, feed_id DESC
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';


                    $sqlHotCnt = '
                        SELECT
                        COUNT(t1.feed_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        ORDER BY
                        like_num DESC, comment_num DESC, feed_id DESC
                    ';


                    $total = $this->query($sqlHotCnt)[0]['total'];

                    $result = @$this->query($sqlHot);
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' . $result . ') '; //ORDER BY t3.comment_id DESC

                    break;

                case 3:
                    //最新消息
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
                        COUNT(t1.feed_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        ORDER BY created  DESC
                    ';

                    $total = $this->query($sqlLatestCnt)[0]['total'];

                    $result = @$this->query($sqlLatest);
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' . $result . ') ORDER BY `feed_id` DESC ';

                    break;

                case 2:
                    //关注
                    $sqlFriend = '
                        SELECT
                        t1.feed_id
                        FROM
                        `bibi_feeds` AS t1
                        LEFT JOIN
                        `bibi_friendship` AS t2
                        ON
                        t1.user_id = t2.friend_id
                        LEFT_JOIN
                        `bibi_user_profile` AS t3
                        ON
                        t1.user_id = t3.user_id
                        WHERE
                        t2.user_id = '.$this->currentUser.'
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';

                    $sqlFriendCnt = '
                        SELECT
                        COUNT(t1.feed_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        LEFT JOIN
                        `bibi_friendship` AS t2
                        ON
                        t1.user_id = t2.friend_id
                        LEFT JOIN
                        `bibi_user_profile` AS t3
                        ON
                        t1.user_id = t3.user_id
                        WHERE
                        t2.user_id = '.$this->currentUser.'
                    ';


                    $total = $this->query($sqlFriendCnt)[0]['total'];

                    $result = @$this->query($sqlFriend);
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' . $result . ')  ORDER BY `feed_id` DESC ';

                    break;


                case 6:

                    $userM = new UserModel();
                    $info = $userM->getAllInfoById($this->currentUser);
                    $geohash = $info['geohash'];
                    $geohash = substr($geohash, 0, 5);

                    //附近
                    $sqlNearBy = '
                        SELECT
                        t1.feed_id
                        FROM
                        `bibi_feeds` AS t1
                        LEFT JOIN
                        `bibi_users` AS t2
                        ON
                        t1.user_id = t2.user_id
                        WHERE
                        t2.geohash LIKE "'.$geohash.'%"
                    ';

                    $sqlNearByCnt = '
                        SELECT
                        COUNT(t1.feed_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        LEFT JOIN
                        `bibi_users` AS t2
                        ON
                        t1.user_id = t2.user_id
                        WHERE
                        t2.geohash LIKE "'.$geohash.'%"
                    ';

                    $total = $this->query($sqlNearByCnt)[0]['total'];

                    $result = @$this->query($sqlNearBy);
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' . $result . ') ';


                    break;


                case 5:
                    //用户发布的朋友圈
                    $sqlMine = '
                        SELECT
                        t1.feed_id
                        FROM
                        `bibi_feeds` AS t1
                        WHERE t1.user_id = '.$this->currentUser.'
                        ORDER BY created DESC
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';


                    $sqlMineCnt = '
                        SELECT
                        COUNT(t1.feed_id) AS total
                        FROM
                        `bibi_feeds` AS t1
                        WHERE t1.user_id = '.$this->currentUser.'
                        ORDER BY created  DESC
                    ';

                    $total = $this->query($sqlMineCnt)[0]['total'];

                    $result = @$this->query($sqlMine);
                    $result = $this->implodeArrayByKey('feed_id', $result);

                    $sql .= ' WHERE t1.feed_id in (' . $result . ') ';

                    break;
            }


        }


        $feeds = $this->query($sql);

        $feeds = $this->handleFeed($feeds,$action);


        if(!$feedId){

            $count = count($feeds);

            $list['feed_list'] = $feeds;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;
        }
        else{

            return isset($feeds[0]) ? $feeds[0] : array() ;
        }



    }

    public function handleFeed($feeds, $action='list')
    {

        $items = array();

        foreach ($feeds as $k => $feed) {

            if ($feed['user_id']) {

                $items[$feed['feed_id']]['post_user_info'] = array();
                $items[$feed['feed_id']]['post_user_info']['user_id'] = $feed['user_id'];
                $items[$feed['feed_id']]['post_user_info']['profile']['avatar'] = $feed['avatar'];
                $items[$feed['feed_id']]['post_user_info']['profile']['nickname'] = $feed['nickname'];

            } else {

                $items[$feed['feed_id']]['post_user_info'] = new stdClass();
            }

            $items[$feed['feed_id']]['feed_id'] = $feed['feed_id'];
            $items[$feed['feed_id']]['feed_type'] = $feed['feed_type'];
            $items[$feed['feed_id']]['post_content'] = $feed['post_content'];
            $items[$feed['feed_id']]['comment_num'] = $feed['comment_num'];
            $items[$feed['feed_id']]['like_num'] = $feed['like_num'];
            $items[$feed['feed_id']]['created'] = $feed['created'];


            if ($feed['post_files']) {

                $images = unserialize($feed['post_files']);

                $postFiles = array();

                foreach ($images as $k => $image) {

                    $item = array();
                    $item['file_id'] = $image['hash'];
                    $item['file_url'] = IMAGE_DOMAIN . $image['key'];
                    $item['file_type'] = $image['type'] ? $image['type'] : 0;
                    $postFiles[] = $item;
                }

            }

            $items[$feed['feed_id']]['post_files'] = $postFiles;


            $items[$feed['feed_id']]['comment_list'] =
                isset($items[$feed['feed_id']]['comment_list'])
                    ? $items[$feed['feed_id']]['comment_list']
                    : array();

            $comment = $this->getFeedComment($feed);
            $commentIds = isset($commentIds) ? $commentIds : array();

            if ($comment && !in_array($comment['comment_id'], $commentIds)) {

                $items[$feed['feed_id']]['comment_list'][] = $comment;
                $commentIds[] = $comment['comment_id'];

            }

            $items[$feed['feed_id']]['like_list'] =
                isset($items[$feed['feed_id']]['like_list'])
                    ? $items[$feed['feed_id']]['like_list']
                    : array();

            $likeIds = isset($likeIds) ? $likeIds : array();

            $like = $this->getFeedLike($feed);

            if ($like && !in_array($like['like_id'], $likeIds)) {

                $items[$feed['feed_id']]['like_list'][] = $like['user_info'];
                $likeIds[] = $like['like_id'];
            }


        }

        $list = array();

        foreach ($items as $key => $item) {

            $item['car_info'] = $this->getFeedRelateCar($item);

            $commentTotal = count($item['comment_list']);

            $commentList = array();

            if($action=='list'){

                foreach ($item['comment_list'] as $k => $cl) {

                    if ($k < 10) {

                        $commentList[] = $cl;
                    } else {

                       break;
                    }
                }

                $item['comment_list'] = $commentList;
            }


            $item['comment_num'] = $commentTotal;

            $likeList = array();
            $likeTotal = count($item['like_list']);

            foreach ($item['like_list'] as $k => $ll) {

                if ($k < 4) {

                    $likeList[] = $ll;
                } else {

                    break;
                }
            }

            $item['like_list'] = $likeList;

            $item['like_num'] = $likeTotal;

            $isLike = RedisDb::getValue('like_'.$item['feed_id'].'_'.$this->currentUser.'');

            $item['is_like']  = $isLike ? $isLike : 2;


            $list[] = $item;
        }

        return $list;
    }

    public function getFeedComment($feed)
    {


        if ($feed['comment_id']) {

            $comment['comment_id'] = $feed['comment_id'];
            $comment['feed_id']    = $feed['feed_id'];

            $comment['content'] = $feed['comment_content'];

            $comment['reply_id'] = 0;

            if($feed['comment_reply_id']) {
                $comment['reply_id'] = $feed['comment_reply_id'];
            }

            $comment['created'] = $feed['comment_created'];

            $comment['from_user_info'] = array();
            $comment['from_user_info']['user_id'] = $feed['comment_from_user_id'];
            $comment['from_user_info']['profile']['avatar'] = $feed['comment_from_avatar'];
            $comment['from_user_info']['profile']['nickname'] = $feed['comment_from_nickname'];


            if($comment['reply_id']){

                $comment['to_user_info'] = array();
                $comment['to_user_info']['user_id'] = $feed['comment_to_user_id'];
                $comment['to_user_info']['profile']['avatar'] = $feed['comment_to_avatar'];
                $comment['to_user_info']['profile']['nickname'] = $feed['comment_to_nickname'];
            }
            else{

                $comment['to_user_info'] = new stdClass();
            }


            return $comment;

        } else {

            return array();
        }
    }

    public function getFeedLike($feed)
    {

        if ($feed['like_id']) {

            $like = array();
            $like['like_id'] = $feed['like_id'];
            $like['user_info']['user_id'] = $feed['like_user_id'];
            $like['user_info']['profile']['avatar'] = $feed['like_avatar'];
            $like['user_info']['profile']['nickname'] = $feed['like_nickname'];

            return $like;

        } else {

            return array();
        }
    }

    public function getFeedRelateCar($feed)
    {


        $userId = $feed['post_user_info']['user_id'];

        $carM = new CarSellingModel();

        $carM->page = 1;

        $cars = $carM->getUserPublishCar($userId);


        if ($cars) {

//            $num = count($cars['car_list']);
//
//            $rand = rand(0, $num - 1);

            return @$cars['car_list'][0]['car_info'];
        } else {

            return new \stdClass();
        }

    }

    public function serializePostFiles($postFiles)
    {

        $files = array();

        $postFiles = json_decode($postFiles, true);

        foreach ($postFiles as $k => $postFile) {

            $file = array();
            $file['hash'] = $postFile;
            $file['key'] = $postFile;
            $file['type'] = 1;

            $files[] = $file;
        }

        return serialize($files);

    }

    public function updateCommentNum($feedId){

        $sql = '
            UPDATE
            `bibi_feeds`
            SET
            comment_num = comment_num + 1
            WHERE
            `feed_id` = '.$feedId.'
            ;
        ';

        $this->exec($sql);

    }


    public function updateLikeNum($feedId){

        $sql = '
            UPDATE
            `bibi_feeds`
            SET
            like_num = like_num + 1
            WHERE
            `feed_id` = '.$feedId.'
            ;
        ';

        $this->exec($sql);

    }

    public function deleteFeed($feedId){

        $sql = ' DELETE FROM `bibi_feeds` WHERE `feed_id` = '.$feedId.' AND `user_id` = '.$this->currentUser.' ';

        echo $sql;exit;

        $this->execute($sql);
    }



}