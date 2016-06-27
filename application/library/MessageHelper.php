<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/6/27
 * Time: 00:21
 */

class MessageHelper{

    public $handler;

    const SYSTEM_TYPE = 1;
    const COMMENT_TYPE = 2;
    const LIKE_TYPE = 3;

    public function __construct(){

        $this->handler = new RcloudServerAPI(RCLOUD_APP_KEY, RCLOUD_APP_SECRET);

    }

    public function commentNotify($toId,$content){

        $data['content'] = $content;
        $data = json_encode($data);

        $rs = $this->handler->messagePublish(self::COMMENT_TYPE, array($toId),"RC:TxtMsg",$data,$content);

        return $rs;
    }

    public function likeNotify($toId,$content){

        $data['content'] = $content;
        $data = json_encode($data);

        $rs = $this->handler->messagePublish(self::LIKE_TYPE, array($toId),"RC:TxtMsg",$data,$content);

        return $rs;
    }

    public function recommendNotify($toId, $carId){

        $carModel = new CarSellingModel();
        $carInfo = $carModel->GetCarInfoById($carId);

        $imgUrl = $carInfo['files'][0]['file_url'];
        $content['title'] = '二手车推荐';
        $content['content'] = $carInfo['car_name'];
        $content['imageUri'] = $imgUrl;
        $content['url'] = 'bibicar://gotoCar?car_id=5762e10a94a11';
        $content = json_encode($content);

        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE, array($toId),"RC:ImgTextMsg",$content,'你有新的二手车推荐');

        return $rs;
    }

    public function wzNotify($toId, $info){

//        $info = '{
//			"date":"2013-12-29 11:57:29",
//			"area":"316省道53KM+200M",
//			"act":"16362 : 驾驶中型以上载客载货汽车、校车、危险物品运输车辆以外的其他机动车在高速公路以外的道路上行驶超过规定时速20%以上未达50%的",
//			"code":"",
//			"fen":"6",
//			"money":"100",
//			"handled":"0"
//			}';

        $content = json_decode($info,true);

        $json = array('content'=>$content , 'extra'=>'');

        $json = json_encode($json);

        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE , array($toId),"BBMsg",$json,'你有新的违章消息');

        return $rs;
    }
}