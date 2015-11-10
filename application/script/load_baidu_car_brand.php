<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/30
 * Time: 上午1:08
 */

ini_set('max_execution_time', 0);
set_time_limit(0);

$file =   '../static/baidu/baidu_car.json';
$myfile = fopen($file, "r") or die("Unable to open file!");
$json = fread($myfile,filesize($file));
fclose($myfile);


$array = json_decode($json,true);


$brands = $array['data']['filters']['brand']['all'];


$db = new PDO('mysql:host=127.0.0.1;dbname=bibi',"root","123456");
$db->query('set names utf8');

foreach($brands as $key => $brand){

    $abbre = $key;

    foreach($brand as $k => $item) {

        //$sql = "SELECT * FROM `bibi_car_brand_list` WHERE `abbre` = '{$abbre}' AND name LIKE ";
        $value = $item['value'];
        $name  = trim($item['name']);

        $sql = "UPDATE `bibi_car_brand_list` SET `baidu_brand_id` = {$value} WHERE `brand_name` = '{$name}'";
        //$db->exec($sql);

        echo $sql;
        echo "\n";
    }

}