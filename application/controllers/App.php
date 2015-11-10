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

        RedisDb::setValue('di_'.$data['device_identifier'].'', true);

        //查找是否有该DEVICE_IDENTIFIER
        $appModel = new \AppModel;

        $result = $appModel->getDevice($data['device_identifier']);

        if($result){

            $this->send_error(APP_REGISTER_EXIST , STATUS_FAIL);
        }

        $data['created'] = time();
        $data['updated'] = time();

        //$id = $this->db->insert('bibi_device_info' , $data);
        $id = $appModel->registerDevice($data);

        if($id){

            $this->send($data);
        }
        else{

            $this->send_error(APP_REGISTER_FAIL , STATUS_FAIL);

        }





    }

    public function generateIdentifier($data){

        $key = $data['device_id'] . $data['device_resolution'] . $data['device_sys_version'] . $data['device_type'];

        $identifier = md5(md5($key));

        return $identifier;

    }


    public function uploadAction(){

       // $token = 'b2uNBag0oxn1Kh1-3ZaX2I8PUl_o2r19RWerT3yI:7ybP6eSg1UWghOKsdYLFpUfdBWE=:eyJzY29wZSI6ImJpYmkiLCJkZWFkbGluZSI6MTQ0Njc0NzM2OH0=';
        $accessKey = QI_NIU_AK;
        $secretKey = QI_NIU_SK;

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = 'bibi';

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        //print_r($_FILES);exit;

        // 要上传文件的本地路径
        if($_FILES){

            foreach($_FILES as $k => $file){

                $filePath = $file['tmp_name'];

                // 上传到七牛后保存的文件名
                $key = 'test_'.time().'.png';

                // 初始化 UploadManager 对象并进行文件的上传。
                $uploadMgr = new \Qiniu\Storage\UploadManager();

                list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
                echo "\n====> putFile result: \n";
                if ($err !== null) {
                    var_dump($err);
                } else {
                    var_dump($ret);
                }

            }

        }


    }

}
