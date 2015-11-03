<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/29
 * Time: 下午10:22
 */

$db = new PDO('mysql:host=120.25.62.110;dbname=bibi',"root","bibi2015");
$db->query('set names utf8');

$sql = "SELECT * FROM `bibi_car_brand_list`";

$rs = $db->query($sql);
while($row = $rs->fetch(PDO::FETCH_ASSOC)){
    //print_r($row);

    $brandId = $row['brand_id'];
    //$brandName = $row['brandName'];
    $file =   "../static/series_{$brandId}.json";

//    $myfile = fopen($path, "w+") or die("Unable to open file!");
//    $json = fread($myfile,filesize($file));
//    fclose($myfile);

    $json = file_get_contents($file);


    $data = json_decode($json, true);

    $list = $data['carTypeList'];

    foreach($list as $key => $li) {
        $brand_series_name = $li['typeName'];
        $brand_series_id = $li['typeId'];
        $brand_series_url = $li['typeLogo'];
        $sql = "INSERT INTO `bibi_car_brand_series` (`brand_id`,`brand_series_name`,`brand_series_id`,`brand_series_url`)
                VALUES
                ({$brandId},'{$brand_series_name}',{$brand_series_id}, '{$brand_series_url}')";

        $db->exec($sql);

    }

}