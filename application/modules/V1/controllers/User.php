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

        $data = $this->get_request_data();

        if($data['code'] != $_SESSION['code']){

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

        $userId = $userModel->register($data);
        //$this->db->insert('bibi_user' , $data);

        if(!$userId){

            $this->send_error(USER_REGISTER_FAIL);

        }

        $profileModel = new \ProfileModel;
        $profileInfo = array();
        $profileInfo['user_id'] = $userId;
        $profileInfo['user_no'] = $name;
        $profile = $profileModel->initProfile($profileInfo);


        $response = array(

            'user_id'  => $userId,
            'username' => $name,
            'user_no'  => $name,
            'created'  => $time,
        );

        $this->send($response);


    }


    public function sendCodeAction(){

        $code = 6666;

        $data = $this->get_request_data();


        $_SESSION['code'] = $code;

        $response = array(
          'code'=>$code

        );
        $this->send($response);

    }

    public function loginAction(){

        $data = $this->get_request_data();

        $user = new \UserModel;


        $user = $user->login($data['mobile'] , $data['password']);

        if(!$user){

            $this->send_error(USER_LOGIN_FAIL);
        }

        $user_id = $user[0]['user_id'];

        $time = time();

        $profile = new \ProfileModel;

        $info = $profile->getProfile($user_id);

        $response = $info[0];

        $this->send($response);

    }

    /*
     * @nickname
     * @birth
     * @signature
     * @user_no
     * @constellation
     */
    public function updateProfileAction(){

        $data = $this->get_request_data();



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
            $this->send($response[0]);
        }
        else{
            $this->send_error(USER_PROFILE_UPDATE_FAIL);
        }

    }

}