<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/12
 * Time: 下午6:21
 */


class FriendShipModel extends PdoDb {


    public $friendship_id;
    public $friend_id;
    public $user_id;
    public $created;

    public function __construct(){

        parent::__construct();

        self::$table = 'bibi_friendship';
    }

    public function saveProperties(){

        $this->properties['friend_id']   = $this->friend_id;
        $this->properties['user_id']     = $this->user_id;
        $this->properties['created']     = $this->created;

    }

    public function getMyFriendShip($userId, $friendId=0, $page=0){

        $sql = '
            SELECT
            t1.friendship_id,t1.created,
            t2.user_id AS friendship_user_id,
            t2.avatar  AS friendship_avatar,
            t2.nickname AS friendship_nickname
            FROM
            `bibi_friendship` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.friend_id = t2.user_id
            WHERE t1.user_id = '.$userId.'
        ';



        if($friendId){

            $sql .= ' AND t1.friend_id = '.$friendId.' ';
        }
        else{

            $sqlCnt = '

            SELECT
            COUNT(t1.friendship_id) AS total
            FROM
            `bibi_friendship` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.friend_id = t2.user_id
            WHERE t1.user_id = '.$userId.'

            ';

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;

            $sql .= ' ORDER BY t1.created DESC   LIMIT ' . $number . ' , ' . $pageSize . '';

            $total = $this->query($sqlCnt)[0]['total'];
        }

        $friendships = $this->query($sql);


        if($friendships){

            $friendships = $this->handleFriendShip($friendships);

        }


        if(!$friendId){

            $count = count($friendships);
            $list['friendship_list'] = $friendships;
            $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $list['total'] = $total;

            return $list;

        }
        else{

            if(isset($friendships[0])){


                return $friendships[0];
            }
            else{

                return array();
            }
            //return isset($friendships[0]) ? $friendships[0] : array() ;

        }

    }

    //我的粉丝
    public function getFriendShipToMe($userId, $friendId=0, $page=0){


        $sql = '
            SELECT
            t1.friendship_id,t1.created,
            t2.user_id AS friendship_user_id,
            t2.avatar  AS friendship_avatar,
            t2.nickname AS friendship_nickname
            FROM
            `bibi_friendship` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.user_id = t2.user_id
            WHERE t1.friend_id = '.$userId.'
        ';


        $sqlCnt = '

            SELECT
            COUNT(t1.friendship_id) AS total
            FROM
            `bibi_friendship` AS t1
            LEFT JOIN
            `bibi_user_profile` AS t2
            ON
            t1.user_id = t2.user_id
            WHERE t1.friend_id = '.$userId.'

            ';

        $pageSize = 10;
        $number = ($page - 1) * $pageSize;

        $sql .= ' ORDER BY t1.created DESC   LIMIT ' . $number . ' , ' . $pageSize . '';

        $total = $this->query($sqlCnt)[0]['total'];

        $friendships = $this->query($sql);

        $friendships = $this->handleFriendShip($friendships);

        $count = count($friendships);
        $list['friendship_list'] = $friendships;
        $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;

    }


    public function handleFriendShip($friendShips){

        $items = array();

        foreach($friendShips as $k => $friendShip){

            if ($friendShip['friendship_id']) {

                $item = array();
                $item['friendship_id'] = $friendShip['friendship_id'];
                $item['user_info']['user_id'] = $friendShip['friendship_user_id'];
                $item['user_info']['profile']['avatar'] = $friendShip['friendship_avatar'];
                $item['user_info']['profile']['nickname'] = $friendShip['friendship_nickname'];

                $items[] = $item['user_info'];

            }
        }

        return $items;
    }

//    public function isFriend($userId=0, $friendId=0){
//
//
//        $isFriend = RedisDb::getValue($key);
//
//        return $isFriend ? $isFriend : 0;
//
//    }
//    
     public function isFriend($fromId, $userId){

       $sql = '
            SELECT
            friendship_id
            FROM
            `bibi_friendship` 
            WHERE user_id = '.$fromId.' AND friend_id=
        '.$userId;
        $friendship= $this->query($sql);
        return $friendship;
   }
  





    public function friendNumCnt(){

        $sql  = 'SELECT
                  COUNT(friendship_id) AS friend_num
                  FROM `bibi_friendship` WHERE `user_id` = '.$this->currentUser.'';


        $friendNum = $this->query($sql)[0]['friend_num'];

        return $friendNum;

    }

    public function fansNumCnt(){

        $sql  = 'SELECT
                  COUNT(friendship_id) AS fans_num
                  FROM `bibi_friendship` WHERE `friend_id` = '.$this->currentUser.'';


        $fans_num = $this->query($sql)[0]['fans_num'];

        return $fans_num;

    }

    public function deleteFriendShip($friendId, $userId){

        $sql  = '
                DELETE FROM `bibi_friendship` WHERE `friend_id` = '.$friendId.'
                AND `user_id` = '.$userId.'
        ';

        $this->execute($sql);

    }

}