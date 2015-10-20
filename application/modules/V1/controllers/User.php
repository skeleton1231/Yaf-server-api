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

        switch($key){
            case 'nickname':
                break;
            case 'birth':
                break;
            case 'signature':
                break;
            case 'user_no':
                break;
            case 'constellation':
                break;
        }

    }

}