<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午1:00
 */



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

}
