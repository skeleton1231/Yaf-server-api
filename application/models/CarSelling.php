<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class CarSellingModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_selling_list';
    }

    public function GetCarInfoById($hash)
    {

        //$key = 'car_id_' . $carId . '';

//        $sql = '
//                SELECT
//                t1.*,
//                t3.hash AS file_id,
//                t3.url AS file_url
//                FROM `' . self::$table . '` AS t1
//                LEFT JOIN `' . ItemFilesRelationModel::$table . '` AS t2
//                ON t1.id = t2.item_id
//                LEFT JOIN `' . FileModel::$table . '` AS t3
//                ON t2.file_id = t3.hash
//                WHERE t2.type = ' . ITEM_TYPE_CAR . ' AND t2.item_id = ' . $carId . '
//                ';

        $sql = '
            SELECT
            *
            FROM `' . self::$table . '`
            WHERE `hash` = "'.$hash.'"
        ';

        $items = $this->query($sql);

        if (isset($items[0])) {

            $car = $items[0];
        } else {

            return array();
        }

        $carInfo = array();
        $carInfo['brand_id'] = $car['brand_id'];
        $carInfo['series_id'] = $car['series_id'];
        $carInfo['model_id'] = $car['model_id'];

        $brand = new BrandModel($carInfo);
        $carName = $brand->getBrandInfo();

        if($carName){

            $car['car_name']    = $carName['car_name'];
            $car['brand_name']  = $carName['brand_name'];
            $car['series_name'] = $carName['series_name'];
            $car['model_name']  = $carName['model_name'];
        }



        unset($car['baidu_brand_id']);
        unset($car['baidu_series_id']);

        $images = unserialize($car['files']);
        $items = array();

        foreach ($images as $k => $image) {

            if ($image['hash']) {

                $item = array();
                $item['file_id']   = $image['hash'];
                $item['file_url']  = IMAGE_DOMAIN . $image['key'];
                $item['file_type'] = $image['type'];
                $items[] = $item;

            }

        }

        $car['files'] = $items;

        return $car;
        //return isset($car[0]) ? $car[0] : null;

    }

    public function dealFilesWithString($files_id, $files_type){

        $filesInfo = array();

        $files   = explode(',' , $files_id);

        $files_type = explode(',' , $files_type);

        foreach($files as $k => $fileHash){

            $filesInfo[] = array('hash'=>$fileHash,'type'=> $files_type[$k],'key'=>$fileHash);

        }

        return $filesInfo;

    }

}