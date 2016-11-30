<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:30
 */


class UserSortModel extends PdoDb{

    static public $table = 'bibi_user_sort';

    public function __construct(){

        parent::__construct();
    }

    public function initProfile($data){

        $this->insert(self::$table , $data);
    }


    public function updateSortByKey($type="like",$type_id=0,$userId=0,$toId=0){
       
        switch ($type) {
            case 'like':
               $fromcode=5;
               $tocode=5;
                break;
             case 'comment': 
               $fromcode=10;
               $tocode=5;
                break;
             case 'follow': 
               $fromcode=5;
               $tocode=5;
                break;
             case 'articlecomment': 
               $fromcode=5;
               $tocode=5;
                break;
            default:
                $fromcode=5;
                $tocode=5;
                break;
        }
        
        
        $Key= 'usersort_'.$type.'_'.$type_id.'_'.$userId;
        Common::globalLogRecord('usersort key', $Key);
        $isLike = RedisDb::getValue($Key);
        
       if(!$isLike){
            RedisDb::setValue($Key,1);
            if($userId){
                $profileInfo['user_id'] = $userId;
                $profileInfo['code'] = $fromcode;
                $profileInfo['created'] = time();
                $profileInfo['type'] =$type;
                $profileInfo['type_id']=$type_id;
                $this->initProfile($profileInfo);
                $this->updatesort($userId,$fromcode);
            }
            if($toId){
                $Info['user_id'] = $toId;
                $Info['code'] = $fromcode;
                $Info['created'] = time();
                $Info['type'] =$type;
                $Info['type_id']=$type_id;
                $this->initProfile($Info);
                $this->updatesort($toId,$tocode);
            }

      }
        
    }


    public function updatesort($userId,$code){

        $condition ='sort = sort + '.$code;
        $sql = '
            UPDATE
            `bibi_user_profile`
            SET
            '.$condition.'
            WHERE
            `user_id` = '.$userId.'
            ;
        ';

        $this->exec($sql);
    }



}