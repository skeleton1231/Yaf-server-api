<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:11
 */

class UserModel extends PdoDb {

    static public $table = 'bibi_user';

    public function __contsruct(){

        parent::__construct();

    }

    public function register($data){

        $id = $this->insert(self::$table , $data);

        return $id;

    }

    public function changepass($data){
        $where=array('mobile'=>$data['mobile']);
        unset($data['mobile']);
        $id =$this->updateByPrimaryKey(self::$table,$where,$data);
        return $id;
    }

    public function getInfoByMobile($mobile){

        $table = self::$table;
        $sql = "SELECT * FROM {$table} WHERE `mobile` = :mobile";
        $param = array(':mobile'=>$mobile);
        $info = $this->query($sql,$param);

        return $info;
    }

    public function getAllInfoById($userId){

        $sql = 'SELECT * FROM bibi_user WHERE `user_id` = :user_id';
        $param = array(':user_id'=>$userId);
        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }

    public function getInfoById($userId){

        $sql = 'SELECT `user_id` ,`username`, `mobile`, `created` FROM '.self::$table.' WHERE `user_id` = :user_id';
        $param = array(':user_id'=>$userId);
        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }

    public function login($mobile , $password){

        $table = self::$table;
        $sql = "SELECT `user_id` ,`username`, `mobile`, `created` FROM {$table} WHERE `mobile` = :mobile AND `password` = :password ";
        $param = array(':mobile'=>$mobile, ':password'=>$password);
        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }

    public function loginByOauth($data){

        $weibo_open_id = $data['weibo_open_id'];
        $wx_open_id = $data['wx_open_id'];
        $table = self::$table;

        $param = array();

        if($wx_open_id){

            $where = '`wx_open_id` = :wx_open_id';
            $param[':wx_open_id'] = $wx_open_id;
        }

        if($weibo_open_id){

            $where = '`weibo_open_id` = :weibo_open_id';
            $param[':weibo_open_id'] = $weibo_open_id;
        }

        $sql = "SELECT `user_id` ,`username`, `mobile`, `created` FROM
                {$table} WHERE {$where}";


        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }


    public static function setUserKeyCache($device_identifier , $user_id){

        $session_id = uniqid('session');

        $keyToUser = 'auth_'.$device_identifier.'_'.$session_id.'';
        $userToKey = 'key_'.$user_id.'';


        $oldAuth =  'auth_' . RedisDb::getValue($userToKey);

        RedisDb::delValue($oldAuth);
        RedisDb::delValue($keyToUser);
        RedisDb::delValue($userToKey);

        RedisDb::setValue($keyToUser, $user_id);
        RedisDb::setValue($userToKey, ''.$device_identifier.'_'.$session_id.'');



        return $session_id;

    }

    public function update($where, $data){

        $this->updateByPrimaryKey(self::$table, $where, $data);

    }

    public function getProfileInfoById($userId){


        $userInfo = $this->getInfoById($userId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile($userId);

        $userInfo['profile'] = $profile;

        return $userInfo;

    }

    public function updateGeoById($userId, $lat , $lng){

        $geohashM = new GeoHash();
        $geohash = $geohashM->encode($lat, $lng);

        $sql = '
            UPDATE
            `bibi_user`
            SET
            `lat` = '.$lat.',
            `lng` = '.$lng.',
            `geohash` = "'.$geohash.'"
            WHERE
            `user_id` = '.$userId.'
        ';

        $this->exec($sql);

        return $geohash;
    }


//    public static function userAuth($device_identifier , $user_id, $session_id){
//
//        $id = RedisDb::getValue('auth_'.$device_identifier.'_'.$session_id.'');
//
//        $result = $id == $user_id ? true : false;
//
//        return $result;
//    }

}

