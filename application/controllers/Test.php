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

        print_r(new Qiniu\Auth('',''));

    }
}