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

//$testUrl = 'http://www.baidu.com/zhixin.php?url=Kf0000aSkqTZzM8CTLZQYryuyxkXYztl5JAV8abVE5WXsNBVmI1UopFwIOoaAjIHiEkIy-QKjVBTo_S2vp2A0fW-k-oaeJM2ehxTzVZOTavluna1zLbQmP_GNPprNATqoRj73aD.DR_a6zlAG2cQtAB7hpaO5tb_YR1xlOqxsde_MWYdtxYrxxqEqjzYst8FBSe9JxY5BoyFBPPZy_nYQ7XlEIm0.THY0IZF9uANGujYdPj0znjT0mvq1I7qzmy4o5H00TLNBTy-b5HDYPj6znjmYPWDYnHcLrjTYPjT0ugw4TARqnHD0uy-b5H00uyw-TvPGujYs0AP_pyPogLw4TARqn6KsUWYk0Zw-ThdGUh7_5H00XMfqTvN_u6KsTjYznWnsPW6vnHc0mywGujYzrjbsPWfLrHRvPfKGTdqLpgF-UAN1T1Ys0AN3Ijd9mvP-TLPRXgK-rhdsr1VsIh-brWDYPj6znjR4P1TYPjR3PWTYn1berv-1N-PfrW0erLKzUvwdmLwFujCvPWm1nWbzPjDervPGIZblrHnervuzUvYlnHfsnj0snj_epvN4IvqzuD-brWc4rHD1rHfkPj01r1_0mLFW5Hf4P1fk&ss=7559174';
//
//$ss = explode('&ss=',$testUrl)[1];
//
//
//$response = file_get_contents($testUrl);
//
//$location = '';
//
//foreach($http_response_header as $hk => $hsh){
//
//    if(stristr($hsh, 'Location')){
//
//        $location = explode('Location: ',$hsh)[1];
//
//    }
//}
//
//print_r($location);exit;


$db = new PDO('mysql:host=127.0.0.1;dbname=bibi', "root", "123456");
$db->query('set names utf8');

$start =1;
$page = 295;

$sql = 'select `baidu_brand_id`  from `bibi_car_brand_list` where is_hot = 1 and `baidu_brand_id` != 0';

$rs = $db->prepare($sql);
$rs->execute();
$baidu_brand_ids = $rs->fetchAll(PDO::FETCH_ASSOC);

foreach($baidu_brand_ids as $k => $baidu_brand_id){

    $bd[] = $baidu_brand_id['baidu_brand_id'];
}


for ($i = 1; $i <= $page; $i++) {

    //$url = 'http://car.baidu.com/indexAjax/filterCars?subqid=1448617761087642400&srcid=1400000&resourceid=1400000&qid=1448269220984714269&pvid=1448269220984714269&tn=self&zt=self&srcid_from=1400000&reqtype=0&t=ihbqbb98&sessionID=1448269220984714269&fr=self&sid=-&pid=362&city=93&cat=ershouche&page='.$i.'&style=0&brand=0&series=0&price=0&age=0&mileage=0&use=0&country=0&gearbox=0&displacement=0&total='.$page.'';

    $url = 'http://car.baidu.com/indexAjax/filterCars?subqid=1448617761087642400&srcid=1400000&resourceid=1400000&qid=1448616871205703465&pvid=1448616871205703465&tn=self&zt=self&srcid_from=1400000&reqtype=0&t=ihhhmf64&sessionID=1448616871205703465&fr=self&sid=-&pid=362&city=93&pn=1&cat=ershouche&page='.$i.'&style=0&brand=0&series=0&price=0&age=0&mileage=0&use=0&country=0&gearbox=0&displacement=0&total='.$page.'';

    $data = json_decode(json_encode($curl->get($url)), true);

    $cars = $data['data']['cars'];

    if ($cars['list'])  {

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

            if(!in_array($baidu_brand_id , $bd)){

                continue;
            }

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

            $ss = explode('&ss=',$car['url'])[1];

            $location = '';

            $image = $car['image'];

            $thumbnail = $car['thumbnail'];

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

    sleep(1);


}







