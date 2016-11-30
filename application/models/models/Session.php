<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午2:36
 */

class SessionModel extends PdoDb{


    static public $table = 'bibi_session';

    public function __contsruct(){

        parent::__construct();

    }

    public function Create($data){


        $session_id = uniqid('session');
        $device_identifier = $data['device_identifier'];
        $user_id = $data['user_id'];

        $keyToUser = $device_identifier.'_'. $session_id;
        RedisDb::delValue($keyToUser);

        //$userToKey = 'key_'.$user_id.'';
        //$oldAuth =  'auth_' . RedisDb::getValue($userToKey);
        //RedisDb::delValue($oldAuth);
        //RedisDb::delValue($keyToUser);
        //RedisDb::delValue($userToKey);
        //RedisDb::setValue($userToKey, ''.$device_identifier.'_'.$session_id.'');
        $time = time();
        $data['created'] = $time;
        $data['updated'] = $time;
        $data['session_id'] = $session_id;
        $id = $this->insert(self::$table , $data);

        RedisDb::setValue($keyToUser, $user_id);

        return $session_id;

    }

    public function Get($data){

        $device_identifier = $data['device_identifier'];
        $session_id = @$data['session_id'];

        if(!$session_id){

            return 0;
        }

        $keyToUser = $device_identifier.'_'. $session_id;
        $user_id = RedisDb::getValue($keyToUser);

        if(!$user_id){

            $table = self::$table;
            //$sql = "SELECT `user_id` FROM `{$table}` WHERE `session_id` = '{$session_id}' AND `device_identifier` = '{$device_identifier}' ";
            $sql = "SELECT `user_id` FROM `{$table}` WHERE `session_id` = :session_id AND `device_identifier` = :device_identifier ";
            $param = array(':session_id'=>$session_id, ':device_identifier'=>$device_identifier);
            $info = $this->query($sql,$param);
            $user_id = @$info[0]['user_id'];

            RedisDb::setValue($keyToUser, $user_id);

        }

        return $user_id;

    }
}