<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/23
 * Time: 上午9:46
 */

ini_set('max_execution_time', 0);
set_time_limit(0);

$file =   '../static/baidu_car.json';
$myfile = fopen($file, "r") or die("Unable to open file!");
$json = fread($myfile,filesize($file));
fclose($myfile);


$array = json_decode($json,true);


$keys = array('style','price','use','age','mileage','displacement','gearbox','country');


$filter = $array['data']['filters'];


$db = new PDO('mysql:host=120.25.62.110;dbname=bibi',"root","bibi2015");
$db->query('set names utf8');

foreach($keys as $k => $key){

    $type = $k+1;
    $items = $filter[$key]['options'];
    foreach($items as $i => $item){

       $value = $item['value'];
       $name  = $item['name'];

        $sql = "INSERT INTO `bibi_car_options`
                (`name`,`value`,`type`)
                VALUES
                ('{$name}',$value,$type)
                ";

        $db->query($sql);

    }
}

//$styles         = $filter['style']['options'];
//$price          = $filter['price']['options'];
//$use            = $filter['use']['options'];
//$age            = $filter['age']['options'];
//$mileage        = $filter['mileage']['options'];
//$displacement   = $filter['displacement']['options'];
//$gearbox        = $filter['gearbox']['options'];
//$country        = $filter['country']['options'];





