<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/23
 * Time: 下午6:10
 */
require '../vendor/autoload.php';
require '../configs/Constants.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$curl = new \Curl\Curl();
$location = '';
//bj-iautos && 第一车网
//$url = 'http://www.baidu.com/zhixin.php?url=K60000j0vmc7JWHX8pa3212-2dY-60ac1XtJgdiYMsoDys1gHTPL62uAE-QNNEGT9AYC9U1a71Aw_teINqpzEfoW4koskvtjMN8Gw_qrXI6MBt87-gKPnvGAMZbOIgVh_c_pmPC.7D_iuc3xFera6l5b6swG89uh6eP-hOGvNtdBmWHwdojPak8_3els.THY0IZF9uANGujYdPj0znjT0mvq1I7qzmy4o5H00TLNBTy-b5HDYPj6zP10vnWmLP1nYPHfLnWc0ugw4TARqnHD0uy-b5H00uyw-TvPGujYs0AP_pyPogLw4TARqn6KsUWYk0Zw-ThdGUh7_5H00XMfqTvN_u6KsTjY0mywGujYznWfsrHDdnWnknsKGTdqLpgF-UAN1T1Ys0AN3Ijd9mvP-TLPRXgK-rhdsr1VsIh-brWDYPj6zPWbznW04rjfLnHfzPWberv-1N-PfrW0erLKzUvwdmLwFujCdrHDvnjbzrHbervPGIZblrHnervuzUvYlnHfsnj0snj_epvN4IvqzuD-brWcznWRzPjnkPWm4r1_0mLFW5Hn1nHRs&ss=443923';

//人人车
#$url = 'http://www.baidu.com/zhixin.php?url=K60000jeRS6sAFbVzGGJldA9M1qYluWziC5gfdYVjc1qjJ7gN4_-_0zFsN2r9rb1FuIEsSw24z1M7FlxhWguHBDGp2Ju5tM4W6oqsOHzEY9TtS0RghTkPIEFQ-iLIS4zJb3BjPb.7R_aunRzmCpbC69uh6LevpeeAUqSH_3qPIY5xgnksd5lZkQDkAhWoyAp7WFEzIPm.THY0IZF9uANGujYdPj0znjT0mvq1I7qzmy4o5H00TLNBTy-b5HDYPj6zP10vn1bYP1RkP101PjR0ugw4TARqnHD0uy-b5H00uyw-TvPGujYs0AP_pyPogLw4TARqn6KsUWY40Zw-ThdGUh7_5H00XMfqTvN_u6KsTjY0mywGujYkPWDzP1bdn1n1PfKGTdqLpgF-UAN1T1Ys0AN3Ijd9mvP-TLPRXgK-rhdsr1VsIh-brWDYPj6zPWbznW04rjfLnHfzPWberv-1N-PfrW0erLKzUvwdmLwFujCdnjmzP1TkPj0ervPGIZblrHnervuzUvYlnHfsnj0snj_epvN4IvqzuD-brWDdnWfzP1bdPW0dr1_0mLFW5HRvn1cY&ss=7659526';

//优信二手车
#$url = 'http://car.baidu.com/rcv?module=car&city=93&subqid=1448270668805585708&wd=&key=&srcid=1400000&resourceid=1400000&qid=1448269220984714269&pvid=1448269220984714269&pssid=&tn=self&zt=self&srcid_from=1400000&reqtype=0&prod=0103c2d00810bb0b7dbd53681d9aae30fae367ce939d09bbce4bacfd8e118a73c38765c3dfc0f07e7e7943fd0f275ff5591d4dcbc978dd1aa02d0b66e1c189e33f8d5ef890d9676f66ac743a11bf4584a5ba064c72a2b75976078c2c4b406152cd49e1a8992a681d15380a339e9f7871d5858f7db4c989cee2b1dcdabb86e0fa2c2caacafcde477e6069c83f7ce704d906153b2ebe39ee9643688225d509e72509de9190178773fc6538c9dbb9f7b7bf87faaf04ba6f1d1e0c056a7e09e198b2ba830ecc7f096f746d63b37a38fb7665e6feff535ddf38ee03b33e972643732407052a9d4d60f32c393c03871254fb6341d4d53d3af824fddd50a1b47f7838a0ed3b5b8f7fc979d74eb4bc64fdf8ac22a757fdd064743375f6f6e5b52a71742f99798f5b60baf53236eda81051536a641e42c8a60bfa7ff4d25069a309dd306cd369c5390b3e0dc18f1af27f6b484a6f5e2ff074c4adda0155a9d68c99393&u=0103c2d008118e6a2db9f70ca98f8b718e67330b229da84fab1a9c4cae910a63f2f2a1f67b21c43f6e09e76cbe673b41bda&requestID=1448270668805585708&page=28&userIP=3682951865&ss=10079477';

//51汽车
#$url = 'http://car.baidu.com/rcv?module=car&city=93&subqid=1448270677852644969&wd=&key=&srcid=1400000&resourceid=1400000&qid=1448269220984714269&pvid=1448269220984714269&pssid=&tn=self&zt=self&srcid_from=1400000&reqtype=0&prod=0103c2d00810bb0b7dbd53681d9aae30fae367ce939d09bbce4bacfd8e618a03333704926fc404de3fb8a3bcca02aef43818c8ca98189dcab498ea160191e9e36e4c0b3c71c947efc219943a909f3514f57ab60d23f2b2cdb7c62c2d4ff581527c4981e8c95a18edd5192bf39b2bc9e125957fc90129894f9211ecfa8b46f0ea5c5dcb6af85a360e01c9bc8a9ce785a9a6251b1e7e29fee63209222010081644a9ae8331113293fca428b97b79f7c79f469b3f010ebf4dafbc01df9f2880bcc6cba22fac2fbadf72d883b3fb78db16a596be1f739d2e094e07362fd757d2e3e0f2f52a1c6d50a2ec78fdc3b6f3c4daa20192952a6b8852bdda31a0f47abcfd85ac2bba6eda2d4817ea71ad744c09a8d657766df1a061124516e6a5b52a70b50a6c788efbc07e40d3060df8d031b3da24ae627826db0a2fc4e270d802bc695539336845d90f4a7ce5fb2af3ff8b4dbf6e5f9f84c1259d3a1115a9d6884&u=0103c2d008118e6a2db9f70ca8ce7a518ab2b3fe93edac7b6eff5da98a551f46a736d5933ee1412b3bfdd23ddef7ce21ad795a1bb8a99c7ac0e89f6354048db27bd96aef4109f73f176ce5ce151af18110db423847e563ad47866cad9ba0c0a6c9ec651d3ca&requestID=1448270677852644969&page=34&userIP=3682951865&ss=607822';

//好车无忧
#$url = 'http://car.baidu.com/rcv?module=car&city=93&subqid=1448270673408180295&wd=&key=&srcid=1400000&resourceid=1400000&qid=1448269220984714269&pvid=1448269220984714269&pssid=&tn=self&zt=self&srcid_from=1400000&reqtype=0&prod=0103c2d00810bb0b7dbd53681d9aae30fae367ce939d09bbce4bacfd8e71ca13b33735f28f20146ece88732d9e732e75d8fda8aba8488d0a60f96ae611d1c9c31ffcee1db119b79f167815bb90bf752495aa56fc431257bc07b7fdfd9b9400d34c79b118c91a78fd05485b137e7a2991a5051f1d60a8084fe2112cdaeb66f0dabc4c8b8a1c3a276f016968eb1d6685d9a6e53b7e5e29ce062349c2c53029c6f5b8cff0e3c553127de478f97ba927e78f778bdfe45f3e9ccf6cd5be1f894058562a62ce2dfed80da6b90232fb28db1635c6ee5f03cddfa9aee3575fe71602a3648374ab1c4d40e36db8fc0246e2457ac320f5b21a0a99359afa70b0149f9d19442cbbcbbe3f2c78e7bee02de43c09a8d657766df1a061124516e6a5b52a70b50a6c788efbc07e40d3060df8d031b3da24ae627826db0a2fc4e270d802bc695539336845d90f4a7ce5fb2af3ff8b4dbf6e5f9f84c1259d3a1115a9d6884&u=0103c2d008118e6a2db9f70ca88eafc5eff753df42f80d8fbe3f798d5b44de36a682e1825ea1b15b3bcdd23ddef7c&requestID=1448270673408180295&page=31&userIP=3682951865&ss=7460214';

//易车二手车
#$url = 'http://www.baidu.com/zhixin.php?url=K60000KCGkGaz_l4sKntBVTgKjc_nSDAM-N6Kp0GbL_3U7g4IjRMDaV4XrKvMNnBPK5TKEKGUnPKLi_e6GWbJcKm6cZJbYDOtmrNv64cN800gNr6o34wb52dtf5ph-bl4ebf7IT.7Y_a6zlAG2cQtAB7hpaO5tb_YR1NeJlePdxuE3dtSOexlZ-G94TH7qLjROCx7y_HDpprVmuCy2lqd-m.THY0IZF9uANGujYdPj0znjT0mvq1I7qzmy4o5H00TLNBTy-b5HDYPj6zP10vnWmLP1nYPHfLnWc0ugw4TARqnHD0uy-b5H00uyw-TvPGujYs0AP_pyPogLw4TARqn6KsUWYk0Zw-ThdGUh7_5H00XMfqTvN_u6KsTjY0mywGujYzrjbYnHndP1n4P0KGTdqLpgF-UAN1T1Ys0AN3Ijd9mvP-TLPRXgK-rhdsr1VsIh-brWDYPj6zPWbznW04rjfLnHfzPWberv-1N-PfrW0erLKzUvwdmLwFujCvPWf4nWfzPHmervPGIZblrHnervuzUvYlnHfsnj0snj_epvN4IvqzuD-brWc4rHf4nHnLP1fdr1_0mLFW5H64nj0&ss=7559174';

//$url = 'http://www.baidu.com/zhixin.php?url=K60000aPlNeu_HuzhyEIaQp-X_adN6jYhoVgjwTgMunZbS-DQZfk_Os0QnjQQEEu7vH0ptKNwvxJj8CNT46_KDpF5RIDOuYOnDle-KLM_-fWlR80yPvxlPmcmAY1weIZe49LiZC.7R_aunRzmCpbC6W3mcyuCrrSEgjjuvfLXrk5uY47BmWHwdojPakg8lXMX0.THY0IZF9uANGujYdPj0znjT0mvq1I7qzmy4o5H00TLNBTy-b5HDYPj6zP10vPjR1rjmknjm1PHR0ugw4TARqnHD0uy-b5H00uyw-TvPGujYs0AP_pyPogLw4TARqn6KsUWYknsKYugFVpy49UjYs0ZGY5gP-UAm0TZ0q0A7bpyfqnW04n1DkPjRkPHc0pgPxIv-zuyk-TLnqn0K-XZfqmyPWugP1NZ-suHGVTj_eTZuGujCkPjf3nWm4nWcsrH6YP1DYnWm4r1VGTduHRjCsr1VsThqbIyPYiyflPHTdP1D4nWfzr1VWpgw4rWb1r1VhThqVrWDYnj0snj0ervV-XgIEThwFujCznH03rHfLrHTvn1_e0APzm1YkrHm3r0&ss=7659526';
//@file_get_contents($url);
//
//foreach ($http_response_header as $hk => $hsh) {
//
//    if (stristr($hsh, 'Location')) {
//
//        $location = explode('Location: ', $hsh)[1];
//
//    }
//}
//
//$id = preg_location_id($location);
//
//
//$str = $curl->get($location);
//
//
//$html = \Sunra\PhpSimple\HtmlDomParser::str_get_html($str);
//
//$files = renrenche($html);
//
//print_r($files);exit;


function diyiche($html)
{

    $key = 'data-original';
    $files = array();

    $parent = $html->find('#clspzpDiv', 0);

    if(!$parent){
        return array();
    }

    $images = $parent->find('img');


    if(!$images){
        return array();
    }

    unset($images[0]);

    foreach ($images as $img) {

        $files[] = $img->$key;
    }

    return $files;
}

function renrenche($html)
{

    $key = 'data-src';

    $files = array();

    $parent = $html->find('#carousel', 0);

    if(!$parent){
        return array();
    }

    $items = $parent->find('img');

    if(!$items){
        return array();
    }

    foreach ($items as $item) {

        $files[] = $item->$key;

    }

    return $files;
}


function haochewuyou($html)
{
    $key = 'data-original';

    $items = $html->find('img.pop-bimg');

    if(!$items){

        return array();
    }

    $files = array();

    foreach ($items as $k => $item) {

        $files[] = $item->$key;
    }

    return $files;

}

function xin($html)
{

    $key = 'data-original';

    $parent = $html->find('.carimg', 0);

    if(!$parent){
        return array();
    }

    $items = $parent->find('img');

    if(!$items){
        return array();
    }

    $files = array();

    foreach ($items as $k => $item) {

        $files[] = $item->$key;
    }

    return $files;
}

function wuyaoqiche($html)
{

    $key = 'data-original';

    $parent = $html->find('.car-pic', 0);

    if(!$parent){
        return array();
    }

    $items = $parent->find('img');

    if(!$items){
        return array();
    }

    $files = array();

    foreach ($items as $k => $item) {

        $files[] = $item->$key;
    }

    return $files;

}

function yiche($html)
{

    $key = "data-src";
    $files = array();
    $items = $html->find('img[name=lazyimg]');

    if(!$items){
        return array();
    }

    foreach ($items as $item) {

        $files[] = $item->$key;
    }

    return $files;
}

function preg_location_id($location)
{

    $patterns = "/\d+/";
    preg_match_all($patterns, $location, $arr);

    $number = max($arr[0]);
    return $number;
}


//
//
$platforms = array(
    '51汽车' => 'wuyaoqiche',
    'bj-iautos' => 'diyiche',
    '人人车' => 'renrenche',
    '优信二手车' => 'xin',
    '好车无忧' => 'haochewuyou',
    '易车二手车' => 'yiche',
    '第一车网' => 'diyiche',

);

$file_path = '/Users/huanghaitao/bibi-files';

//$db = new PDO('mysql:host=127.0.0.1;dbname=bibi', "root", "itadmin");

$db = new PDO('mysql:host=120.25.62.110;dbname=bibi', "root", "bibi2015");

$db->query('set names utf8');

$curl = new \Curl\Curl();

//foreach ($platforms as $k => $platform) {


    $sql = 'SELECT * FROM `bibi_car_selling_list` WHERE `car_type` = 2';


    $rs = $db->query($sql);

    $rs = $db->prepare($sql);
    $rs->execute();
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);

    $accessKey = QI_NIU_AK;
    $secretKey = QI_NIU_SK;

    // 构建鉴权对象
    $auth = new \Qiniu\Auth($accessKey, $secretKey);

    // 要上传的空间
    $bucket = 'bibi';

    $type = 0;

    foreach ($rows as $row) {

        if($row['files']) {
            continue;
        }

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        $location = '';
        $url = $row['platform_url'];
        @file_get_contents($url);

        foreach ($http_response_header as $hk => $hsh) {

            if (stristr($hsh, 'Location')) {

                $location = explode('Location: ', $hsh)[1];

            }
        }

        $hashId = $row['hash'];

        $str = $curl->get($location);

        $html = \Sunra\PhpSimple\HtmlDomParser::str_get_html($str);

        if(!$html){

            continue;
        }

        $site = $platforms[$row['platform_name']];

        $images = $site($html);

        $files = array();

        $dir = '';

        $dir = $file_path . '/'.$row['baidu_brand_id'].'/'.$row['baidu_series_id'].'/';

        if(!is_dir($dir)){

            $res=mkdir(iconv("UTF-8", "GBK", $dir),0777,true);
        }

        if($images){

            foreach ($images as $k => $image) {

                if($k > 9){

                    continue;
                }

                $image = explode('?', $image)[0];

                $file_ext = explode(".", $image);

                $ext =  strtolower($file_ext[count($file_ext)-1]);

                $key = $hashId . '-' . $k . '.' . $ext;

                $tempfile =  $dir . $key;

                if($site == 'renrenche'){

                    $image = 'https:' . $image;
                }

                $curl->download($image, $tempfile);

                $size = filesize($tempfile);


                if($size > 0){

                    $uploadMgr = new \Qiniu\Storage\UploadManager();

                    list($ret, $err) = $uploadMgr->putFile($token, $key, $tempfile);

                    $files[] = array('hash'=>$ret['hash'],'type'=>$type,'key'=>$ret['key']);

                }

            }

        }


        $fs = $files ? serialize($files) : '';

        $sql = 'UPDATE `bibi_car_selling_list`
                        SET
                        `platform_location`="' . $location . '",
                        `files` = \''.$fs.'\'
                        WHERE
                        `id` = '.$row['id'].'
                        ';

        $db->exec($sql);


        sleep(120);
    //}

}