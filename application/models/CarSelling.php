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
    public $brand_info;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_selling_list';
    }

    public function GetCarInfoById($hash)
    {


        $sql = '
            SELECT
            t1.*,
            t3.avatar,t3.nickname
            FROM `' . self::$table . '`
            AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            WHERE t1.hash = "' . $hash . '"
        ';

        $items = $this->query($sql);

        if (isset($items[0])) {

            $car = $items[0];
        } else {

            return array();
        }

        $car = $this->handlerCar($car);

        return $car;

    }

    public function handlerCar($car){

        $brandM = new BrandModel();

        $car['brand_info']  = $brandM->getBrandModel($car['brand_id']);
        $car['series_info'] = $brandM->getSeriesModel($car['brand_id'],$car['series_id']);
        $car['model_info']  = $brandM->getModelModel($car['series_id'], $car['model_id']);

        unset($car['brand_id']);
        unset($car['series_id']);
        unset($car['model_id']);
        unset($car['brand_name']);
        unset($car['series_name']);
        unset($car['model_name']);
        unset($car['baidu_brand_id']);
        unset($car['baidu_series_id']);
        unset($car['image']);
        unset($car['thumbnail']);

        if($car['user_id']){

            $car['user_info'] = array();
            $car['user_info']['user_id']  = $car['user_id'];
            unset($car['user_id']);
            $car['user_info']['username'] = '';
            $car['user_info']['mobile']   = '';
            $car['user_info']['created']  = 0;
            $car['user_info']['profile']['avatar']  = $car['avatar'];
            unset($car['avatar']);
            $car['user_info']['profile']['nickname']  = $car['nickname'];
            unset($car['nickname']);
            $car['user_info']['profile']['signature']  = '';
            $car['user_info']['profile']['age']  = 0;
            $car['user_info']['profile']['constellation']  = '';
            $car['user_info']['profile']['gender']  = 0;
        }
        else{

            $car['user_info'] = new stdClass();
        }


        $images = unserialize($car['files']);
        $items = array();

        foreach ($images as $k => $image) {

            if ($image['hash']) {

                $item = array();
                $item['file_id'] = $image['hash'];
                $item['file_url'] = IMAGE_DOMAIN . $image['key'];
                $item['file_type'] = $image['type'];
                $items[] = $item;

            }

        }


        unset($car['id']);
        $car['car_id'] = $car['hash'];
        unset($car['hash']);

        $car['city_info'] = array(
            'city_id' => $car['city_id'],
            'city_name' => $car['city_name'],
            'city_lng' => 360,
            'city_lat' => 360,
        );

        if ($car['platform_id']) {

            $car['platform_info'] = array('platform_id' => $car['platform_id'], 'platform_location' => $car['platform_location'], 'platform_name' => $car['platform_name']);
        } else {

            $car['platform_info'] = new stdClass();
        }

        $car['files'] = $items;

        unset($car['city_id']);
        unset($car['city_name']);
        unset($car['user_id']);
        unset($car['platform_id']);
        unset($car['platform_location']);
        unset($car['platform_name']);
        unset($car['platform_url']);
        unset($car['avatar']);
        unset($car['nickname']);


        $car['is_fav'] = 1;
        $car['car_time'] = '今天';
        $car['visit_num'] = 100;

        return $car;

    }


    public function dealFilesWithString($files_id, $files_type)
    {

        $filesInfo = array();

//        $files = explode(',', $files_id);
//
//        $files_type = explode(',', $files_type);

        $files = json_decode($files_id, true);
        $files_type = json_decode($files_type, true);

        foreach ($files as $k => $fileHash) {

            $filesInfo[] = array('hash' => $fileHash, 'type' => $files_type[$k], 'key' => $fileHash);

        }

        return $filesInfo;

    }


    public function getCarList()
    {

        $pageSize = 10;

        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                ';


        $sql .= $this->where;
        $sql .= $this->order;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $cars = $this->query($sql);

        $carM = new CarSellingModel();

        $items = array();

        foreach($cars as $k => $car){

            $item = array();
            $item = $carM->handlerCar($car);
            $items[$k]['car_info'] = $item;
            $items[$k]['car_users'] = $this->getSameBrandUsers();

        }

        return $items;
    }

    public function getTotal()
    {

        $sql = '
            SELECT
            COUNT(*) AS total
            FROM `' . self::$table . '`';

        $total = $this->query($sql)[0];

        return $total;

    }

    public function getSameBrandUsers(){

        $userInfos = unserialize(RedisDb::getValue('test_car_users'));

        return $userInfos ? $userInfos : array();

    }

}