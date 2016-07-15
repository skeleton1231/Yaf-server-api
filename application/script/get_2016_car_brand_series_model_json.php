<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/7/6
 * Time: 15:11
 */

require '../vendor/autoload.php';


ini_set('max_execution_time', 0);
set_time_limit(0);

$url = 'http://www.xin.com/evaluate/get_series/';

$db = new PDO('mysql:host=127.0.0.1;dbname=bibi', "root", "itadmin");
$db->query('set names utf8');

$sql = 'SELECT `brand_id` FROM `bibi_car_brand_list` WHERE `is_hot` = 1 ORDER BY `abbre` ASC';

$rs = $db->prepare($sql);
$rs->execute();
$list = $rs->fetchAll();

foreach ($list as $k => $li) {

    $brand_id = $li['brand_id'];

    $data['type'] = 'b';
    $data['id'] = $brand_id;
    $data['is_pcar'] = 1;


    $curl = new \Curl\Curl();

    $str = $curl->post($url, $data);

    $html = \Sunra\PhpSimple\HtmlDomParser::str_get_html($str);

    $BrandTips = $html->find('.BrandTip');

    $CarMultis = $html->find('.CarMulti');

    foreach ($CarMultis as $i => $carMulti) {

        $makename = $BrandTips[$i]->innertext;

        $series = $carMulti->find('a.serie_bts');

        foreach ($series as $j => $serie) {

            $key = 'data-s';

            $brand_sereis_id = str_replace('s', '', $serie->$key);

            $brand_series_name = $serie->find('em', 0)->innertext;

            $sql1 = "insert into `bibi_car_brand_series` 
            (brand_id, brand_series_id,brand_series_name,makename) 
            values($brand_id, $brand_sereis_id,'{$brand_series_name}','{$makename}')";

            $db->exec($sql1);


            $postModel = array();
            $postModel['type'] = 's';
            $postModel['id'] = $brand_sereis_id;
            $postModel['is_pcar'] = 1;

            $postStr = $curl->post($url, $postModel);

            $models = \Sunra\PhpSimple\HtmlDomParser::str_get_html($postStr);

            $models = $models->find('a.filter_item');

            foreach ($models as $j => $model) {


                $model_id = str_replace('m', '', $model->$key);

                $model_name = $model->find('em', 0)->innertext;

                $sql2 = "insert into `bibi_car_series_model` 
                          (series_id, model_id,model_name) 
                          values($brand_sereis_id, $model_id,'{$model_name}')";

                $db->exec($sql2);
            }


//            echo $sql1;
//            echo "\n";
//            echo $sql2;
//            exit;


            //$series[] = $serie;
            sleep(1);

        }
    }


}





