<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/22
 * Time: 下午9:57
 */

ini_set('max_execution_time', 0);
set_time_limit(0);

class ScriptController extends ApiYafControllerAbstract
{

    public function carTypeAction(){



        $file = APPPATH . '/static/BrandList.json';
        $myfile = fopen($file, "r") or die("Unable to open file!");
        $json = fread($myfile,filesize($file));
        $array = json_decode($json,true);

        $brandList = $array['brandList'];
        //print_r($brandList);
        fclose($myfile);


        foreach($brandList as $key => $brand){


            $brandId = $brand['brandId'];
            $path = $file = APPPATH . "/static/series_{$brandId}.json";
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


    }

    public function userRegisterAction(){


        $ar = new ApiRequest();

        for($i=1; $i<= 10; $i++){

            $ar->appRegister();

            $ar->userSendCode();

            $ar->userRegister();

            $ar->userUpdateAll();

            $ar->carCreate();

            $ar->publishCarCreate();

            sleep(1);
        }


//        /$ar->appRegister();

        //$ar->carCreate();


    }

    public function publishCarAction(){

        $ar = new ApiRequest();
        $ar->publishCarCreate();

    }




}