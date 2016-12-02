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


        exit;

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

         //   $ar->carCreate();

         //   $ar->publishCarCreate();

            
        }


//        /$ar->appRegister();

        //$ar->carCreate();


    }

    public function publishCarAction(){

        $ar = new ApiRequest();
        $ar->publishCarCreate();

    }

    public function getCarAction(){

        exit;

        $rand = rand(0,500);

        $pdo = new PdoDb();
        $sql = 'SELECT * FROM `bibi_car_selling_list` WHERE `platform_name` = "优信二手车"  LIMIT '.$rand.',  1';

        $car = $pdo->query($sql)[0];


        $files = unserialize($car['files']);

        foreach($files as $k => $file){
            if($k < 9){

                $keys[] = $file['key'];
                $type[] = 1;
            }

        }

        echo json_encode($keys);
        echo "<br />";
        echo json_encode($type);
        echo "<br />";
    }


    public function getPostAction(){

        for($j = 1; $j<=2; $j++){

            $ar = new ApiRequest();
            $response = $ar->postCreateAction();

            $feedId = $response->feed_id;

            $userId = $response->post_user_info->user_id;


            $userIds = range(1,10);

            $replyIds = array();

            for($i=0;$i<10;$i++){

                if($userIds[$i]%2==0){

                    $num = count($replyIds);

                    Common::globalLogRecord('replyIds json:',json_encode($replyIds));

                    Common::globalLogRecord('from user:' , $userIds[$i]);

                    Common::globalLogRecord('replyIds:' , $replyIds[$num-1]);


                    $ar->commentCreateAction($feedId , $userIds[$i], $userIds[$i-1] , $replyIds[$num-1]);

                }
                else{

                    $replyId = $ar->commentCreateAction($feedId , $userIds[$i], $userId, 0);

                    Common::globalLogRecord('comment id:' , $replyId);

                    $replyIds[] = $replyId;

                }


                $ar->likeCreateAction($feedId,$userIds[$i]);

            }

        }

    }

    public function updatesortAction(){

        $sql="SELECT to_id, COUNT(*) as num FROM bibi_comments GROUP BY to_id";

        $db=new PdoDb;

        $result=$db->query($sql);
        
        foreach($result as $key =>$value){
                $num=$value['num']*5;
                $condition = 'sort = sort +'.$num ;

                $sql = '
                    UPDATE
                    `bibi_user_profile`
                    SET
                    '.$condition.'
                    WHERE
                    `user_id` = '.$value['to_id'].';
                ';

                $db->exec($sql);
        }

       
    }



}