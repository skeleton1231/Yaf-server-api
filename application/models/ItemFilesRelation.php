<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午4:07
 */

class ItemFilesRelationModel extends PdoDb{

    static public  $table = 'bibi_item_files_relationship';

    public function __contsruct(){

        parent::__construct();

    }

    public function Create($item_id, $file_id, $type){

        $data = array();
        $data['item_id'] = $item_id;
        $data['file_id'] = $file_id;
        $data['type']    = $type;

        $id = $this->insert(self::$table , $data);

        return $id;

    }

    public function CreateBatch($item_id, $files_id, $type){

        $files_id = explode(',', $files_id);

        $items = array();

        foreach($files_id as $k => $file_id){
            $data = array();
            $data['item_id'] = $item_id;
            $data['file_id'] = $file_id;
            $data['type']    = $type;
            $items[] = $data;
        }

        $this->insertBatch(self::$table, $items);
    }

}