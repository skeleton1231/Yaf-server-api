<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/4/14
 * Time: 14:51
 */

class ThirdController extends ApiYafControllerAbstract {

    public function callbackAction(){

        $_body = file_get_contents('php://input');

        Common::globalLogRecord('qiniu request' , $_body);

        echo 3333;
    }
}


