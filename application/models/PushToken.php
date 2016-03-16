<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/3/1
 * Time: 16:46
 */

class PushTokenModel extends  PdoDb{


    public $user_id;
    public $device_token;
    public $created;


    public function __construct(){

        parent::__construct();

        self::$table = 'bibi_push_token';
    }

    public function saveProperties(){

        $this->properties['user_id']        = $this->user_id;
        $this->properties['device_token']   = $this->device_token;
        $this->properties['created']        = $this->created;

    }


    public function delete(){

        $sql = ' DELETE FROM `'.self::$table.'` WHERE `user_id` = '.$this->user_id.' ';

        $this->execute($sql);
    }
}