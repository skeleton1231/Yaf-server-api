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


$db = new PDO('mysql:host=127.0.0.1;dbname=bibi',"root","123456");
$db->query('set names utf8');

$sql = "SELECT * FROM `bibi_car_brand_list`";

$rs = $db->query($sql);

$curl = new \Curl\Curl();

$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
$curl->setHeader('Cookie','sto-id-20480=IJGEBAKMFAAA; uid=rBBkiVY4LoNQZB2OGq+aAg==');





while($row = $rs->fetch(PDO::FETCH_ASSOC)) {

    $brandId = $row['brand_id'];


    $url = 'http://api.xin.com/serie/view/';

    $post = array(
        'appver'=>4.5,
        'authtoken'=>'aYTVYBdB832u3OuVQwjRttF+k2ckqNYg/A7OpQdOaTxLxilU8SnBTSmpsfFvTs3JONU4ZNL+zZsyuGKTdKrW3HOWAla6gQhJsoARifnnXKNKkg+o0Y11gxGO1NPdQA4Tp+wwKx1sgJmmbs58uCE1OCCAs87eg1mMyZl3uAmZxawgYoMjEv5jM4wWlv+Ae/VfXe61WswycmA0fpaIuome6A==',
        'brandid'=>$brandId,
        'cityid'=>502,
        'nb'=>'d1a08b64e54940ca90e05f0348dc1a96',
        'os'=>'iphone',
        'showcounter'=>0,
        'showmodel'=>1,
        '_apikey'=>'87427e'
    );


    $response = $curl->post($url, $post);
    $raw = json_encode($response);

    $data = json_decode($raw, true);

    $data = $data['data'];

    $data_json = json_encode($data);

    $path =   "../static/xin/data/brand_series_model_{$brandId}.json";
    $myfile = fopen($path, "w+") or die("Unable to open file!");

    fwrite($myfile, $data_json);
    fclose($myfile);

    $list = $data['serie'];

    if(!$list){
        continue;
    }

    foreach($list as $key => $li) {

        $makename = $li['makename'];

        $serieslist = $li['serielist'];

        foreach($serieslist as $k => $seire){

            $brand_series_name = $seire['seriename'];
            $brand_series_id = $seire['serieid'];

            //$brand_series_url = $li['typeLogo'];
            $sql = "INSERT INTO `bibi_car_brand_series` (`brand_id`,`brand_series_name`,`brand_series_id`,`makename`)
                VALUES
                ({$brandId},'{$brand_series_name}',{$brand_series_id}, '{$makename}')";

            $db->exec($sql);

            $model = $seire['model'];

            foreach($model as $i => $md){

                $modelyear = $md['year'];

                $modellist = $md['modellist'];

                foreach($modellist as $n => $modelitem){

                    $modleid = $modelitem['modelid'];
                    $modelname = $modelitem['modelname'];

                    $sql = "INSERT INTO `bibi_car_series_model` (`series_id`, `model_id`,`model_name`,`model_year`)
                                  VALUES
                                ({$brand_series_id}, {$modleid}, '{$modelname}', '{$modelyear}')";

                    $db->exec($sql);

                }
            }


        }
        
    }


    sleep(10);

}



