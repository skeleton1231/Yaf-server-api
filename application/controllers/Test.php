<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午12:54
 */
class TestController extends Yaf_Controller_Abstract
{

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/bibi-framework/index/index/index/name/huanghaitao 的时候, 你就会发现不同
     */
    public function checkAction($name = "Stranger", $sex = "bas")
    {
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

        $array = explode(',', $str);

        echo json_encode($array);


        echo "\n";

        $numbers = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

        echo json_encode($numbers);

    }

    public function userCarsAction()
    {

        $rs = RedisDb::getValue('test_car_users');

        //exit;
        $jsonData = require APPPATH . '/configs/JsonData.php';


        $sql = '
                SELECT t1.brand_id, t2.user_id,t2.nickname,t2.avatar FROM `bibi_car_selling_list` AS t1 LEFT JOIN `bibi_user` AS t2 ON t1.user_id = t2.user_id
                WHERE t1.`car_type` = 3
                ';

        echo $sql;
        exit;
        $db = new PdoDb();

        $data = $db->query($sql);

        print_r($data);
        exit;
//
//        $sql = 'SELECT
//	              t1.user_id,t2.avatar,t2.nickname
//                FROM
//	            `bibi_user` AS t1
//                LEFT JOIN `bibi_user_profile` AS t2
//                on t1.user_id = t2.user_id
//                LIMIT 0, 10';

        $db = new PdoDb();

        $data = $db->query($sql);

        foreach ($data as $k => $d) {

            $userData = $jsonData['user_info'];
            $userData['user_id'] = $d['user_id'];
            $userData['profile']['nickname'] = $d['nickname'];
            $userData['profile']['avatar'] = $d['avatar'];

            $items[] = $userData;

        }

        $str = serialize($items);

        RedisDb::setValue('test_car_users', $str);

    }

    public function smsAction()
    {

        $mobile = '15999593293';

        // $rest = Common::sendSMS($mobile,array('1234'),74511);
    }

    public function forwardAction()
    {


//        for($i =0; $i < 3; $i++){
//
//            RedisDb::saveForwardUser(1,$i);
//        }

        $results = RedisDb::getForwardUsers(1);

        print_r($results);
    }


    public function sysMessageAction($type = 1)
    {


        $rc = new RcloudServerAPI(RCLOUD_APP_KEY, RCLOUD_APP_SECRET);

        $userId = 314;

        if ($type == 1) {

            $content = '{
			"date":"2013-12-29 11:57:29",
			"area":"316省道53KM+200M",
			"act":"16362 : 驾驶中型以上载客载货汽车、校车、危险物品运输车辆以外的其他机动车在高速公路以外的道路上行驶超过规定时速20%以上未达50%的",
			"code":"",
			"fen":"6",
			"money":"100",
			"handled":"0"
			}';

            $content = json_decode($content, true);

            $json = array('content' => $content, 'extra' => '');

            $json = json_encode($json);

            $rc->messagePublish(1, array($userId), "BBMsg", $json, '你有新的违章消息');

        } elseif ($type == 2) {

            $carModel = new CarSellingModel();
            $carInfo = $carModel->GetCarInfoById('576bb220c300c');

            $imgUrl = $carInfo['files'][0]['file_url'];
            $content['title'] = '二手车推荐';
            $content['content'] = $carInfo['car_name'];
            $content['imageUri'] = $imgUrl;
            $content['url'] = 'bibicar://gotoCar?car_id=5762e10a94a11';
            $content = json_encode($content);

            $rs = $rc->messagePublish(1, array($userId), "RC:ImgTextMsg", $content, '你有新的二手车推荐');

        } elseif ($type == 3) {

            $content['content'] = 'xxxxx';
            $content = json_encode($content);
            $rc->messagePublish(1, array($userId), "RC:TxtMsg", $content, '你有新的一条消息');


        } elseif ($type == 4) {

            $content['content'] = 'xxx评论了你';
            $content = json_encode($content);
            $rc->messagePublish(2, array($userId), "RC:TxtMsg", $content, 'xxx评论了你');
        } elseif ($type == 5) {

            $content['content'] = 'xxx赞了你';
            $content = json_encode($content);
            $rc->messagePublish(3, array($userId), "RC:TxtMsg", $content, 'xxx赞了你');
        }

    }

    public function loadUserAction()
    {


        $path = APPPATH . '/static/bibi/namelist3.csv';

        $file = fopen($path, "r");

        $users = array();

        while ($data = fgetcsv($file)) {

            $item = explode(';', $data[0]);
            $user = array();
            $user['mobile'] = $item[0];
            $user['nickname'] = $item[1];
            $user['gender'] = $item[2];
            
            $users[] = $user;
        }

        fclose($file);
        
        $ar = new ApiRequest();

        foreach($users as $k => $user){

            //$ar->appRegister();
            $ar->device_identifier = '706a134330147953333122218df046e7';
            $ar->code = '65832';
            $ar->mobile = $user['mobile'];
            $ar->nickname = $user['nickname'];
            $ar->password = md5($user['mobile']);
            $ar->userRegister();
        }


//        $users = array(
//            //array('mobile'=>'bibi01','nickname'=>"旅先生"),
//            array('mobile'=>'bibi02','nickname'=>"Tony-P"),
//            array('mobile'=>'bibi03','nickname'=>"松鼠"),
//            array('mobile'=>'bibi04','nickname'=>"Jazz-P"),
//            array('mobile'=>'bibi05','nickname'=>"糖先生"),
//            array('mobile'=>'bibi06','nickname'=>"张大宁"),
//            array('mobile'=>'bibi07','nickname'=>"MR·胡"),
//            array('mobile'=>'bibi08','nickname'=>"吴简"),
//            array('mobile'=>'bibi09','nickname'=>"PP-34"),
//            array('mobile'=>'bibi10','nickname'=>"James-P"),
//            array('mobile'=>'bibi11','nickname'=>"Noble-李尔",),
//            array('mobile'=>'bibi13','nickname'=>"Kevin"),
//            array('mobile'=>'bibi14','nickname'=>"萝卜-夢"),
//            array('mobile'=>'bibi15','nickname'=>"Sam-P"),
//            array('mobile'=>'bibi16','nickname'=>"车先锋"),
//        );
//
//        $ar = new ApiRequest();
//
//        foreach($users as $k => $user){
//
//            //$ar->appRegister();
//            $ar->device_identifier = '706a134330147953333122218df046e7';
//            $ar->code = '65832';
//            $ar->mobile = $user['mobile'];
//            $ar->nickname = $user['nickname'];
//            $ar->password = md5($user['mobile']);
//            $ar->userRegister();
//        }
        $file = '';

    }


}