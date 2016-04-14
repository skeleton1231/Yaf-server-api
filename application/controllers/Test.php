<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午12:54
 */


class TestController extends Yaf_Controller_Abstract {

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/bibi-framework/index/index/index/name/huanghaitao 的时候, 你就会发现不同
     */
    public function checkAction($name = "Stranger", $sex="bas") {
//
//        $client = new RedisDb( );
//        $value = RedisDb::getValue('foo');
//        print_r($value);

      //  print_r(new Qiniu\Auth('',''));
//
//        $db = new PdoDb();
//
//        $data = $db->query('SELECT * FROM `bibi_car_brand_list`');
//
//        print_r($data);exit;

        $str = 'YmliaS1maWxlNTY1OGEwZDQ4OWUwYg==,YmliaS1maWxlNTY1OGEwZDRkN2IwYw==,YmliaS1maWxlNTY1OGEwZDUyOWRjOA==,YmliaS1maWxlNTY1OGEwZDU4MzY0NQ==,YmliaS1maWxlNTY1OGEwZDVjOTQ3Nw==,YmliaS1maWxlNTY1OGEwZDYxYWYzNg==,YmliaS1maWxlNTY1OGEwZDY2YWI5MA==,YmliaS1maWxlNTY1OGEwZDZhYThkNA==,YmliaS1maWxlNTY1OGEwZDZkZmZjOA==,YmliaS1maWxlNTY1OGEwZDcyMmQ1Yg==';

        $array = explode(',' , $str);

        echo json_encode($array);


        echo "\n";

        $numbers = array(1,2,3,4,5,6,7,8,9,10);

        echo json_encode($numbers);

    }

    public function userCarsAction(){

        $rs = RedisDb::getValue('test_car_users');

        //exit;
        $jsonData = require APPPATH .'/configs/JsonData.php';

        $sql = 'SELECT
	              t1.user_id,t2.avatar,t2.nickname
                FROM
	            `bibi_user` AS t1
                LEFT JOIN `bibi_user_profile` AS t2
                on t1.user_id = t2.user_id
                LIMIT 0, 10';

        $db = new PdoDb();

        $data = $db->query($sql);

        foreach($data as $k => $d){

            $userData = $jsonData['user_info'];
            $userData['user_id'] = $d['user_id'];
            $userData['profile']['nickname'] = $d['nickname'];
            $userData['profile']['avatar']   = $d['avatar'];

            $items[] = $userData;

        }

        $str = serialize($items);

        RedisDb::setValue('test_car_users' , $str);

    }

    public function smsAction(){

        $mobile = '15999593293';

       // $rest = Common::sendSMS($mobile,array('1234'),74511);
    }
}