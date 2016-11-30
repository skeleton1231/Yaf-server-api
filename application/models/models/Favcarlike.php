<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class FavcarlikeModel extends PdoDb {

    public $like_id;
    public $car_id;
    public $user_id;
    public $created;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_fav_car_like';
    }

    public function saveProperties(){

        $this->properties['car_id'] = $this->car_id;
        $this->properties['user_id'] = $this->user_id;
        $this->properties['created'] = $this->created;

    }

    public function getLike($userId=0 , $car_Id=0, $page=1){

        $sql = '
                SELECT
                t1.like_id,
                t1.user_id , t2.avatar, t2.nickname
                FROM
                `bibi_fav_car_like` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                WHERE
                t1.car_id = '."'".$car_Id."'".' ';

        if($userId){

            $sql .= ' AND t1.user_id = '.$userId.' ';
        }
        else{

            $sqlCnt = '
                SELECT
                COUNT(t1.like_id) AS total
                FROM
                `bibi_fav_car_like` AS t1
                LEFT JOIN
                `bibi_user_profile` AS t2
                ON
                t1.user_id = t2.user_id
                WHERE
                t1.car_id = '."'".$car_Id."'".'
            ';

            $pageSize = 10;
            $number = ($page - 1) * $pageSize;

            $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

            $total = $this->query($sqlCnt)[0]['total'];

        }

        $likes = $this->query($sql);
        
        return $likes;


    }



    public function deleteLike($feedId, $userId){

        $key = 'favoritecarlike_'.$favoriteId.'_'.$userId.'';

        $sql = 'DELETE FROM `bibi_favorite_car_like` WHERE `favorite_id`='.$favoriteId.' AND `user_id`='.$userId.'';

        $rs = $this->execute($sql);

        RedisDb::delValue($key);

        return $rs;
    }

}


