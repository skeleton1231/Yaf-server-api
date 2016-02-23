<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/2/13
 * Time: 上午8:47
 */


class PushController extends ApiYafControllerAbstract {


    public function testAction(){

        $data = $this->get_request_data();

        $device = $data['device_token'];

        $message = '这是一条测试推送';

        unset($data['device_token']);

        $pushM = new PushNotification();

        $pushM->push_ios($device,$message,1,1,$data);

        $this->send();
    }
}


