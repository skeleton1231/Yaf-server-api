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


$filters = $array['data']['filters'];

print_r($filters);