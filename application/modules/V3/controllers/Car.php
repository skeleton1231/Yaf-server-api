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

        unset($data["v3/car/create"]);

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
        $properties['car_intro'] = @$data['car_intro']?$data['car_intro']:" ";


        $cs->properties = $properties;

        $id = $cs->CreateM();

       
                    



        if ($id) {

            $mh = new MessageHelper;
            $userM = new ProfileModel();
            $profile = $userM->getProfile($userId);
        
            $contentto = $profile["nickname"].'提交了爱车认证,请尽快到后台提交审核';
            $sysId=389;
            $mh->systemNotify($sysId, $contentto);
        
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

        $carInfo = $carModel->GetCarInfoById($carId,$userId);


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
        $response['share_url'] = 'http://wap.bibicar.cn/car/'.$carId.'?identity='.base64_encode($data['device_identifier']);
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
        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 AND (t1.verify_status = 2 OR t1.verify_status = 11 OR t1.verify_status = 4) ';

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
       if($data['min_price']==200){
          $where .=' AND t1.price >='.$data['min_price'].' ';
       }else{
        
            if($data['min_price']){    
                 $where .=' AND t1.price >='.$data['min_price'].' ';
            }

             if($data['max_price']){    
                 $where .=' AND t1.price <='.$data['max_price'].' ';
            }
       }

       if($data['min_mileage']==15){
             $min_mileage=$data['min_mileage']*10000;
             $where .=' AND t1.mileage >='.$min_mileage.' ';
       }else{
        
            if($data['min_mileage']){
                 $min_mileage=$data['min_mileage']*10000;
                 $where .=' AND t1.mileage >='.$min_mileage.' ';
            }
             if($data['max_mileage']){
                 $max_mileage=$data['max_mileage']*10000;
                 $where .=' AND t1.mileage <='.$max_mileage.' ';
            }
        }
       
         $year=date("Y");
         if($data['min_board_time']==10){
               $min=$year-$data['min_board_time'];
               $where .=' AND t1.board_time <='.$min.' ';
         }else{

            if($data['min_board_time']){
                $max=$year-$data['min_board_time'];
                $where .=' AND t1.board_time <='.$max.' ';
            } 
             if($data['max_board_time']){
                $min=$year-$data['max_board_time'];
                $where .=' AND t1.board_time >='.$min.' ';
            } 
        
        }

        if(@$data['has_vr']==1){

            $where .= 'AND t1.vr_url is not null';
        }
       
        if($data['old']){
            if($data['old'] == 1){
               $data['car_type']=0;
               $where.=' AND t1.car_type='.$data['car_type'].' ';
            }else if($data['old']==2){
               if($data['source']){
                $data['car_type']=$data['source'];
                $where.=' AND t1.car_type='.$data['car_type'].' ';
               }else{
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

        $lists = $carM->getCarList($userId);

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
        
        $objId = $this->getAccessId($data,$userId);

        $car = new CarSellingModel();
        $car->currentUser = $userId;
        $response['list'] = $car->getUserCars($objId);

        $this->send($response);
    }




    public function checkcarAction(){
        
        $this->required_fields = array_merge($this->required_fields, array('session_id','city','hphm','classno','engineno',));
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);
       
        $wz=new WeiZhang();
       /* $data['city']="GD_GZ";
        $data['hphm']="粤AN324Y";
        $data['classno']="741406";
        $data['engineno']="604825";
        $userId='389';
        */
        $time=time();
        $HascheckcarM= new HascheckcarModel();
        $HascheckcarM->user_id  = $userId;
        $HascheckcarM->city     = $data['city'];
        $HascheckcarM->hphm     = $data['hphm'];
        $HascheckcarM->engineno = $data['engineno'];
        $HascheckcarM->classno  = $data['classno'];
        $HascheckcarM->created  = $time;
        $HascheckcarM->saveProperties();
        $HascheckcarId = $HascheckcarM->CreateM();

        $city=$data['city'];
        $hphm=$data['hphm'];   //车牌号码
        $classno=$data['classno'];    //车架号
        $engineno=$data['engineno'];    //发动机号

        $result=$wz->query($city,$hphm,$engineno,$classno);

        $this->send($result);
    
    }


     public function ContactSellerAction(){
        
        $this->required_fields = array_merge($this->required_fields, array('session_id','seller_id',));
        $data = $this->get_request_data();
        unset($data["v3/car/contactseller"]);
        $userId = $this->userAuth($data);
        $data['user_id']=$userId;
        $data['created']=time();
        $car = new CarSellingModel();
        $result=$car->insertContactSeller($data);
        $this->send($result);
    
    }


    public function applyloanAction(){
      
       
        $this->required_fields = array_merge($this->required_fields, array('carid','contact_name','mobile','pay_scale','pay_stages'));
        $data = $this->get_request_data();
        unset($data["v3/car/applyloan"]);
        $ApplyLoanM = new ApplyLoanModel();

        $loan=$ApplyLoanM ->getloan($data['mobile'],$data['contact_name'],$data['carid']);
        if($loan){
             $this->send_error(APPLY_LOAN_ERROR);
        }
        $data['created']=time();
        $ApplyLoanM->carid       =$data['carid'];
        $ApplyLoanM->contact_name=$data['contact_name']; 
        $ApplyLoanM->mobile      =$data['mobile'];
        $ApplyLoanM->pay_scale   =$data['pay_scale'];
        $ApplyLoanM->pay_stages  =$data["pay_stages"];
        $ApplyLoanM->created     =$data['created'];
        $ApplyLoanM->saveProperties();
        $ApplyLoanId=$ApplyLoanM->CreateM();
        $this->send($ApplyLoanId);
    
    }


    public function applycarAction(){
      
       
        $this->required_fields = array_merge($this->required_fields, array('is_burn','is_strike','is_soak','brand_name','post_type','price','mileage','age','maintenance','maintain','desc','name','phone'));
        $data = $this->get_request_data();
        unset($data["v3/car/applycar"]);
        $ApplyCarM = new ApplyCarModel();
        /*
        $loan=$ApplyCarM ->getapply($data['mobile'],$data['contact_name'],$data['carid']);
        if($loan){
             $this->send_error(APPLY_LOAN_ERROR);
        }
       
        $data['is_burn']=0;
        $data['is_strike']=0; 
        $data['is_soak']=0;
        $data['brand_name']=1;
        $data["pay_stages"]=1;
        $data['post_type']=1;
        $data['price']='56.5';
        $data['mileage']='25000'; 
        $data['age']="1522";
        $data['maintenance']='good';
        $data["maintain"]='1';
        $data['desc']="saasfasld";
        $data["name"]="jamp";
        $data['phone']="2435435435";
        */
        $data['created']=time();
        $str=json_encode($data);
        $ApplyCarM->info         =$str;
        $ApplyCarM->created      =$data['created'];
        $ApplyCarM->saveProperties();
        $ApplyCarId=$ApplyCarM->CreateM();
        $this->send($ApplyCarId);
    
    }









}
