<?php
/*
*
*更新bibi_no 
*
* 
 */ 
class ProfileController extends ApiYafControllerAbstract{
      
  exit;
      //
  public function updatebibiAction(){
     
        $db=new ProfileModel();
        $sql = '
                 SELECT
                      user_id
                    FROM
                      `bibi_user_profile` 
        ';
        $data = $db->query($sql);


      foreach($data as $key => $value){
        $val=$value['user_id']+10000;
         $sql = '
                 UPDATE
                    `bibi_user_profile` 
                    set `bibi_no`='.$val.'
                    WHERE
                    `user_id`='."'".$value['user_id']."'".'
        ';
         $db->query($sql);
     }

    }

}
