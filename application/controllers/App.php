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

        $data = $this->get_request_data();

        $data['device_identifier'] = $this->generateIdentifier($data);

        //查找是否有该DEVICE_IDENTIFIER

        $appModel = new \AppModel;
//
//        $sql = "SELECT id FROM `bibi_device_info` WHERE `device_identifier` = :device_identifier";
//
//        $result = $this->db->query($sql, array(':device_identifier'=

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
