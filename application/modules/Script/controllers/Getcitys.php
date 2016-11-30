<?php
/*
* 覆盖carsellginlist表的board_time格式
* 
* 
*/
class GetcitysController extends ApiYafControllerAbstract
{
   
  
  public function getcityAction(){
        $pdo = new PdoDb;
        $wz  =new WeiZhang();
        $list=$wz->getCitys();
        $result=$list['result'];
        foreach($result as $key =>$value){
        $sql = "INSERT INTO bibi_zode_province(province_id,province,province_code) VALUES("."'".$key."'".","."'".$value["province"]."'".","."'".$value["province_code"]."'".")";
        $datt = $pdo->query($sql);
                foreach($value['citys'] as $k =>$val){
                    $sql1 = "INSERT INTO bibi_zode_citys(province_id,province,city_name,city_code,abbr,engineno,classno) VALUES("."'".$key."'".","."'".$value["province"]."'".","."'".$val["city_name"]."'".","."'".$val["city_code"]."'".","."'".$val["abbr"]."'".","."'".$val["engineno"]."'".","."'".$val["classno"]."'".")";   
                    $data = $pdo->query($sql1);

                }

        }




  }






}