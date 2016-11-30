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
        $sql = "SELECT avatar, signature, age, constellation, nickname, gender FROM {$table} WHERE `user_id` = :user_id";
        $profile = $this->query($sql, array(':user_id'=>$user_id));
        $profile = $profile[0];

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

}