<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:30
 */


class ProfileModel extends PdoDb{

    static public $table = 'bibi_user_profile';

    public function __construct(){

        parent::__construct();
    }

    public function initProfile($data){

        $this->insert(self::$table , $data);
    }


    public function updateProfileByKey($user_id , $data){

        $where = array('user_id' => $user_id);

        $result = $this->updateByPrimaryKey(self::$table, $where, $data);
        return $result;
    }

    public function getProfile($user_id){
        $table = self::$table;
        $sql = "SELECT avatar, signature, age, constellation, nickname, gender, bibi_no FROM {$table} WHERE `user_id` = :user_id";
        $profile = $this->query($sql, array(':user_id'=>$user_id));
        @$profile = $profile[0];

//        if($profile['year'] == 0 || $profile['month'] == 0 || $profile['day'] == 0){
//
//            $profile['birth'] = '';
//        }
//        else{
//
//            $profile['birth'] = $profile['year'] . '-' . $profile['month'] . '-' . $profile['day'];
//        }
//
//        unset($profile['year']);
//        unset($profile['month']);
//        unset($profile['day']);


        return $profile;
    }



    public function getUserInfos($users){

        if($users){

            $str = '(' . implode(',' , $users) . ')';

            $sql = 'SELECT `user_id`, `nickname`, `avatar` FROM `bibi_user_profile` WHERE `user_id` in '.$str.'';

            $results = $this->query($sql);

            return $results;
        }
        else{

            return array();
        }

    }


    public function gethotgirl($page=1,$userId=0){

        $pageSize = 24;
        $number = ($page-1) * $pageSize;

        if($page < 5){

        $sql = 'SELECT 
                  user_id,nickname,avatar,sort FROM `bibi_user_profile` 
                   ORDER BY sort DESC LIMIT ' . $number . ' , ' . $pageSize .'';       
        $users = $this->query($sql);

        $total = 100;

        $items['list']=array();
        $items['list']=$users;
        $count = count($users);

        $items['has_more'] = (($number+$count+24) < $total) ? 1 : 2;
        $items['total'] = $total;

        }else{

        $items['list']=new stdClass();
        $total = 100;
        $items['has_more'] = 2;
        $items['total'] = $total;

        }
        return  $items;

    }

}