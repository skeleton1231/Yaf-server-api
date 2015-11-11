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

    public function GetMultiple($files_id){

        $sql = 'SELECT id as file_id, url as file_url FROM `'.self::$table.'` WHERE id in ('.$files_id.')';
        $files = $this->query($sql);

        return $files;
    }
}