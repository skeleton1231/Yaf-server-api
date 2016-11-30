<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class ShiwanController extends ApiYafControllerAbstract
{
  
  //排重接口
  public function checkAction(){
      $device_identifier=$this->getRequest()->getParam("idfa", 0);
      
      $sql = 'SELECT `id` FROM `bibi_device_info` WHERE `device_id` = "'.$device_identifier.'"';
      $pdo = new PdoDb();
      $id = $pdo->query($sql);
     
       if(empty($id)){
            $response = array (
            'status' => 0,
            );
        }else{
            $response = array (
            'status' => 1,
            );
        }
        $response = Common::arrToJson ( $response );
        Common::globalLogRecord ( 'success_res', $response );
        echo $response;

  }

  //点击请求
  public function clickAction(){

        $idfa=$this->getRequest()->getParam("idfa", 0);
        $callback=$this->getRequest()->getParam("callback", 0);

        if($idfa && $callback){
            $response = array (
                'status' => 1,
                );
            $key = 'shiwan_callback' . $idfa . '';
            RedisDb::setValue($key, $callback);
        }else{
            $response = array (
                'status' => 0,
            );
        }
        $response = Common::arrToJson ( $response );
        Common::globalLogRecord ( 'success_res', $response );
        echo $response;
    
  }
  //激活上报
  public function submitAction(){
    $idfa=$this->getRequest()->getParam("idfa", 0);
  }


 public static function arrToJson($arr) {
        $json = json_encode ( $arr );
        // $json = preg_replace('/\"(\d+)\"/', '$1', $json);
        $json = preg_replace('/\"(\d+)\.(\d+)"/', '$1.$2', $json);
        return $json;
    }
  
  public function testAction(){
    // $device_id=Common::shiwan($device_identifier);
     $device_id='397F28DA-7DE7-49DB-8A0D-BE38A2B0280A';
        if($device_id){
            $key = 'shiwan_callback' . $device_id . '';
            $callback = RedisDb::getValue($key);
            $url=urldecode($callback);
            print_r($url);
            $html = file_get_contents($url); 
            print_r($html);
        }
  }


  public function testtypeAction(){

    $file['key']='FldpPDwbX49DZAmcA3d9psl5EFgv';
    $info=Common::checktype( $file['key']);
    
    
  }






}
