<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/4
 * Time: 上午2:02
 */

require '../vendor/autoload.php';


ini_set('max_execution_time', 0);
set_time_limit(0);
//
//$file =   '../static/baidu/data/baidu_car.json';
//$myfile = fopen($file, "r") or die("Unable to open file!");
//$json = fread($myfile,filesize($file));
//fclose($myfile);



$db = new PDO('mysql:host=127.0.0.1;dbname=bibi',"root","123456");
$db->query('set names utf8');


$sql = "SELECT * FROM `bibi_car_brand_list` WHERE `baidu_brand_id` != 0 ORDER BY `abbre` ASC";
$rs  = $db->query($sql);

$curl = new \Curl\Curl();



while($row = $rs->fetch(PDO::FETCH_ASSOC)) {

    $brand_id = $row['baidu_brand_id'];
    $bid = $row['brand_id'];

    $url = 'http://car.baidu.com/indexAjax/filterCars?subqid=1446574013500217600&srcid=1400000&resourceid=1400000&qid=1446573870413941666&pvid=1446573870413941666&tn=self&zt=self&srcid_from=1400000&reqtype=0&t=igjothkf&sessionID=1446573870413941666&fr=self&sid=-&pid=362&city=93&pn=1&cat=ershouche&page=1&style=0&brand='.$brand_id.'&series=0&price=0&age=0&mileage=0&use=0&country=0&gearbox=0&displacement=0&total=1';

    $response = $curl->get($url);

    $response = json_encode($response);

    $response = json_decode($response, true);

    $data = $response['data']['filters'];

    $series_options = $data['series']['options'];

    foreach($series_options as $key => $option){

        $name = $option['name'];
        $value = $option['value'];

        $name = explode('(', $name);

        $name = $name[0];

        $sql = "SELECT * FROM `bibi_car_brand_series` WHERE `brand_series_name` LIKE '%{$name}%' AND `baidu_series_id` = 0 AND brand_id = {$bid}";

        $rs = $db->query($sql);

        while($row = $rs->fetch(PDO::FETCH_ASSOC)) {

            $series_id = $row['brand_series_id'];
            $sql = "UPDATE `bibi_car_brand_series` SET `baidu_series_id` = {$value} WHERE `brand_series_id` = '{$series_id}'";
            $db->exec($sql);

        }


    }
    sleep(30);

}