<?php
/*
* 覆盖carsellginlist表的board_time格式
* 
* 
*/
class CarsellingController extends ApiYafControllerAbstract
{
   //统一上牌时间脚本
  public function updatetimeAction(){
       
        $db=new CarSellingModel;
        $sql = '
                 SELECT
                      hash,
                      board_time
                    FROM
                      `bibi_car_selling_list` 
                    WHERE
                    `car_type` = 1 
        ';
        $data = $db->query($sql);
       foreach($data as $key =>$value){
              $data[$key]['board_time']=date('Y',strtotime($value['board_time']));
       }

       foreach($data as $key => $value){
         $sql = '
                 UPDATE
                    `bibi_car_selling_list` 
                    set `board_time`='.$value['board_time'].'
                    WHERE
                    `hash`='."'".$value['hash']."'".'
        ';
         $info = $db->query($sql);
     }


  }


  public function couponAction(){
       
       
       $this->required_fields = array_merge($this->required_fields,array('session_id','action'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);
        
        $CarSellingM=new CarSellingModel();
     
        $car=$CarSellingM->getUserCarshascheck($userId);
       
        if($data['action'] == "check"){
            
              if(!$car){
                   $response['status']=0;
                   $response['user_id']=$userId;
                   $this->send($response);
              }else{ 
                   $db= new PdoDb;
                   $sql="select status,user_id,code from bibi_coupon where user_id=".$userId;
                   $result=$db->query($sql);
                  
                  if($result){
                              $response['status']  = $result[0]['status'];
                              $response['code']    = $result[0]['code'];
                              $response['user_id'] = $userId;
                  }else{
                    $time=time();
                 
                    $num = $this->GetRandStr(4);  
                    $num = "1001-".$userId."-".$num;
                     $sql="INSERT INTO bibi_coupon (created,updated,user_id,code) VALUES(".$time.",".$time.",".$userId.","."'".$num."'".")";
                     $result=$db->query($sql);

                    $response['status']=2;
                    $response['user_id']=$userId;
                    $response['code']  =$num;
                  }
                 
                  $this->send($response);
              }
             
        }else if($data['action'] == "change"){
             $db= new PdoDb;
             $sql1= "select status,user_id,code from bibi_coupon where user_id=".$userId;
             $result=$db->query($sql1);
             $status=$result[0]['status'];
             $code  =$result[0]['code'];
             if($status < 4){
                 $status=$result[0]['status']+1;

               if($status == 3){
                    $mh = new MessageHelper;
                    $userM = new ProfileModel();
                    $profile = $userM->getProfile($userId);
                    $content = $profile["nickname"].',恭喜你成功领取优惠劵,到店点击链接出示二维码给客服即可使用优惠劵'.'http://share.bibicar.cn/coupon'.'?se='.base64_encode($data['session_id']).'&identity='.base64_encode($data['device_identifier']);
                    $mh->systemNotify($userId, $content);
                }
               if($status == 4){
                   
                    $mh = new MessageHelper;
                    $userM = new ProfileModel();
                    $profile = $userM->getProfile($userId);
                    $content = '你的优惠劵已成功使用';
                    $mh->refreshNotify($userId,$content);
                }
                  if($car){
                       $db= new PdoDb;
                       $updated=time();
                       $sql="UPDATE bibi_coupon  SET status = ".$status.",updated=".$updated." WHERE user_id=".$userId;
                       $result=$db->query($sql);
                   }
             

             }

            $response['status']=$status;
            $response['user_id']=$userId;
            $response['code']=$code;

            $this->send($response);
            
        }
     
        
  }

public function testAction(){

                    $userId=456;
                    $mh = new MessageHelper;
                    $userM = new ProfileModel();
                    $profile = $userM->getProfile($userId);
                    $content = '你的优惠劵已使用1';
                    $mh->refreshNotify($userId,$content);
}


 public function GetRandStr($len)   
{  
    
    $chars = array(   
        "A", "B", "C", "D", "E", "F", "G",    
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",    
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",    
        "3", "4", "5", "6", "7", "8", "9"   
    );   
     
    $charsLen = count($chars) - 1;   
    shuffle($chars);     
    $output = "";   
    for ($i=0; $i<$len; $i++)   
    {   
        $output .= $chars[mt_rand(0, $charsLen)];   
    }    
    
    return $output; 

}   

public function intestAction(){
      /*
        $favCarM = new FavoriteCarModel();
        $favCarM->user_id = 544;
        $favCarM->car_id  = "5821671a4fc12";
        $favId = $favCarM->get();
        print_r($favId);
       /* $favkey = 'favorite_'.$userId.'_'.$car['car_id'].'';
        $favId = RedisDb::getValue($favkey);
        */
}

  






}