<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午11:50
 */


class UserController extends ApiYafControllerAbstract
{

    public  function registerAction(){

        $this->required_fields = array_merge($this->required_fields, array('mobile','password','code'));

        $data = $this->get_request_data();

        if($data['code'] != RedisDb::getValue('code_'.$data['device_identifier'].'' )){

            $this->send_error(USER_CODE_ERROR);

        }

        unset($data['code']);

        $time = time();

        $data['login_ip']   = $_SERVER['REMOTE_ADDR'];
        $data['login_time'] = $time;
        $data['created']    = $time;
        $data['updated']    = $time;

        $name = 'bibi_' . Common::randomkeys(6);

        $data['username']   = $name;

        $userModel = new \UserModel;

        $user = $userModel->getInfoByMobile($data['mobile']);

        if($user){
            $this->send_error(USER_MOBILE_REGISTERED);
        }

        $device_identifier = $data['device_identifier'];
        unset($data['device_identifier']);

        $userId = $userModel->register($data);

        if(!$userId){

            $this->send_error(USER_REGISTER_FAIL);

        }

        $sessId = UserModel::setUserKeyCache($device_identifier , $userId);

        $profileModel = new \ProfileModel;
        $profileInfo = array();
        $profileInfo['user_id'] = $userId;
        $profileInfo['user_no'] = $name;
        $profile = $profileModel->initProfile($profileInfo);

        $profile = $profileModel->getProfile($userId);

        $profile['created'] = $time;

        $profile['session_id'] = $sessId;

        $this->send($profile);


    }


    public function sendCodeAction(){

        $this->required_fields = array_merge($this->required_fields, array('mobile'));

        $code = 6666;

        $data = $this->get_request_data();

        RedisDb::setValue('code_'.$data['device_identifier'].'' , $code);

        $response = array(
          'code'=>$code
        );

        $this->send($response);

    }

    public function loginAction(){

        $this->required_fields = array_merge($this->required_fields, array('mobile','password'));

        $data = $this->get_request_data();

        $user = new \UserModel;

        $info = $user->login($data['mobile'] , $data['password']);

        if(!$user){

            $this->send_error(USER_LOGIN_FAIL);
        }

        $userId = $info[0]['user_id'];
        $device_identifier = $data['device_identifier'];


        $sessId = UserModel::setUserKeyCache($device_identifier , $userId);

        $time = time();

        $profile = new \ProfileModel;

        $info = $profile->getProfile($userId);

        $info['session_id'] = $sessId;

        $this->send($info);

    }

    /*
     * @nickname
     * @birth
     * @signature
     * @user_no
     * @constellation
     */
    public function updateProfileAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','key','value','user_id'));

        $data = $this->get_request_data();

        $this->userAuth($data);

        $key = $data['key'];
        $value = $data['value'];
        $user_id = $data['user_id'];

        $profile = new ProfileModel();

        $update = array();
        $update[$key] = $value;

        switch($key){
            case 'nickname':
                $result = $profile->updateProfileByKey($user_id, $update);
                break;
            case 'birth':

                $date = explode('-' , $value);

                list($year , $month, $day) = $date;

                unset($update['birth']);
                $update['year']  = $year;
                $update['month'] = $month;
                $update['day']   = $day;

                $cons = Common::get_constellation($month, $day);

                $update['constellation'] = $cons;
                $update['age'] = Common::birthday($value);


                $result = $profile->updateProfileByKey($user_id, $update);

                break;
            case 'signature':

                $result = $profile->updateProfileByKey($user_id, $update);

                break;
            case 'user_no':

                $result = $profile->updateProfileByKey($user_id, $update);

                break;

        }


        if($result >= 0) {
            //$response = array();
            $response = $profile->getProfile($user_id);
            //print_r($response[0]);exit;
            $this->send($response);
        }
        else{
            $this->send_error(USER_PROFILE_UPDATE_FAIL);
        }

    }

}