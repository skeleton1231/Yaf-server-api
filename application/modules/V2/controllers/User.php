<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午11:50
 */
class UserController extends ApiYafControllerAbstract
{



    public function registerAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile', 'password', 'code', 'nickname'));

        $data = $this->get_request_data();


        //unset($data['code']);
        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);
//
//        if($code != $data['code']){
//
//            $this->send_error(USER_CODE_ERROR);
//        }

        unset($data['code']);

        $time = time();

        $data['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['login_time'] = $time;
        $data['created'] = $time;
        $data['updated'] = $time;

        $name = 'bibi_' . Common::randomkeys(6);

        $data['username'] = $name;

        $nickname = $data['nickname'];
        unset($data['nickname']);

        $len = strlen($nickname);

        if ($len < 4 || $len > 30) {

            $this->send_error(USER_NICKNAME_FORMAT_ERROR);

        }


        unset($data['nickname']);

        $userModel = new \UserModel;

        $user = $userModel->getInfoByMobile($data['mobile']);

        if ($user) {
            $this->send_error(USER_MOBILE_REGISTERED);
        }

        $device_identifier = $data['device_identifier'];
        unset($data['device_identifier']);

        $userId = $userModel->register($data);

        if (!$userId) {

            $this->send_error(USER_REGISTER_FAIL);

        }

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);


        $profileModel = new \ProfileModel;
        $profileInfo = array();
        $profileInfo['user_id'] = $userId;
        $profileInfo['user_no'] = $name;
        $profileInfo['nickname'] = $nickname;
        $profileInfo['avatar']   = AVATAR_DEFAULT;


        $profileModel->initProfile($profileInfo);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);


        $response = array();
        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);


    }


    public function sendCodeAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile'));

        $code = rand(1000,9999);

        $data = $this->get_request_data();

        $key = 'code_' . $data['mobile'] . '';

        RedisDb::setValue($key, $code);

        RedisDb::getInstance()->expire($key, 60);

        $response = array(
            'code' => $code
        );

        Common::sendSMS($data['mobile'],array($code),"74511");

        $this->send($response);

    }

    public function loginAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile', 'password'));

        $data = $this->get_request_data();

        $user = new \UserModel;

        $info = $user->login($data['mobile'], $data['password']);

        if (!$info) {

            $this->send_error(USER_LOGIN_FAIL);
        }

        $userId = $info['user_id'];
        $device_identifier = $data['device_identifier'];



        $response = array();

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        //删除sessionId
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $time = time();

        $profile = new \ProfileModel;

        $info['profile'] = $profile->getProfile($userId);
        $response['session_id'] = $sessId;
        $response['user_info'] = $info;

        $nickname = $info['profile']['nickname'];
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);

    }

    /*
     * @nickname
     * @birth
     * @signature
     * @user_no
     * @constellationUSER_PROFILE_UPDATE_FAIL
     *
     */
    public function updateProfileAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'key', 'value'));

        $data = $this->get_request_data();

        $user_id = $this->userAuth($data);

        $profileKey = array('nickname', 'birth', 'avatar', 'gender', 'signature');

        $key = $data['key'];

        if (!in_array($key, $profileKey)) {

            $this->send_error(USER_PROFILE_KEY_ERROR);

        }

        $value = $data['value'];
        $profile = new ProfileModel();

        $update = array();
        $update[$key] = $value;


        switch ($key) {

            case 'nickname':
                // $result = $profile->updateProfileByKey($user_id, $update);
                break;
            case 'birth':

                $date = explode('-', $value);

                list($year, $month, $day) = $date;

                unset($update['birth']);
                $update['year'] = $year;
                $update['month'] = $month;
                $update['day'] = $day;

                $cons = Common::get_constellation($month, $day);

                $update['constellation'] = $cons;
                $update['age'] = Common::birthday($value);

                break;
//            case 'signature':
//
//                break;

            case 'avatar':

                $file = new FileModel();
                $fileUrl = $file->Get($data['value']);
                $update['avatar'] = $fileUrl;

                break;
//
//            case 'gender':
//                break;

        }

        $result = $profile->updateProfileByKey($user_id, $update);


        if ($result >= 0) {

            $userM = new UserModel();
            $userInfo = $userM->getInfoById($user_id);
            $userInfo['profile'] = $profile->getProfile($user_id);
            $response['user_info'] = $userInfo;
            $this->send($response);
        } else {
            $this->send_error(USER_PROFILE_UPDATE_FAIL);
        }

    }

    public function updateAllAction()
    {

        $this->optional_fields = array('nickname', 'birth', 'avatar', 'gender', 'signature');

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $user_id = $this->userAuth($data);

        $update = array();

        foreach ($data as $k => $pk) {

            if (!in_array($k, $this->optional_fields)) {

                continue;
            }



            switch ($k) {

                case 'birth':

                    if ($data['birth']) {

                        $birth = $data['birth'];
                        $date = explode('-', $birth);

                        if (is_array($date)) {

                            list($year, $month, $day) = $date;

                            $update['year'] = $year;
                            $update['month'] = $month;
                            $update['day'] = $day;

                            $cons = Common::get_constellation($month, $day);

                            $update['constellation'] = $cons;
                            $update['age'] = Common::birthday($birth);
                        }
                    }


                    break;

                case 'avatar':

                    if($data['avatar']){

                        $file = new FileModel();
                        $fileUrl = $file->Get($data['avatar']);
                        $update['avatar'] = $fileUrl;
                    }

                    break;

                case 'gender':
                    $update['gender'] = $data['gender'] ? $data['gender'] : 0;
                    break;

                case 'nickname':

                    if($data['nickname']){

                        $update['nickname'] = $data['nickname'];

                    }

                    break;

                case 'signature':

                    if($data['signature']){

                        $update['signature'] = $data['signature'];

                    }

                    break;
//
//                default:
//
//                    $update[$k] = $data[$k];
//
//                    break;


            }

        }

        $profile = new ProfileModel();

        $profile->updateProfileByKey($user_id, $update);

        $userM = new UserModel();

        $userInfo = $userM->getProfileInfoById($user_id);

        $response = array();
        $response['user_info'] = $userInfo;

        $this->send($response);

    }


    public function profileAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $userM = new UserModel();
        $userInfo = $userM->getInfoById($userId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile($userId);

        $userInfo['profile'] = $profile;

        $response['user_info'] = $userInfo;

        $car = new CarSellingModel();

        $response['car_info'] = $car->getUserCar($userId);

        $friendShipM = new FriendShipModel();

        $friendShipM->currentUser = $userId;

        $response['friend_num'] = $friendShipM->friendNumCnt();

        $response['fans_num']   = $friendShipM->fansNumCnt();

        $this->send($response);

    }

    public function homepageAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $otherId = $this->getAccessId($data, $userId);

        $userM = new UserModel();
        $userInfo = $userM->getInfoById($otherId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile($otherId);

        $userInfo['profile'] = $profile;

        $response['user_info'] = $userInfo;

        $car = new CarSellingModel();

        $response['car_info'] = $car->getUserCar($otherId);

        $friendShipM = new FriendShipModel();

        $friendShipM->currentUser = $otherId;

        $response['friend_num'] = $friendShipM->friendNumCnt();

        $response['fans_num']   = $friendShipM->fansNumCnt();

        $friendShip = $friendShipM->getMyFriendShip($userId, $otherId);

        $response['is_friend'] = isset($friendShip['user_id']) ? 1 : 2;

        $feedM = new FeedModel();

        $response['feed_num'] = $feedM->getPublishedFeedTotal($data['user_id']);

//        $publishCar = $car->getUserPublishCar($otherId);
//
//        foreach($publishCar['car_list'] as $k => $car){
//
//            $response['publish_car_list'][] = $car['car_info'];
//        }

       // $response['publish_car_list'] = $publishCar['car_list'];

        $this->send($response);


    }

    public function oauthloginAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('wx_open_id','weibo_open_id','nickname','avatar')
        );

        $data = $this->get_request_data();

        $user = new \UserModel;

        $wx_open_id = $data['wx_open_id'];
        $weibo_open_id = $data['weibo_open_id'];



        $oauth['wx_open_id'] =  preg_match("/[A-Za-z0-9]+/", $wx_open_id) ? $wx_open_id : '';
        $oauth['weibo_open_id'] = preg_match("/[A-Za-z0-9]+/", $weibo_open_id) ? $weibo_open_id : '';


        $info = $user->loginByOauth($oauth);

        if (!$info) {

            $this->send_error(USER_OAUTH_UPDATE_PROFILE);
        }

        $userId = $info['user_id'];
        $device_identifier = $data['device_identifier'];

        $response = array();

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        //删除sessionId
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $time = time();

        $profile = new \ProfileModel;

        $update['nickname'] = $data['nickname'];
        $update['avatar']   = $data['avatar'];
        $profile->updateProfileByKey($userId,$update);

        $info['profile'] = $profile->getProfile($userId);
        $response['session_id'] = $sessId;
        $response['user_info'] = $info;

        $nickname = $info['profile']['nickname'];
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);


        $this->send($response);
    }


    public function oauthregisterAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('mobile', 'password', 'code', 'nickname','avatar','wx_open_id','weibo_open_id')
        );

        $data = $this->get_request_data();

        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

//        if($code != $data['code']){
//
//            $this->send_error(USER_CODE_ERROR);
//        }

        unset($data['code']);

        $time = time();

        $nickname = $data['nickname'];

        $avatar = $data['avatar'];

        $userModel = new \UserModel;
        $profileModel = new \ProfileModel;

        $info = $userModel->getInfoByMobile($data['mobile']);


        if($info){

            $userId = $info[0]['user_id'];

            $update['password'] = $data['password'];

            if($data['wx_open_id']){

                $update['wx_open_id'] = $data['wx_open_id'];
            }

            if( $data['weibo_open_id']){

                $update['weibo_open_id'] = $data['weibo_open_id'];

            }


            $update['updated'] = $time;

            $userModel->update(array('user_id'=>$userId),$update);

            $updateProfile['nickname'] = $data['nickname'];
            $updateProfile['avatar']   = $data['avatar'];

            $profileModel->updateProfileByKey($userId, $updateProfile);

        }
        else{

            $insert = array();
            $insert['login_ip'] = $_SERVER['REMOTE_ADDR'];
            $insert['login_time'] = $time;
            $insert['created'] = $time;
            $insert['updated'] = $time;

            $name = 'bibi_' . Common::randomkeys(6);

            $insert['username'] = $name;
            $insert['wx_open_id'] = $data['wx_open_id'];
            $insert['weibo_open_id'] = $data['weibo_open_id'];
            $insert['mobile'] = $data['mobile'];
            $insert['password'] = $data['password'];

            $userId = $userModel->register($insert);

            $profileInfo = array();
            $profileInfo['user_id'] = $userId;
            $profileInfo['user_no'] = $name;
            $profileInfo['nickname'] = $nickname;
            $profileInfo['avatar']   = $avatar;

            $profileModel->initProfile($profileInfo);
        }

        $device_identifier = $data['device_identifier'];
        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);


        $response = array();
        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);


        $this->send($response);


    }

    public function chattokenAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $profileModel = new ProfileModel();

        $profile = $profileModel->getProfile($userId);


        $chatToken = $this->getRcloudToken($userId, $profile['nickname'],$profile['avatar']);

        $response['chat_token'] = $chatToken;

        $this->send($response);
    }

    public function searchAction(){
       
        
        $this->required_fields = array_merge($this->required_fields, array('session_id','nickname','page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
        

        $nickname = $data['nickname'];

        $page = $data['page'];
        
         $userModel = new Model('bibi_user_profile');
         $userModel = new ProfileModel();
    /*    
        $sqlCnt = 'SELECT
                  COUNT(*) AS count FROM `bibi_user_profile` AS t2
                  LEFT JOIN `bibi_user` AS t1 ON t1.user_id = t2.user_id
                  WHERE t2.`nickname` LIKE "%'.$nickname.'%"';

        $count = $userModel->query($sqlCnt)[0]['count'];
    */ 
        $pageSize = 10;
        $number = ($page - 1) * $pageSize;

        $sql = 'SELECT 
                  t2.user_id,t2.nickname,t2.avatar FROM `bibi_user_profile` AS t2 
                  LEFT JOIN `bibi_user` AS t1 ON t1.user_id = t2.user_id
                  WHERE t2.`nickname` LIKE "%'.$nickname.'%" LIMIT ' . $number . ' , ' . $pageSize . '';
       
        $users = $userModel->query($sql);
       
        $response['list'] = $users;
       
        $this->send($response);

    }


}