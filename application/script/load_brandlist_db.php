<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/29
 * Time: 下午8:00
 */



$file =   '../static/xin/brand.json';
$myfile = fopen($file, "r") or die("Unable to open file!");
$json = fread($myfile,filesize($file));
$array = json_decode($json,true);
fclose($myfile);

//print_r($array['data']);exit;

$brandList = $array['data'];

unset($brandList['★']);


$obj = new PDO('mysql:host=127.0.0.1;dbname=bibi',"root","123456");
$obj->query('set names utf8');


foreach($brandList as $key => $items){

    foreach($items as $alpha => $item){

        //print_r($item);exit;
        $brandName = $item['brandname'];
        $brandId   = $item['brandid'];
        $brandLogo = $item['brandimg'];
        $abbre     = $item['brandename'];

        $sql = "
            INSERT INTO `bibi_car_brand_list`
            (`brand_name`,`brand_id`,`brand_url`,`abbre`)
            VALUES
            ('{$brandName}',{$brandId},'{$brandLogo}', '{$abbre}')
           ";


        $stmt = $obj->exec($sql);
    }


}

$obj = null;





