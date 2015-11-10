<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午1:18
 */

class FileModel extends PdoDb{


    public function __contsruct(){

        parent::__construct();

    }

    public function insert($data){

        $id = $this->insert(self::$table , $data);

        return $id;
    }

    public function update(){

        
    }
}