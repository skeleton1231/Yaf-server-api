<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午1:18
 */

class FileModel extends PdoDb{

    static public $table = 'bibi_files_list';


    public function __contsruct(){

        parent::__construct();

    }

    public function Create($data){

        $id = $this->insert(self::$table , $data);

        return $id;
    }

    //获取文件
    public function Get($hash){

        $sql = 'SELECT `url` FROM `bibi_files_list` WHERE `hash` =  "'.$hash.'" ';
        $file = $this->query($sql);

        $file_url = isset($file[0]) ? $file[0]['url'] : '';

        return $file_url;

    }

    public function GetMultiple($files_id){

        $fs = explode(',' , $files_id);
        $condition = '';
        foreach($fs as $k => $f){

            $condition .=  "'".trim($f)."'".',';
        }

        $condition = substr($condition, 0, -1);


        $sql = 'SELECT `hash` as file_id, url as file_url FROM `'.self::$table.'` WHERE `hash` in ('.$condition.')';
        $files = $this->query($sql);

        $files = $files ? $files : array();

        return $files;
    }
}