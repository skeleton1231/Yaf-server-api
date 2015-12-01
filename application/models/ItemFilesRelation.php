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

    public function CreateBatch($item_id, $files_id, $type, $items_type=''){

        $files_id = json_decode($files_id, true);
        $items_type = json_decode($items_type, true);

        $items = array();

        foreach($files_id as $k => $file_id){
            $data = array();
            $data['item_id']   = $item_id;
            $data['file_id']   = $file_id;
            $data['type']      = $type;
            $data['item_type'] = isset($items_type[$k]) ? $items_type[$k] : 0;
            $items[] = $data;
        }

        $this->insertBatch(self::$table, $items);
    }

}