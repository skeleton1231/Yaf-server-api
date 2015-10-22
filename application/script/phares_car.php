<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/22
 * Time: 下午4:49
 * phares car json
 */


require '../vendor/autoload.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$file =   '../static/BrandList.json';
$myfile = fopen($file, "r") or die("Unable to open file!");
$json = fread($myfile,filesize($file));
$array = json_decode($json,true);

$brandList = $array['brandList'];
fclose($myfile);



foreach($brandList as $key => $brand){


    $brandId = $brand['brandId'];
    $path = $file =   "../static/series_{$brandId}.json";
    $myfile = fopen($path, "w+") or die("Unable to open file!");

    $json = '{"systemVersion" : "2.3","channel" : "01","sessionId" : "amQJFKoF3KyXHdRLZ4QH1445271605524","inputParamJson":{"brandId":"'.$brandId.'","userId":"285866"}}';

    $url = 'http://182.92.97.142:8881/car/queryCarType';

    $curl = new \Curl\Curl();
    $curl->setHeader('Content-Type', 'application/octet-stream');
    $response = $curl->post($url, $json);
    $raw = json_encode($response);

    fwrite($myfile, $raw);
    fclose($myfile);

    sleep(5);


}


