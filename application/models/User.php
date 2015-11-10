<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:11
 */

class UserModel extends PdoDb {

    static private $table = 'bibi_user';

    public function __contsruct(){

        parent::__construct();

    }

    public function register($data){

        $id = $this->insert(self::$table , $data);

        return $id;

    }

    public function getInfoByMobile($mobile){

        $table = self::$table;
        $sql = "SELECT * FROM {$table} WHERE `mobile` = :mobile";
        $param = array(':mobile'=>$mobile);
        $info = $this->query($sql,$param);

        return $info;
    }

    public function login($mobile , $password){

        $table = self::$table;
        $sql = "SELECT `user_id` FROM {$table} WHERE `mobile` = :mobile AND `password` = :password ";
        $param = array(':mobile'=>$mobile, ':password'=>$password);
        $info = $this->query($sql,$param);

        return $info;

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

    public static function userAuth($device_identifier , $user_id, $session_id){

        $id = RedisDb::getValue('auth_'.$device_identifier.'_'.$session_id.'');

        $result = $id == $user_id ? true : false;

        return $result;
    }

}

