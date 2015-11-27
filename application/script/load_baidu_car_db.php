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

$db = new PDO('mysql:host=127.0.0.1;dbname=bibi', "root", "itadmin");
$db->query('set names utf8');

$sql = "SELECT `baidu_brand_id`, `brand_id` FROM `bibi_car_brand_list` WHERE `baidu_brand_id` != 0";

$rs = $db->query($sql);

$rs = $db->prepare($sql);
$rs->execute();
$rows = $rs->fetchAll(PDO::FETCH_ASSOC);

$curl = new \Curl\Curl();

foreach ($rows as $r1 => $row) {

    $baidu_brand_id = $row['baidu_brand_id'];
    $brand_id = $row['brand_id'];
    $sql = "SELECT DISTINCT `baidu_series_id` FROM `bibi_car_brand_series` WHERE `brand_id` = {$brand_id}";

    $rs2 = $db->prepare($sql);
    $rs2->execute();
    $results = $rs2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $r2 => $row2) {

        $baidu_series_id = $row2['baidu_series_id'];

        $page = 285;

        for ($i = 1; $i < $page; $i++) {

            $url = "http://car.baidu.com/indexAjax/filterCars?subqid=1446652908669472000&srcid=1400000&resourceid=1400000&qid=1446652887821347159&pvid=1446652887821347159&tn=self&zt=self&srcid_from=1400000&reqtype=0&t=igl0zayo&sessionID=1446652887821347159&fr=self&sid=-&pid=362&city=93&pn=1&cat=ershouche&page={$i}&style=0&brand={$baidu_brand_id}&series={$baidu_series_id}&price=0&age=0&mileage=0&use=0&country=0&gearbox=0&displacement=0";

            $data = json_decode(json_encode($curl->get($url)), true);

            $cars = $data['data']['cars'];

            if (!$cars['list']) {

                continue;

            } else {

                $filters = json_encode($data['data']['filters']);


                $sql = "UPDATE `bibi_car_brand_series` SET `filters` = '{$filters}' WHERE `baidu_series_id` = {$baidu_series_id}";

                $db->exec($sql);

                $style = $data['data']['filters']['style']['options'][0]['value'];
                //insert cars
                foreach ($cars['list'] as $c => $car) {

                    $brand_id = 0;
                    $brand_name = '';
                    $series_id = 0;
                    $series_name = '';

                    //update brand_id series_id

                    $sql = "SELECT `brand_id`, `brand_name` FROM `bibi_car_brand_list` where `baidu_brand_id` = {$baidu_brand_id} ";
                    $rs3 = $db->prepare($sql);
                    $rs3->execute();
                    $brand = $rs3->fetch(PDO::FETCH_ASSOC);

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

                    $sql = "INSERT INTO `bibi_car_selling_list`
                    (
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
                      `car_type`,
                      `city_name`,
                      `style`,
                      `created`,
                      `updated`
                    )
                    VALUES
                    (
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
                      2,
                      '深圳',
                      {$style},
                      {$time},
                      {$time}
                      )
                    ";

                    $db->exec($sql);


                }


            }


        }


    }


    sleep(10);

}




