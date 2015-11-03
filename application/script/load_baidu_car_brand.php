<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/30
 * Time: 上午1:08
 */

ini_set('max_execution_time', 0);
set_time_limit(0);

$file =   '../static/baidu_car.json';
$myfile = fopen($file, "r") or die("Unable to open file!");
$json = fread($myfile,filesize($file));
fclose($myfile);


$array = json_decode($json,true);


$brands = $array['data']['filters']['brand']['all'];

foreach($brands as $key => $brand){

    $abbre = $key;

    foreach($brand as $k => $item) {

        $sql = "SELECT * FROM `bibi_car_brand_list` WHERE `abbre` = '{$abbre}' AND name LIKE ";



    }

}