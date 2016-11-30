<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */
class FeedrelatedModel extends Model {

    static public $table = 'bibi_feed_related';

    public function __construct(){
        parent::__construct(self::$table);
    }

    public function savefeed($data){
        
         $feedM=new FeedrelatedModel;
         $option['user_id']=$data['user_id'];
         $option['feed_id']=$data['feed_id'];
         $options['user_id']=$data['user_id'];
         $options['feed_id']=$data['feed_id'];
         $list=$feedM->where($option)->find($options);
         
        if($list){
          foreach($list as $key =>$value){
            if($list[$key]){
              $data[$key]=$list[$key];
            }
          }
          $data['id']=$list['id'];

         $feedM->add($data,$options,true);
        }else{

         $feedM->add($data,$options,false);
        }
    }


    public function getFeeds($data){
       
        $feedM=new FeedrelatedModel;
        $options['feed_id']=$data['feed_id'];
       // $options['page']   =$data['page'];
        $sql='select feed_id,user_id from bibi_feed_related where feed_id='. $options['feed_id'];
        $lists=$feedM->query($sql);
        $arr=array();
        foreach($lists as $key =>$value){
            $profileM=new ProfileModel;
            $options=array();
            $profile=$profileM->getProfile($value['user_id']);
            $lists[$key]['avatar']=$profile['avatar'];
            $lists[$key]['nickname']=$profile['nickname'];
        }
        
        return $lists;
    }




}


