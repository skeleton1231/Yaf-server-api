<?php
/**
 * Created by PhpStorm.
 * User: jp
 * Date: 16/4/14
 * Time: 14:51
 */
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
class YicheController extends ApiYafControllerAbstract {

    public function brandAction(){
           exit;
           $url ='http://carapi.ycapp.yiche.com/car/getmasterbrandlist?allmasterbrand=true';
           $html=file_get_contents($url);
           $data=json_decode($html,true)['data'];
          // print_r($data);
          // $arr=Array();
           foreach ($data as $key => $value){         
          // $arr[$key]['masterId']=$value['masterId'];
          
           //防止品牌重复插入
           $list_brand=$this->is_exist('id','bibi_car_brand_list_yiche_test','brand_id',$value['masterId']);
           if(!$list_brand){

           $list=$this->is_exist('brand_id','bibi_brand_limit1','brand_id',$value['masterId']);
           if($list){
           	$value['is_hot']=1;
           }else{
           	$value['is_hot']=2;
           }

           $sql = "INSERT INTO bibi_car_brand_list_yiche_test (brand_id,brand_name,masterId,abbre,brand_url,is_hot,uv,saleStatus) VALUES("."'".$value["masterId"]."'".","."'".$value["name"]."'".","."'".$value["masterId"]."'".","."'".$value["initial"]."'".","."'".$value["logoUrl"]."'".","."'".$value["is_hot"]."'".","."'".$value["uv"]."'".","."'".$value["saleStatus"]."'".")";
          
           $pdo = new PdoDb;
           $list = $pdo->query($sql);

           }

           }


          
         
          
         // INSERT INTO bibi_car_brand_list_yiche_test (brand_name,baidu_brand_id,abbre,brand_url,is_hot,uv,saleStatus) VALUES('奥迪','9','A','http://image.bitautoimg.com/bt/car/default/images/logo/masterbrand/png/100/m_9_100.png','1','2794316','1') 
        
        //[0] => Array ( [masterId] => 9 [name] => 奥迪 [logoUrl] => http://image.bitautoimg.com/bt/car/default/images/logo/masterbrand/png/100/m_9_100.png [initial] => A [uv] => 2794316 [saleStatus] => 1 )
    }



    
    public function seriesAction(){

           exit;
           $sql="SELECT masterId FROM bibi_car_brand_list_yiche_test WHERE is_hot=1";
           $pdo = new PdoDb;
           $list = $pdo->query($sql);

           foreach($list as $key =>$value){
           $masterId=$value["masterId"];

           $url="http://carapi.ycapp.yiche.com/car/getseriallist?masterid=".$masterId."&allserial=true";
           $html=file_get_contents($url);
           $data=json_decode($html,true)['data'];

               foreach($data as $key =>$value){
                        $makename=$value['brandName'];
                       // $foreign=$value['foreign'];
                        if($value['foreign']){
                        	$foreign=1;
                        }else{
                            $foreign=2;
                        }
                       
                       foreach($value['serialList'] as $key =>$value){
                             // print_r($value["serialName"]);
                             
                             $exist=$this->is_exist('id','bibi_car_brand_series_yiche_test','brand_series_id',$value["serialId"]);
                             if(!$exist){
                              $sql = "INSERT INTO bibi_car_brand_series_yiche_test(brand_id,brand_series_name,brand_series_id,brand_series_url,makename,uv,saleStatus,foreigns,dealerPrice) VALUES("."'".$masterId."'".","."'".$value["serialName"]."'".","."'".$value["serialId"]."'".","."'".$value["Picture"]."'".","."'".$makename."'".","."'".$value["uv"]."'".","."'".$value["saleStatus"]."'".","."'".$foreign."'".","."'".$value["dealerPrice"]."'".")";
                              $pdo = new PdoDb;
                              $list = $pdo->query($sql);
                             
                              }



                       }


               }
          

           }
           //print_r($list);exit;
           //exit;
          // $this->is_exist();
           

    }
    
     public function getseAction(){


          exit;

         
           $masterId=8;

           $url="http://carapi.ycapp.yiche.com/car/getseriallist?masterid=".$masterId."&allserial=true";
           $html=file_get_contents($url);
           $data=json_decode($html,true)['data'];

               foreach($data as $key =>$value){
                        $makename=$value['brandName'];
                       // $foreign=$value['foreign'];
                        if($value['foreign']){
                          $foreign=1;
                        }else{
                            $foreign=2;
                        }
                       
                       foreach($value['serialList'] as $key =>$value){
                             // print_r($value["serialName"]);
                             
                             $exist=$this->is_exist('id','bibi_car_brand_series_yiche_test_copy','brand_series_id',$value["serialId"]);
                             if(!$exist){
                              $sql = "INSERT INTO bibi_car_brand_series_yiche_test_copy(brand_id,brand_series_name,brand_series_id,brand_series_url,makename,uv,saleStatus,foreigns,dealerPrice) VALUES("."'".$masterId."'".","."'".$value["serialName"]."'".","."'".$value["serialId"]."'".","."'".$value["Picture"]."'".","."'".$makename."'".","."'".$value["uv"]."'".","."'".$value["saleStatus"]."'".","."'".$foreign."'".","."'".$value["dealerPrice"]."'".")";
                              $pdo = new PdoDb;
                              $list = $pdo->query($sql);
                             
                              }



                       }


               }
          

           //print_r($list);exit;
           //exit;
          // $this->is_exist();
           

    }

    public function getmoAction(){
         exit;
         $sql="SELECT brand_series_id FROM bibi_car_brand_series_yiche_test_copy ";
           $pdo = new PdoDb;
           $list = $pdo->query($sql);
          
           foreach($list as $key => $value){
              $serialId=$value["brand_series_id"];

              $url="http://carapi.ycapp.yiche.com/car/GetCarListV61?csid=".$serialId."&cityId=502";
              $html=file_get_contents($url);
              $data=json_decode($html,true)['data'];
             
                foreach($data as $key =>$value){
                  if(!empty($value["CarGroup"]["CarList"])){
                         $Name=$value["CarGroup"]["Name"];
                      
                        foreach($value["CarGroup"]["CarList"]  as $key => $value){
                             $exist=$this->is_exist('id','bibi_car_series_model_yiche_test_copy','model_id',$value["CarId"]);
                             if(!$exist){
                              $str=$value["Year"]." ".$value["Name"];
                              $sql = "INSERT INTO bibi_car_series_model_yiche_test_copy(series_id,model_id,model_name,model_year,name) VALUES("."'".$serialId."'".","."'".$value["CarId"]."'".","."'".$str."'".","."'".$value["Year"]."'".","."'".$Name."'".")";
                              
                              $pdo = new PdoDb;
                              $list = $pdo->query($sql);
                             
                              }
                                
                        }
                     }


                }

              
           }




    }



    public function modelAction(){
        exit;
    	   $sql="SELECT brand_series_id FROM bibi_car_brand_series_yiche_test ";
           $pdo = new PdoDb;
           $list = $pdo->query($sql);
          
           foreach($list as $key => $value){
              $serialId=$value["brand_series_id"];

              $url="http://carapi.ycapp.yiche.com/car/GetCarListV61?csid=".$serialId."&cityId=502";
              $html=file_get_contents($url);
              $data=json_decode($html,true)['data'];
             
                foreach($data as $key =>$value){
                	if(!empty($value["CarGroup"]["CarList"])){
                         $Name=$value["CarGroup"]["Name"];
                	    
                        foreach($value["CarGroup"]["CarList"]  as $key => $value){
                             $exist=$this->is_exist('id','bibi_car_series_model_yiche_test','model_id',$value["CarId"]);
                             if(!$exist){
                              $str=$value["Year"]." ".$value["Name"];
                              $sql = "INSERT INTO bibi_car_series_model_yiche_test(series_id,model_id,model_name,model_year,name) VALUES("."'".$serialId."'".","."'".$value["CarId"]."'".","."'".$str."'".","."'".$value["Year"]."'".","."'".$Name."'".")";
                              
                              $pdo = new PdoDb;
                              $list = $pdo->query($sql);
                             
                              }
                                
                        }
                     }


                }

              
           }




    }

    public function is_exist($var,$table,$check,$value){
            $sql="SELECT ".$var." FROM ".$table." WHERE ".$check."="."'".$value."'";
            $pdo = new PdoDb;
            $list = $pdo->query($sql);
            return $list;
    }

   //批量
    public function getseriesAction (){
          exit;
           $sql="SELECT brand_series_id FROM bibi_car_brand_series ";
           $pdo = new PdoDb;
           $list = $pdo->query($sql);
           $year=['2002','2003','2004','2005','2006','2007','2008','2009','2010','2011','2012','2013','2014','2015','2016'];
           
           foreach($list as $key => $value){
              $serialId=$value["brand_series_id"];
                  foreach($year as $v){
                        $url="http://car.bitauto.com/AjaxNew/GetNoSaleSerailListByYear.ashx?csID=".$serialId."&year=".$v;
                        $html=file_get_contents($url);
                        $data=json_decode($html,true);
                        if($data){
                            foreach($data as $key =>$value){
                                
                                foreach($value["carList"] as $k =>$val){

                                              $exist=$this->is_exist('id','bibi_car_series_model_yiche_test','model_id',$val["CarID"]);
                                              if(!$exist){
                                                  $str=$v." ".$val["Name"];
                                                  $Name=$value["Engine_Exhaust"]."/".$value["MaxPower"]." ".$value["InhaleType"];
                                                  $sql = "INSERT INTO bibi_car_series_model_yiche_test_copy(series_id,model_id,model_name,model_year,name) VALUES("."'".$serialId."'".","."'".$val["CarID"]."'".","."'".$str."'".","."'".$v."'".","."'".$Name."'".")";
                                                  $pdo = new PdoDb;
                                                  $list = $pdo->query($sql);
                                              }
                                }
                        
                            }
                        }
                  
                  }
              
           }
      

    }
    //逐条
    public function getseriesbyoneAction (){
      exit;
         /*  $masterId=7;
           $sql="SELECT brand_series_id FROM bibi_car_brand_series WHERE brand_id=".$masterId;
           $pdo = new PdoDb;
           $list = $pdo->query($sql);
           */
           $list = ['3840','3354','2532','3088','4600','4254','3338'];
           $year =['2002','2003','2004','2005','2006','2007','2008','2009','2010','2011','2012','2013','2014','2015','2016'];
           
           foreach($list as $key => $value){
              $serialId=$value;
                  foreach($year as $v){
                        $url="http://car.bitauto.com/AjaxNew/GetNoSaleSerailListByYear.ashx?csID=".$serialId."&year=".$v;
                        $html=file_get_contents($url);
                        $data=json_decode($html,true);
                        if($data){
                            foreach($data as $key =>$value){
                                
                                foreach($value["carList"] as $k =>$val){

                                              $exist=$this->is_exist('id','bibi_car_series_model','model_id',$val["CarID"]);
                                              if(!$exist){
                                                  $str=$v." ".$val["Name"];
                                                  $Name=$value["Engine_Exhaust"]."/".$value["MaxPower"]." ".$value["InhaleType"];
                                                  $sql = "INSERT INTO bibi_car_series_model(series_id,model_id,model_name,model_year,name,is_stop) VALUES("."'".$serialId."'".","."'".$val["CarID"]."'".","."'".$str."'".","."'".$v."'".","."'".$Name."'".",1)";
                                                  $pdo = new PdoDb;
                                                  $list = $pdo->query($sql);
                                              }
                                }
                        
                            }
                        }
                  
                  }
              
           }
      

    }
    
   

    public function insertmodelAction () {
           exit;
           $sql="SELECT * FROM `bibi_car_series_model1_test`";
           $pdo = new PdoDb;
           $list = $pdo->query($sql);
         
          foreach($list as $key =>$value){
                 $exist=$this->is_exist('id','bibi_car_series_model','model_id',$value["model_id"]);
                 $is_stop=1;
                 if(!$exist){
                 $sql = "INSERT INTO bibi_car_series_model(series_id,model_id,model_name,model_year,name,is_stop) VALUES("."'".$value['series_id']."'".","."'".$value["model_id"]."'".","."'".$value['model_name']."'".","."'".$value['model_year']."'".","."'".$value['name']."'".","."'".$is_stop."'".")";
              
                 $list = $pdo->query($sql);
                 }
           }

    }


   

    public function modeldetailAction () {
          exit;
           $sql="SELECT model_id FROM `bibi_car_series_model_copy`";
           $pdo = new PdoDb;
           $list = $pdo->query($sql);
         
          foreach($list as $key =>$value){

                 $url="http://carapi.ycapp.yiche.com/Car/GetCarStylePropertys?carIds=".$value["model_id"];
                 $html=file_get_contents($url);
                 $data=json_decode($html,true);
                 
                 $arr=array();
                 $tablename="bibi_car_model_detail_copy";
                 $arr["model_id"]=$value["model_id"];
                 if(isset($data["data"][0]["CarReferPrice"])){
                   $arr["CarReferPrice"]=$data["data"][0]["CarReferPrice"];
                 }
                 
                if(isset($data["data"][0]["Car_RepairPolicy"])){
                    $arr["Car_RepairPolicy"]=$data["data"][0]["Car_RepairPolicy"];
                 }
                
                if(isset($data["data"][0]["Engine_ExhaustForFloat"])){
                   $arr["Engine_ExhaustForFloat"]=$data["data"][0]["Engine_ExhaustForFloat"];
                }

                if(isset($data["data"][0]["UnderPan_ForwardGearNum"])){
                   $arr["UnderPan_ForwardGearNum"]=$data["data"][0]["UnderPan_ForwardGearNum"];
                }

                if(isset($data["data"][0]["Perf_ZongHeYouHao"])){
                  $arr["Perf_ZongHeYouHao"]=$data["data"][0]["Perf_ZongHeYouHao"];
                }

                if(isset($data["data"][0]["Perf_AccelerateTime"])){
                   $arr["Perf_AccelerateTime"]=$data["data"][0]["Perf_AccelerateTime"];
                }

                if(isset($data["data"][0]["Perf_MaxSpeed"])){
                   $arr["Perf_MaxSpeed"]=$data["data"][0]["Perf_MaxSpeed"];
                }
               
               if(isset($data["data"][0]["Perf_SeatNum"])){
                  $arr["Perf_SeatNum"]=$data["data"][0]["Perf_SeatNum"];
               }
               
               if(isset($data["data"][0]["Perf_DriveType"])){
                $arr["Perf_DriveType"]=$data["data"][0]["Perf_DriveType"];
               }

               if(isset($data["data"][0]["Engine_Location"])){
                  $arr["Engine_Location"]=$data["data"][0]["Engine_Location"];
               }

               if(isset($data["data"][0]["Engine_Type"])){
                $arr["Engine_Type"]=$data["data"][0]["Engine_Type"];
               }

               if(isset($data["data"][0]["Engine_InhaleType"])){
                $arr["Engine_InhaleType"]=$data["data"][0]["Engine_InhaleType"];
               }
                
               if(isset($data["data"][0]["Engine_horsepower"])){
                $arr["Engine_horsepower"]=$data["data"][0]["Engine_horsepower"];
               }

               if(isset($data["data"][0]["Engine_MaxNJ"])){
                $arr["Engine_MaxNJ"]=$data["data"][0]["Engine_MaxNJ"];
               }
                 
               if(isset($data["data"][0]["Engine_EnvirStandard"])){
                 $arr["Engine_EnvirStandard"]=$data["data"][0]["Engine_EnvirStandard"];
               }
               if(isset($data["data"][0]["OutSet_Height"])){
                $arr["OutSet_Height"]=$data["data"][0]["OutSet_Height"];
               }

               if(isset($data["data"][0]["OutSet_Length"])){
                 $arr["OutSet_Length"]=$data["data"][0]["OutSet_Length"];
               }
               
               if(isset($data["data"][0]["OutSet_MinGapFromEarth"])){
                 $arr["OutSet_MinGapFromEarth"]=$data["data"][0]["OutSet_MinGapFromEarth"];
               }

               if(isset($data["data"][0]["OutSet_WheelBase"])){
                   $arr["OutSet_WheelBase"]=$data["data"][0]["OutSet_WheelBase"];
               }

               if(isset($data["data"][0]["OutSet_Width"])){
                 $arr["OutSet_Width"]=$data["data"][0]["OutSet_Width"];
               }
                 
               if(isset($data["data"][0]["Oil_FuelCapacity"])){
                $arr["Oil_FuelCapacity"]=$data["data"][0]["Oil_FuelCapacity"];
               }
               
               if(isset($data["data"][0]["Oil_FuelTab"])){
                $arr["Oil_FuelTab"]=$data["data"][0]["Oil_FuelTab"];
               }

               if(isset($data["data"][0]["Oil_FuelType"])){
                $arr["Oil_FuelType"]=$data["data"][0]["Oil_FuelType"];
               }

               if(isset($data["data"][0]["Oil_SupplyType"])){
                 $arr["Oil_SupplyType"]=$data["data"][0]["Oil_SupplyType"];
               }
                 

                 $exist=$this->is_exist('id','bibi_car_model_detail_copy','model_id',$value["model_id"]);
                 if(!$exist){
                 $pdo->insert($tablename,$arr);
                 }
              
           }

    }


         //通过品牌id获取车型
         public function getsebybranAction(){
          
           $masterId=7;

           $url="http://carapi.ycapp.yiche.com/car/getseriallist?masterid=".$masterId."&allserial=true";
           $html=file_get_contents($url);
           $data=json_decode($html,true)['data'];

               foreach($data as $key =>$value){
                        $makename=$value['brandName'];
                       // $foreign=$value['foreign'];
                        if($value['foreign']){
                          $foreign=1;
                        }else{
                            $foreign=2;
                        }
                       
                       foreach($value['serialList'] as $key =>$value){
                             // print_r($value["serialName"]);

                             $exist=$this->is_exist('id','bibi_car_brand_series','brand_series_id',$value["serialId"]);
                             if(!$exist){
                              $sql = "INSERT INTO bibi_car_brand_series(brand_id,brand_series_name,brand_series_id,brand_series_url,makename,uv,saleStatus,foreigns,dealerPrice) VALUES("."'".$masterId."'".","."'".$value["serialName"]."'".","."'".$value["serialId"]."'".","."'".$value["Picture"]."'".","."'".$makename."'".","."'".$value["uv"]."'".","."'".$value["saleStatus"]."'".","."'".$foreign."'".","."'".$value["dealerPrice"]."'".")";
                              $pdo = new PdoDb;
                              $list = $pdo->query($sql);
                             
                              }



                       }


               }
        
    }


     //通过车型获取系列
         public function getmobyseAction(){
          exit;
         $masterid=7;
         $sql="SELECT brand_series_id FROM bibi_car_brand_series WHERE brand_id=".$masterid;
           $pdo = new PdoDb;
           $list = $pdo->query($sql);
          
           foreach($list as $key => $value){
              $serialId=$value["brand_series_id"];

              $url="http://carapi.ycapp.yiche.com/car/GetCarListV61?csid=".$serialId."&cityId=502";
              $html=file_get_contents($url);
              $data=json_decode($html,true)['data'];
             
                foreach($data as $key =>$value){
                  if(!empty($value["CarGroup"]["CarList"])){
                         $Name=$value["CarGroup"]["Name"];
                      
                        foreach($value["CarGroup"]["CarList"]  as $key => $value){
                             $exist=$this->is_exist('id','bibi_car_series_model','model_id',$value["CarId"]);
                             if(!$exist){
                              $str=$value["Year"]." ".$value["Name"];
                              $sql = "INSERT INTO bibi_car_series_model(series_id,model_id,model_name,model_year,name) VALUES("."'".$serialId."'".","."'".$value["CarId"]."'".","."'".$str."'".","."'".$value["Year"]."'".","."'".$Name."'".")";
                              
                              $pdo = new PdoDb;
                              $list = $pdo->query($sql);
                             
                              }
                                
                        }
                     }


                }

              
           }

          

    }

    public function detail($list){
          
            $pdo = new PdoDb;
           foreach($list as $key =>$value){

                 $url="http://carapi.ycapp.yiche.com/Car/GetCarStylePropertys?carIds=".$value["model_id"];
                 $html=file_get_contents($url);
                 $data=json_decode($html,true);
                 
                 $arr=array();
                 $tablename="bibi_car_model_detail";
                 $arr["model_id"]=$value["model_id"];
                 if(isset($data["data"][0]["CarReferPrice"])){
                   $arr["CarReferPrice"]=$data["data"][0]["CarReferPrice"];
                 }
                 
                if(isset($data["data"][0]["Car_RepairPolicy"])){
                    $arr["Car_RepairPolicy"]=$data["data"][0]["Car_RepairPolicy"];
                 }
                
                if(isset($data["data"][0]["Engine_ExhaustForFloat"])){
                   $arr["Engine_ExhaustForFloat"]=$data["data"][0]["Engine_ExhaustForFloat"];
                }

                if(isset($data["data"][0]["UnderPan_ForwardGearNum"])){
                   $arr["UnderPan_ForwardGearNum"]=$data["data"][0]["UnderPan_ForwardGearNum"];
                }

                if(isset($data["data"][0]["Perf_ZongHeYouHao"])){
                  $arr["Perf_ZongHeYouHao"]=$data["data"][0]["Perf_ZongHeYouHao"];
                }

                if(isset($data["data"][0]["Perf_AccelerateTime"])){
                   $arr["Perf_AccelerateTime"]=$data["data"][0]["Perf_AccelerateTime"];
                }

                if(isset($data["data"][0]["Perf_MaxSpeed"])){
                   $arr["Perf_MaxSpeed"]=$data["data"][0]["Perf_MaxSpeed"];
                }
               
               if(isset($data["data"][0]["Perf_SeatNum"])){
                  $arr["Perf_SeatNum"]=$data["data"][0]["Perf_SeatNum"];
               }
               
               if(isset($data["data"][0]["Perf_DriveType"])){
                $arr["Perf_DriveType"]=$data["data"][0]["Perf_DriveType"];
               }

               if(isset($data["data"][0]["Engine_Location"])){
                  $arr["Engine_Location"]=$data["data"][0]["Engine_Location"];
               }

               if(isset($data["data"][0]["Engine_Type"])){
                $arr["Engine_Type"]=$data["data"][0]["Engine_Type"];
               }

               if(isset($data["data"][0]["Engine_InhaleType"])){
                $arr["Engine_InhaleType"]=$data["data"][0]["Engine_InhaleType"];
               }
                
               if(isset($data["data"][0]["Engine_horsepower"])){
                $arr["Engine_horsepower"]=$data["data"][0]["Engine_horsepower"];
               }

               if(isset($data["data"][0]["Engine_MaxNJ"])){
                $arr["Engine_MaxNJ"]=$data["data"][0]["Engine_MaxNJ"];
               }
                 
               if(isset($data["data"][0]["Engine_EnvirStandard"])){
                 $arr["Engine_EnvirStandard"]=$data["data"][0]["Engine_EnvirStandard"];
               }
               if(isset($data["data"][0]["OutSet_Height"])){
                $arr["OutSet_Height"]=$data["data"][0]["OutSet_Height"];
               }

               if(isset($data["data"][0]["OutSet_Length"])){
                 $arr["OutSet_Length"]=$data["data"][0]["OutSet_Length"];
               }
               
               if(isset($data["data"][0]["OutSet_MinGapFromEarth"])){
                 $arr["OutSet_MinGapFromEarth"]=$data["data"][0]["OutSet_MinGapFromEarth"];
               }

               if(isset($data["data"][0]["OutSet_WheelBase"])){
                   $arr["OutSet_WheelBase"]=$data["data"][0]["OutSet_WheelBase"];
               }

               if(isset($data["data"][0]["OutSet_Width"])){
                 $arr["OutSet_Width"]=$data["data"][0]["OutSet_Width"];
               }
                 
               if(isset($data["data"][0]["Oil_FuelCapacity"])){
                $arr["Oil_FuelCapacity"]=$data["data"][0]["Oil_FuelCapacity"];
               }
               
               if(isset($data["data"][0]["Oil_FuelTab"])){
                $arr["Oil_FuelTab"]=$data["data"][0]["Oil_FuelTab"];
               }

               if(isset($data["data"][0]["Oil_FuelType"])){
                $arr["Oil_FuelType"]=$data["data"][0]["Oil_FuelType"];
               }

               if(isset($data["data"][0]["Oil_SupplyType"])){
                 $arr["Oil_SupplyType"]=$data["data"][0]["Oil_SupplyType"];
               }
                 

                 $exist=$this->is_exist('id','bibi_car_model_detail','model_id',$value["model_id"]);
                 if(!$exist){
                 $pdo->insert($tablename,$arr);
                 }
              
           }

    }

    //获取车型详情
    public function getdetailAction(){
         exit;
         $masterId=7;
         $sql="SELECT brand_series_id FROM bibi_car_brand_series WHERE brand_id=".$masterId;
         $pdo = new PdoDb;
         $list = $pdo->query($sql);

         foreach($list as $key =>$value){
                $serialId=$value["brand_series_id"];
                $sql="SELECT model_id FROM bibi_car_series_model WHERE series_id=".$serialId;
                $pdo = new PdoDb;
                $list1 = $pdo->query($sql);
                $result=$this->detail($list1);
               
         }

    }
    
    //处理易车车型图片
    public function updateurlAction(){
       
        $sql="SELECT brand_series_url,brand_series_id FROM bibi_car_brand_series ";
        $pdo = new PdoDb;
        $list = $pdo->query($sql);
       
        foreach($list as $key =>$value){
               $url=str_replace("{0}", "1", $value["brand_series_url"]);
              
        $sql="UPDATE bibi_car_brand_series SET brand_series_url="."'".$url."'"."  WHERE brand_series_id=".$value["brand_series_id"];
        $list = $pdo->query($sql);
        }
         


    }

   //插入分类车型数据
    public function changeAction(){
        exit;
         $sql="SELECT brand_id,brand_name FROM bibi_car_brand_list WHERE is_hot=1";
          $pdo = new PdoDb;
         $list = $pdo->query($sql);
       
          foreach($list as $key =>$value){
                 $grade=2;
                 $father=29;
                 $avatar="http://car3.autoimg.cn/cardfs/product/g8/M08/67/7A/s_autohomecar__wKgH3lceA4uALp8tAAXK-FdTxgU704.jpg";
                 $exist=$this->is_exist('id','bibi_grade','from_id',$value["brand_id"]);
                 if(!$exist){
              
                 $sql = "INSERT INTO bibi_grade(grade,father_id,content,avatar,from_id) VALUES("."'".$grade."'".","."'".$father."'".","."'".$value['brand_name']."'".","."'".$avatar."'".","."'".$value['brand_id']."'".")";
              
                 $list = $pdo->query($sql);
                 }
           }


    }


    public function changeseAction(){
         exit;
         $sql="SELECT id,from_id FROM bibi_grade WHERE grade=2";
         $pdo = new PdoDb;
         $list = $pdo->query($sql);
        
          foreach($list as $key =>$value){

          $sql1="SELECT brand_id,brand_series_id,brand_series_name FROM bibi_car_brand_series WHERE brand_id=".$value["from_id"];
          $pdo = new PdoDb;
          $list1 = $pdo->query($sql1);

                 foreach($list1 as $k =>$val){
                  
                  $exist=$this->is_exist('id','bibi_grade','from_id',$val["brand_series_id"]);
                 if(!$exist){
                 $grade=3;
                 $father=$value["id"];
                 $avatar="http://img.bibicar.cn/avatar_default.jpg";
                 $sql = "INSERT INTO bibi_grade(grade,father_id,content,avatar,from_id) VALUES("."'".$grade."'".","."'".$father."'".","."'".$val['brand_series_name']."'".","."'".$avatar."'".","."'".$val['brand_series_id']."'".")";
                 $list = $pdo->query($sql);
                 }
          
            }
            /*
                 $grade=2;
                 $father=29;
                 $avatar="http://car3.autoimg.cn/cardfs/product/g8/M08/67/7A/s_autohomecar__wKgH3lceA4uALp8tAAXK-FdTxgU704.jpg";
                 $exist=$this->is_exist('id','bibi_grade','from_id',$value["brand_id"]);
                 if(!$exist){
              
                 $sql = "INSERT INTO bibi_grade(grade,father_id,content,avatar,from_id) VALUES("."'".$grade."'".","."'".$father."'".","."'".$value['brand_name']."'".","."'".$avatar."'".","."'".$value['brand_id']."'".")";
              
                 $list = $pdo->query($sql);
                 }
              */
           }


    }

    //分类地区处理
    public function changeareaAction(){
      exit;
         $sql="SELECT area_name,area_no FROM tb_area WHERE parent_no=0";
          $pdo = new PdoDb;
         $list = $pdo->query($sql);
        
         
          foreach($list as $key =>$value){
                 $grade=2;
                 $father=414;
                 $avatar="http://7xopqk.com1.z0.glb.clouddn.com/YmliaS1maWxlNTY1YzBhZGFjZmNkNA==";
                 $exist=$this->is_exist('id','bibi_grade','from_id',$value["area_no"]);
                 if(!$exist){
              
                 $sql = "INSERT INTO bibi_grade(grade,father_id,content,avatar,from_id) VALUES("."'".$grade."'".","."'".$father."'".","."'".$value['area_name']."'".","."'".$avatar."'".","."'".$value['area_no']."'".")";
              
                 $list = $pdo->query($sql);
                 }
           }


    }

     public function changecityAction(){
         exit;
         $sql="SELECT id,from_id FROM bibi_grade WHERE grade = 2 AND father_id=414";
         $pdo = new PdoDb;
         $list = $pdo->query($sql);
        
          foreach($list as $key =>$value){
        
          $sql1="SELECT area_name,area_no,parent_no FROM tb_area WHERE parent_no=".$value["from_id"];
          $pdo = new PdoDb;
          $list1 = $pdo->query($sql1);
         
                 foreach($list1 as $k =>$val){
                  
                  $exist=$this->is_exist('id','bibi_grade','from_id',$val["area_no"]);
                 if(!$exist){
                 $grade=3;
                 $father=$value["id"];
                 $avatar="http://7xopqk.com1.z0.glb.clouddn.com/YmliaS1maWxlNTY1YzBhZDZlYjgzMQ==";
                 $sql = "INSERT INTO bibi_grade(grade,father_id,content,avatar,from_id) VALUES("."'".$grade."'".","."'".$father."'".","."'".$val['area_name']."'".","."'".$avatar."'".","."'".$val['area_no']."'".")";
                 $list = $pdo->query($sql);
                 }
          
            }
            /*
                 $grade=2;
                 $father=29;
                 $avatar="http://car3.autoimg.cn/cardfs/product/g8/M08/67/7A/s_autohomecar__wKgH3lceA4uALp8tAAXK-FdTxgU704.jpg";
                 $exist=$this->is_exist('id','bibi_grade','from_id',$value["brand_id"]);
                 if(!$exist){
              
                 $sql = "INSERT INTO bibi_grade(grade,father_id,content,avatar,from_id) VALUES("."'".$grade."'".","."'".$father."'".","."'".$value['brand_name']."'".","."'".$avatar."'".","."'".$value['brand_id']."'".")";
              
                 $list = $pdo->query($sql);
                 }
              */
           }


    }


    public function changesortAction(){

           $pdo = new PdoDb;
           $sql="SELECT distinct user_id FROM bibi_feeds";
           $result=$pdo->query($sql);
          
           
           foreach($result as $key => $val){
                $pdo = new PdoDb;
                $val1=$val['user_id']*2;
                $sql1="UPDATE bibi_user_profile SET sort = ".$val1." WHERE user_id= ".$val['user_id']." AND sort=0";
                $pdo->query($sql1);
           }

          echo "dsafsda";

    } 
    
    
    



}