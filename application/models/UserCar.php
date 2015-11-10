<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午1:03
 */

class UserCarModel extends PdoDb{

    static private $table = 'bibi_user';

    public function __contsruct(){

        parent::__construct();

    }

    public function Create($data){

        $id = $this->insert(self::$table , $data);

        return $id;
    }


}