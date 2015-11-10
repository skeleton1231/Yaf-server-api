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

$db = new PDO('mysql:host=127.0.0.1;dbname=bibi',"root","123456");
$db->query('set names utf8');

$sql = "SELECT `baidu_brand_id`, `brand_id` FROM `bibi_car_brand_list` WHERE `baidu_brand_id` != 0";

$rs = $db->query($sql);

$rs = $db->prepare($sql);
$rs->execute();
$rows = $rs->fetchAll(PDO::FETCH_ASSOC);

$curl = new \Curl\Curl();

foreach($rows as $r1 => $row){

    $baidu_brand_id = $row['baidu_brand_id'];
    $brand_id       = $row['brand_id'];
    $sql = "SELECT DISTINCT `baidu_series_id` FROM `bibi_car_brand_series` WHERE `brand_id` = {$brand_id}";
    $rs2 = $db->prepare($sql);
    $rs2->execute();
    $results = $rs2->fetchAll(PDO::FETCH_ASSOC);

    foreach($results as $r2 => $row2){

        $baidu_series_id = $row2['baidu_series_id'];

        $url = "http://car.baidu.com/indexAjax/filterCars?subqid=1446652908669472000&srcid=1400000&resourceid=1400000&qid=1446652887821347159&pvid=1446652887821347159&tn=self&zt=self&srcid_from=1400000&reqtype=0&t=igl0zayo&sessionID=1446652887821347159&fr=self&sid=-&pid=362&city=93&pn=1&cat=ershouche&page=1&style=0&brand={$baidu_brand_id}&series={$baidu_series_id}&price=0&age=0&mileage=0&use=0&country=0&gearbox=0&displacement=0";

        $data = json_decode(json_encode($curl->get($url)),true);

        $cars = $data['data']['cars'];

        $filters = json_encode($data['data']['filters']);

        if($baidu_series_id == 0){

            $sql = "UDPATE `bibi_car_brand_list` SET `filters` = '{$filters}' WHERE `brand_id` = {$brand_id}";
            $db->exec($sql);

        }
        else{

            $sql = "UPDATE `bibi_car_brand_series` SET `filters` = '{$filters}' WHERE `baidu_series_id` = {$baidu_series_id}";

            $db->exec($sql);

            $style = $data['data']['filters']['style']['options'][0]['value'];
            //insert cars
            foreach($cars['list'] as $c => $car){

                $sql = "INSERT INTO `bibi_car_list`
                    (
                      `baidu_brand_id`,
                      `baidu_series_id`,
                      `model_name`,
                      `platform_url`,
                      `image`,
                      `thumbnail`,
                      `title`,
                      `price`,
                      `guide_price`,
                      `board_time`,
                      `mileage`,
                      `displacement`,
                      `gearbox`,
                      `platform_name`,
                      `car_type`,
                      `city_name`,
                    `style`
                    )
                    VALUES
                    (
                      {$baidu_brand_id},
                      {$baidu_series_id},
                      '{$car['model']}',
                      '{$car['url']}',
                      '{$car['image']}',
                      '{$car['thumbnail']}',
                      '{$car['title']}',
                      {$car['price']},
                      {$car['guidePrice']},
                      {$car['boardTime']},
                      {$car['mileage']},
                      {$car['displacement']},
                      '{$car['gearbox']}',
                      '{$car['user']}',
                      2,
                      '深圳',
                      {$style}
                      )
                    ";

                $db->exec($sql);



            }


        }


    }


    sleep(10);

}




