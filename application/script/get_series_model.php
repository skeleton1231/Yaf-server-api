<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/31
 * Time: 下午4:46
 */
require '../vendor/autoload.php';


ini_set('max_execution_time', 0);
set_time_limit(0);

$file =   '../static/hcb/che168_series_kv.json';
$myfile = fopen($file, "r") or die("Unable to open file!");
$json = fread($myfile,filesize($file));
$seriesList = json_decode($json,true);
fclose($myfile);

//print_r($seriesList);

$data = array();
$data['head']['app_os'] = "IOS";
$data['head']['app_version'] = "3.5";
$data['head']['uid'] = "27750";
$data['head']['longitude'] = "114.209784";
$data['head']['time'] = Date('y-m-d h:i:s');
$data['head']['latitude'] = "22.711221";
$data['head']['token'] = "f8ea8edd4209cd75fc11c2a4b2f37e4";
$data['head']['cid']   = "233ada2f4e71cbb5df0d249a73e01117";

$url = "http://115.29.208.39/api_huanche_ios/index.php/menu/get_model_info";

$i = 0;
foreach($seriesList as $key => $series){

    if($key < 3067){

        continue;
    }


    if($i == 400){

        exit;
    }

    $id = $key;

    $data['body']['series_id'] = "$id";
    $djson =  json_encode($data);


    $text = "--Boundary+2B6926E72C30B756
Content-Disposition: form-data; name='json_package'

{$djson}
--Boundary+2B6926E72C30B756--";

    $path =   "../static/hcb/BrandSeriesModel/series_id_{$id}.json";
    $myfile = fopen($path, "w+") or die("Unable to open file!");

    $curl = new \Curl\Curl();
    $curl->setHeader('Content-Type', 'multipart/form-data; boundary=Boundary+2B6926E72C30B756');
    $response = $curl->post($url, $text);
    $raw = json_encode($response);

    fwrite($myfile, $raw);
    fclose($myfile);

    if($raw == false)
        die();

    sleep(1);

    $i++;


}

