<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午1:03
 */

class UserCarModel extends PdoDb{

    static public $table = 'bibi_user_owner_cars';

    public function __contsruct(){

        parent::__construct();

    }

    public function Create($data){

        $id = $this->insert(self::$table , $data);

        return $id;
    }

    public function GetCarInfoByUserId($userId){

        $sql = '
                SELECT
                t1.*,
                t3.id AS file_id,
                t3.url AS file_url
                FROM `'.self::$table.'` AS t1
                LEFT JOIN `'.ItemFilesRelationModel::$table.'` AS t2
                ON t1.id = t2.item_id
                LEFT JOIN `'.FileModel::$table.'` AS t3
                ON t2.file_id = t3.id
                WHERE t1.`user_id` = '.$userId.' AND t2.type = '.ITEM_TYPE_USER_CAR.'
                ';

        $items = $this->query($sql);

        $car = array();

        $car['brand_id']    = $items[0]['brand_id'];
        $car['series_id']   = $items[0]['series_id'];
        $car['model_id']    = $items[0]['model_id'];
        $car['car_id']      = $items[0]['id'];
        $brand = new BrandModel($car);
        $carName = $brand->getCarInfo();
        $car['car_name']    = $carName;
        $car['images']      = array();
        $car['created']     = $items[0]['created'];
        $car['updated']     = $items[0]['updated'];
        $car['user_id']     = $items[0]['user_id'];
        $car['car_no']      = $items[0]['car_no'];
        $car['is_validate'] = $items[0]['is_validate'];

        foreach($items as $k => $item){

            $image = array();
            $image['file_id']  = $item['file_id'];
            $image['file_url'] = $item['file_url'];
            $car['images'][] = $image;

        }

        return $car;
        //return isset($car[0]) ? $car[0] : null;

    }




}