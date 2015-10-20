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

}

