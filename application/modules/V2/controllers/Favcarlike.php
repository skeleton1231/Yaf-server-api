<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/11
 * Time: 下午12:21
 */

class FavcarlikeController extends ApiYafControllerAbstract {

    public function createAction(){
        
       $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'car_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
    
        $time = time();
       
        $FavcarlikeM = new FavcarlikeModel();
      
        $like = $FavcarlikeM->getLike($userId, $data['car_id']);

        if(!$like){
           $FavcarlikeM = new FavcarlikeModel();
           $FavcarlikeM->user_id = $userId;
           $FavcarlikeM->car_id = $data['car_id'];
           $FavcarlikeM->created = $time;
           $FavcarlikeM->saveProperties();
           $id = $FavcarlikeM->CreateM();
            if($id){

                $key = '_favcarlike_'.$data['car_id'].'_'.$userId.'';

                RedisDb::setValue($key,1);

                $like =  $FavcarlikeM->getLike($userId, $data['car_id']);

                $this->send($like);
            }


        }
        else{

            $this->send_error(FEED_HAS_LIKED);
        }

    }

    public function listAction(){
        
        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $FavcarlikeM = new FavcarlikeModel();
        $FavcarlikeM->currentUser = $userId;
        $likes = $FavcarlikeM->getLike(0,$data['car_id'],$data['page']);
       
        $this->send($likes);

    }






    


}