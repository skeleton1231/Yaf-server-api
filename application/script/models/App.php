<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午10:42
 */

class AppModel extends PdoDb{


    static public  $table = 'bibi_device_info';

    public function init(){

        parent::__construct();
    }

    public function registerDevice($data){

        $id = $this->insert(self::$table , $data);

        RedisDb::setValue('di_'.$data['device_identifier'].'', true);


        return $id;

    }

    public function getDevice($device_identifier){


        $di = RedisDb::getValue('di_'.$device_identifier.'');

        if(!$di){

            $table = self::$table;
            //查找是否有该device_identifier
            $sql = "SELECT id FROM {$table} WHERE `device_identifier` = :device_identifier";

            $result = $this->query($sql, array(':device_identifier'=>$device_identifier));

            return $result;

        }
        else{

            return $di;
        }


    }



} 