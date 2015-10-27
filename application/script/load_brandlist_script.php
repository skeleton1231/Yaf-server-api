<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/23
 * Time: 上午9:46
 */

ini_set('max_execution_time', 0);
set_time_limit(0);

$file =   '../static/BrandList.json';
$myfile = fopen($file, "r") or die("Unable to open file!");
$json = fread($myfile,filesize($file));
$array = json_decode($json,true);

$brandList = $array['brandList'];
fclose($myfile);

print_r($brandList);