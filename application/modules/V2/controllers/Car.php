<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class CarController extends ApiYafControllerAbstract
{


    public function createAction()
    {
    $this->required_fields = array_merge(
            $this->required_fields,
            array(
                'session_id',
                'car_no',
                'brand_id',
                //'city_id',
                'series_id',
                'files_id',
                'files_type'
            ));

        $this->optional_fields = array('model_id', 'vin_no', 'vin_file');

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);


        if (!json_decode($data['files_id']) || !json_decode($data['files_type'])){

            $this->send_error(CAR_CREATE_FILES_ERROR);
        }


        if (!$data['vin_no'] && !$data['vin_file']) {

            $this->send_error(CAR_DRIVE_INFO_ERROR);
        }

        $cs = new CarSellingModel();

        $properties = $data;
        unset($properties['device_identifier']);
        unset($properties['session_id']);
        unset($properties['files_id']);
        unset($properties['files_type']);
        unset($properties['car_id']);


        $bm = new BrandModel();
        $brandM  = $bm->getBrandModel($data['brand_id']);
        $seriesM = $bm->getSeriesModel($data['brand_id'],$data['series_id']);
        $modelM  =  $bm->getModelModel($data['series_id'], $data['model_id']);


        if(!is_array($brandM)){

            $this->send_error(CAR_BRAND_ERROR);
        }

        if(!is_array($seriesM)){
            $this->send_error(CAR_SERIES_ERROR);
        }

        if(!is_array($modelM)){

            $this->send_error(CAR_MODEL_ERROR);
        }


        $properties['car_name'] = $brandM['brand_name'] . ' ' . $seriesM['series_name'] . ' ' . $modelM['model_name'];
        $properties['car_name'] = trim($properties['car_name']);



//        if (isset($properties['vin_file'])) {
//
//            $vinFile = new FileModel();
//            $vinFile = $vinFile->Get($properties['vin_file']);
//            $properties['vin_file'] = $vinFile;
//
//        }

        $properties['car_type'] = PLATFORM_USER_OWNER_CAR;
        $time = time();
        $properties['created'] = $time;
        $properties['updated'] = $time;
        $properties['user_id'] = $userId;
        $properties['verify_status'] = CAR_NOT_AUTH;
        $properties['files'] = serialize($cs->dealFilesWithString($data['files_id'], $data['files_type']));
        $properties['hash'] = uniqid();

        $cs->properties = $properties;

        $id = $cs->CreateM();


        if ($id) {

            //插入文件
            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($id, $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $carInfo = $cs->GetCarInfoById($properties['hash']);

            $response['car_info'] = $carInfo;

            $this->send($response);

        } else {

            $this->send_error(CAR_ADDED_ERROR);
        }


    }

    public function updateAction()
    {


    }

    public function deleteAction()
    {

        $this->required_fields = array_merge(
            $this->required_fields,
            array(
                'session_id',
                'car_id'
            ));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $carModel = new CarSellingModel();

        $carModel->deleteCarById($userId, $data['car_id']);

        $this->send();
    }

    public function indexAction()
    {
        
        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }

        $carModel = new CarSellingModel();

        $carT = $carModel::$table;

        $carId = $data['car_id'];

        $carModel->currentUser = $userId;

        $carInfo = $carModel->GetCarInfoById($carId);


        $response['car_info'] = $carInfo;

        $brandId = isset($carInfo['brand_info']['brand_id']) ? $carInfo['brand_info']['brand_id'] : 0;


        $response['car_users'] = $carModel->getSameBrandUsers($brandId);

        //同款车
        $response['related_price_car_list'] = $carModel->relatedPriceCars($carId,$carInfo['price']);

        //同价车
        $response['related_style_car_list'] = $carModel->relatedStyleCars(
            $carId,
            $carInfo['brand_info']['brand_id'] ,
            $carInfo['series_info']['series_id']
        );


        $visitCarM = new VisitCarModel();
        $visitCarM->car_id  = $carId;
        $visitCarM->user_id = $userId;

        $id = $visitCarM->get();

        if(!$id){

            $properties = array();
            $properties['created'] = time();
            $properties['user_id'] = $userId;
            $properties['car_id']  = $carId;

            $carModel->updateByPrimaryKey(
                $carT,
                array('hash'=>$carId),
                array('visit_num'=>($carInfo['visit_num']+1))
            );

            $visitCarM->insert($visitCarM->tableName, $properties);
        }

        $title = is_array($carInfo['user_info']) ?
                    $carInfo['user_info']['profile']['nickname'] . '的' . $carInfo['car_name']
                    : $carInfo['car_name'];

        $response['share_title'] = $title;
        //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
        $response['share_url'] = 'http://wx.bibicar.cn/car/index/car_id/'.$carId.'';
        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
        $response['share_img'] = isset($carInfo['files'][0]) ? $carInfo['files'][0]['file_url'] : '';

        $this->send($response);


    }




    public function listAction(){

        
        $jsonData = require APPPATH .'/configs/JsonData.php';

        $this->optional_fields = array('keyword','order_id','brand_id','series_id');
        //$this->required_fields = array_merge($this->required_fields, array('session_id'));
        

        $data = $this->get_request_data();

        $data['order_id'] = $data['order_id'] ? $data['order_id'] : 0 ;
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        $data['brand_id'] = $data['brand_id'] ? $data['brand_id'] : 0 ;
        $data['series_id'] = $data['series_id'] ? $data['series_id'] : 0 ;


        $carM = new CarSellingModel();
        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 ';

      if($data['keyword']){
            $carM->keyword = $data['keyword'];
            $where .= ' AND t1.car_name LIKE "%'.$carM->keyword.'%" ';
        }

        if($data['brand_id']){

            $where .= ' AND t1.brand_id = '.$data['brand_id'].' ';
        }

        if($data['series_id']){

            $where .= ' AND t1.series_id = '.$data['series_id'].' ';
        }

      /*  if($data['source'] == 1){

            $where .= ' AND t1.car_type = 1';
        }
     */
    
        if($data['min_price']){    
             $where .=' AND t1.price >='.$data['min_price'].' ';
        }

         if($data['max_price']){    
             $where .=' AND t1.price <='.$data['max_price'].' ';
        }

        
        if($data['min_mileage']){
             $min_mileage=$data['min_mileage']*10000;
             $where .=' AND t1.mileage >='.$min_mileage.' ';
        }
         if($data['max_mileage']){
             $max_mileage=$data['max_mileage']*10000;
             $where .=' AND t1.mileage <='.$max_mileage.' ';
        }
        
        
         $year=date("Y");
        if($data['min_board_time']){
           
            $max=$year-$data['min_board_time'];
            $where .=' AND t1.board_time <='.$max.' ';
        } 
         if($data['max_board_time']){
          
            $min=$year-$data['max_board_time'];
            $where .=' AND t1.board_time >='.$min.' ';
        } 
        
       
        if($data['old']){
            if($data['old']==0){
               $data['car_type']=0;
               $where.=' AND t1.car_type='.$data['car_type'].' ';
            }else{
               if($data['source']){
                $data['car_type']=$data['source'];
                $where.=' AND t1.car_type='.$data['car_type'].' ';
               }else{
                //$data['car_type']='1,2';
                $car1=1;
                $car2=2;
                $where.=' AND t1.car_type='.$car1.' ';
                $where.=' OR t1.car_type='.$car2.' ';

               }
               
            }
        }else{
            if($data['source']){
               $data['car_type']=$data['source'];
               $where.=' AND t1.car_type='.$data['car_type'].' ';
            }
        }

         

        $carM->where = $where;
        

         

        if(isset($jsonData['order_info'][$data['order_id']])) {

           // $carM->order  = ' ORDER BY t1.car_type ASC , ';
            $carM->order = $jsonData['order_info'][$data['order_id']];

        }

        $carM->page = $data['page'];

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $carM->currentUser = $userId;

        $lists = $carM->getCarList();

        if($lists['car_list']){

            foreach($lists['car_list'] as $key => $list){

                $file = isset($list['car_info']['files'][0]) ?  $list['car_info']['files'][0] : array();

                $lists['car_list'][$key]['car_info']['files'] = array();
                $lists['car_list'][$key]['car_info']['files'][] = $file;
            }
        }


        //$response = array();
        $response = $lists;
        $response['order_id'] = $data['order_id'];

        if($data['city_id']){

            $jsonData['city_info']['city_id'] = $data['city_id'];
            $jsonData['city_info']['city_lat'] = $data['city_lat'];
            $jsonData['city_info']['city_lng'] = $data['city_lng'];

        }

        $response['city_info'] = $jsonData['city_info'];
        $response['keyword']   = $data['keyword'];
        $bm = new BrandModel();
        $response['brand_info'] = $bm->getBrandModel($data['brand_id']);
        $response['series_info'] = $bm->getSeriesModel($data['brand_id'],$data['series_id']);
        
        $this->send($response);

    }


    public function userfavoriteAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $objId = $this->getAccessId($data, $userId);

        $car = new CarSellingModel();

        $response = $car->getUserCar($objId);

        $this->send($response);
    }


    public function userFavCarsAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $objId = $this->getAccessId($data, $userId);

        $car = new CarSellingModel();

        $response['list'] = $car->getUserCars($objId);

        $this->send($response);
    }


    public function checkcarAction(){

        $hphm='粤BJ47T3';   //车牌号码
        $classno="156920";    //车架号
        $engineno="920578";    //发动机号
        $registno=""; //证书编号
        $city_id="152";   //违章查询地城市ID
        $car_type="02";    //车辆车类型（"02"：小型汽车）
        $car_info="hphm=".$hphm."&classno=".$classno."&engineno=".$engineno."&registno=".$registno."&city_id=".$city_id."&city_type=".$car_type;

        //print_r($car_info);exit;
        $car_info=urlencode($car_info);
       // print_r($car_info);exit;
        $car_info="hphm%3d%e7%b2%a4BJ47T3%26classno%3d156920%26engineno%3d920578%26%23174%3bistno%3d%26city_id%3d152%26city_type%3d02";
        list($usec, $sec) = explode(" ", microtime());
        $timestamp=((float)$usec + (float)$sec);
        $app_id="2011";
        $app_key="b1e312f56de613b049fa308384d7d925";
        $sign=md5($app_id+$car_info+$timestamp+$app_key);
        $url="http://www.cheshouye.com/api/weizhang/query_task?car_info=".$car_info."&sign=".$sign."&timestamp=".$timestamp."&app_id=".$app_id;
        

             //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        print_r($data);
    
    }


   




}
