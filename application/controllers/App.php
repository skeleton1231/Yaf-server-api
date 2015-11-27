<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午1:00
 */


use Qiniu\Auth;
//use Qiniu\Storage;


class AppController extends ApiYafControllerAbstract {

    /*
     * @device_id
     * @device_resolution
     * @device_sys_version
     * @device_type
     * @device_identifier
     */
    public function registerAction(){

        $this->required_fields = array('device_id','device_resolution','device_sys_version','device_type');

        $data = $this->get_request_data();

        $data['device_identifier'] = $this->generateIdentifier($data);

        //查找是否有该DEVICE_IDENTIFIER
        $appModel = new \AppModel;

        $result = $appModel->getDevice($data['device_identifier']);


        if(!$result){

            $data['created'] = time();
            $data['updated'] = time();

            //$id = $this->db->insert('bibi_device_info' , $data);
            $id = $appModel->registerDevice($data);

            if(!$id){

                $this->send_error(APP_REGISTER_FAIL , STATUS_FAIL);
            }

        }

        $this->send($data);

    }

    public function generateIdentifier($data){

        $key = $data['device_id'] . $data['device_resolution'] . $data['device_sys_version'] . $data['device_type'];

        $identifier = md5(md5($key));

        return $identifier;

    }

    public function uploadTokenAction(){


        $this->required_fields = array('session_id','device_identifier');

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        // $token = 'b2uNBag0oxn1Kh1-3ZaX2I8PUl_o2r19RWerT3yI:7ybP6eSg1UWghOKsdYLFpUfdBWE=:eyJzY29wZSI6ImJpYmkiLCJkZWFkbGluZSI6MTQ0Njc0NzM2OH0=';
        $accessKey = QI_NIU_AK;
        $secretKey = QI_NIU_SK;

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = 'bibi';

        // 生成上传 Token
        //$token = $auth->uploadToken($bucket);

        $expire = 3600;

        $key = 'uploadToken_' . $userId;

        $policy = array(
            'callbackUrl' => 'http://120.25.62.110/index/callback',
            'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $userId . '}'
        );

        $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);

//        $uploadToken = RedisDb::getValue($key);
//
//        if(!$uploadToken){
//
//            $policy = array(
//                'callbackUrl' => 'http://120.25.62.110/index/callback',
//                'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $userId . '}'
//            );
//
//            $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);
//            RedisDb::setValue($key , $uploadToken);
//            RedisDb::getInstance()->expire($key,$expire);
//
//        }


        $response = array();
        $response['upload_token'] = $uploadToken;

        $this->send($response);


    }

    public function uploadAction(){

        $this->required_fields = array('session_id','device_identifier');

        $data = $this->get_request_data();

        $user_id = $this->userAuth($data);

        // $token = 'b2uNBag0oxn1Kh1-3ZaX2I8PUl_o2r19RWerT3yI:7ybP6eSg1UWghOKsdYLFpUfdBWE=:eyJzY29wZSI6ImJpYmkiLCJkZWFkbGluZSI6MTQ0Njc0NzM2OH0=';
        $accessKey = QI_NIU_AK;
        $secretKey = QI_NIU_SK;

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = 'bibi';

        // 生成上传 Token
        //$token = $auth->uploadToken($bucket);
        $expire = 3600;

        $key = 'uploadToken_' . $user_id;

        $policy = array(
            'callbackUrl' => 'http://120.25.62.110/index/callback',
            'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $user_id . '}'
        );

        $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);


        $items = array();

        if($_FILES){

            foreach($_FILES as $k => $file){

                $filePath = $file['tmp_name'];

                // 上传到七牛后保存的文件名
                $key = base64_encode(uniqid('bibi-file'));

                // 初始化 UploadManager 对象并进行文件的上传。
                $uploadMgr = new \Qiniu\Storage\UploadManager();

                list($ret, $err) = $uploadMgr->putFile($uploadToken, $key, $filePath);


                $hash = $ret['data']['hash'];

                if($err != null){

                    $this->send_error(QINIU_UPLOAD_ERROR);
                }

                $data = array();

//                $data['hash'] = $hash;
//                $data['url']  = IMAGE_DOMAIN . $hash;
//                $data['created'] = time();
//                $data['type'] = 1;
//                $data['user_id'] = $user_id;
//
//
//                $fm = new FileModel;
//                $id = $fm->Create($data);
//
//                $item = array();
//                $item['file_id'] = $data['hash'];
//                $item['file_url'] = $data['url'];
                $items[] = $hash;
            }

            echo implode(',', $items);

//            $response = array();
//            $response['files_id'] = implode();
//            $this->send($response);

        }
        else{

            $this->send_error(QINIU_UPLOAD_ERROR);

        }



    }

}
