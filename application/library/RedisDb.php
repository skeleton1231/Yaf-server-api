<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/24
 * Time: 下午11:29
 */

class RedisDb {

    static $instance;
    static $config;
    public static $key = 'bibi_';

//    public function __construct(){
//
//        $master = Yaf_Registry::get('config')->redis->master;
//
//        sefl::$config = array();
//        sefl::$config['scheme'] = $master->scheme;
//        sefl::$config['host']   = $master->host;
//        sefl::$config['port']   = $master->port;
//
//        //$this->instance = new Predis\Client($config);
//    }

    public static function getInstance(){

        if(is_null(self::$instance)){
            $master = Yaf_Registry::get('config')->redis->master;
            self::$config = array();
            self::$config['scheme'] = $master->scheme;
            self::$config['host']   = $master->host;
            self::$config['port']   = $master->port;
            //$options = array('replication' => true);
            self::$instance = new Predis\Client(self::$config);
        }

        return self::$instance;
    }


    public static function setValue($key, $value){

        $instance = self::getInstance();


        $return = $instance->set($key , $value);

    }

    public static function getValue($key){

        $instance = self::getInstance();

        return $instance->get($key);


    }

    public static function delValue($key){

        $instance = self::getInstance();

        $instance->del(array($key));
    }
} 