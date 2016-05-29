<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/5
 * Time: 上午12:36
 */
require '../vendor/autoload.php';


ini_set('max_execution_time', 0);
set_time_limit(0);

$curl = new \Curl\Curl();

$file_path = '/Users/huanghaitao/bibi-files';


$db = new PDO('mysql:host=127.0.0.1;dbname=bibi', "root", "itadmin");
$db->query('set names utf8');

$start =1;
$page = 200;

for ($i = 1; $i <= $page; $i++) {

    $url = 'http://car.baidu.com/indexAjax/filterCars?subqid=1448217545156069806&srcid=1400000&resourceid=1400000&qid=1448217545156069806&pvid=1448217545156069806&tn=self&zt=self&srcid_from=1400000&reqtype=0&t=ihavc72d&sessionID=1448217545156069806&fr=self&sid=-&pid=362&city=93&pn=1&cat=ershouche&page='.$i.'&style=0&brand=0&series=0&price=0&age=0&mileage=0&use=0&country=0&gearbox=0&displacement=0&total='.$page.'';
    $data = json_decode(json_encode($curl->get($url)), true);

    $cars = $data['data']['cars'];

    if (!$cars['list']) {

        continue;

    } else {

        $filters = json_encode($data['data']['filters']);


        $style = isset($data['data']['filters']['style']['options'][0]) ? $data['data']['filters']['style']['options'][0]['value'] : 0;
        //insert cars
        foreach ($cars['list'] as $c => $car) {

            $brand_id = 0;
            $brand_name = '';
            $series_id = 0;
            $series_name = '';

            $baidu_brand_id = $car['brand'];
            $baidu_series_id = $car['series'];

            //update brand_id series_id

            $sql = "SELECT `brand_id`, `brand_name`, `is_hot` FROM `bibi_car_brand_list` where `baidu_brand_id` = {$baidu_brand_id} ";
            $rs3 = $db->prepare($sql);
            $rs3->execute();
            $brand = $rs3->fetch(PDO::FETCH_ASSOC);

            if($brand['is_hot'] != 1){

                continue;
            }

            $sql = "SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name` FROM `bibi_car_brand_series` where `baidu_series_id` = {$baidu_series_id} ";

            $rs3 = $db->prepare($sql);
            $rs3->execute();
            $series = $rs3->fetch(PDO::FETCH_ASSOC);


            if ($brand && $series) {

                $brand_id = $brand['brand_id'];
                $brand_name = $brand['brand_name'];
                $series_id = $series['series_id'];
                $series_name = $series['series_name'];
            }

            $time = time();

            $ss = explode('&ss=',$car['url'])[1];

            $location = '';


            $image = $car['image'];

            $thumbnail = $car['thumbnail'];


            $location = '';
            $url = $car['url'];
            @file_get_contents($url);

            foreach ($http_response_header as $hk => $hsh) {

                if (stristr($hsh, 'Location')) {

                    $location = explode('Location: ', $hsh)[1];

                }
            }

            $id = preg_location_id($location);


            if($id){

                $hashId = $ss . $id;

                $sql = ' SELECT * FROM `bibi_car_selling_list` WHERE `hash` = '.$hashId.' ';

                $rs3 = $db->prepare($sql);
                $rs3->execute();
                $item = $rs3->fetch(PDO::FETCH_ASSOC);

                if(!$item){

                    $sql = "INSERT INTO `bibi_car_selling_list`
                    (
                      `hash`,
                      `brand_id`,
                      `brand_name`,
                      `series_id`,
                      `series_name`,
                      `baidu_brand_id`,
                      `baidu_series_id`,
                      `model_name`,
                      `platform_url`,
                      `car_name`,
                      `price`,
                      `guide_price`,
                      `board_time`,
                      `mileage`,
                      `displacement`,
                      `gearbox`,
                      `platform_name`,
                      `platform_id`,
                      `platform_location`,
                      `car_type`,
                      `city_name`,
                      `style`,
                      `created`,
                      `updated`,
                      `image`,
                      `thumbnail`
                    )
                    VALUES
                    (
                      {$hashId},
                      {$brand_id},
                      '{$brand_name}',
                      {$series_id},
                      '{$series_name}',
                      {$baidu_brand_id},
                      {$baidu_series_id},
                      '{$car['model']}',
                      '{$car['url']}',
                      '{$car['title']}',
                      {$car['price']},
                      {$car['guidePrice']},
                      {$car['boardTime']},
                      {$car['mileage']},
                      {$car['displacement']},
                      '{$car['gearbox']}',
                      '{$car['user']}',
                      {$ss},
                      '{$location}',
                      2,
                      '深圳',
                      {$style},
                      {$time},
                      {$time},
                      '{$image}',
                      '{$thumbnail}'
                      )
                    ";


                    $db->exec($sql);
                }



            }


        }


    }

    sleep(30);


}


function preg_location_id($location)
{

    $patterns = "/\d+/";
    preg_match_all($patterns, $location, $arr);

    $number = 0;
    if(isset($arr[0])){

        $number = max($arr[0]);

    }

    return $number;
}








