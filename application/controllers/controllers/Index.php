<?php
/**
 * @name IndexController
 * @author huanghaitao
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends ApiYafControllerAbstract {

	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/bibi-framework/index/index/index/name/huanghaitao 的时候, 你就会发现不同
     */
	public function indexAction($name = "Stranger") {

        echo md5('123456');
	}

    public function callbackAction(){


        $_body = file_get_contents('php://input');

        Common::globalLogRecord('qiniu request' , $_body);

        $body = json_decode($_body, true);

        $time = time();

        $hash = urldecode($body['hash']);

        $data = array();
        $data['user_id'] = $body['user_id'];
        $data['url'] = IMAGE_DOMAIN .  $hash;
        $data['hash'] = $hash;
        $data['created'] = $time;
        $data['type'] = 1;

        $file = new FileModel();
        $file->Create($data);

        $this->send(array('hash'=>$hash));



    }
}
