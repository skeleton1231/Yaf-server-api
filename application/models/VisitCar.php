<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/19
 * Time: 下午4:15
 */

class VisitCarModel extends PdoDb {


    public $tableName = "bibi_visit_car";
    public $id;
    public $user_id;
    public $car_id;
    public $created;

    public function __construct(){


        parent::__construct();

    }

    public function get(){

        $key = 'visit_'.$this->user_id.'_'.$this->car_id.'';


        $visitId = RedisDb::getValue($key);


        if(!$visitId){

            $sql = 'SELECT
                  `visit_id`
                FROM
                  '.$this->tableName.'
                WHERE
                  `user_id` = '.$this->user_id.' AND `car_id` = "'.$this->car_id.'" ';


            $item = @$this->query($sql)[0];

            if($item){

                $visitId = $item['visit_id'];
                RedisDb::setValue($key,$visitId);

                return $visitId;
            }
            else{

                RedisDb::setValue($key, 0);

                return 0;
            }

        }
        else{

            return $visitId;
        }


    }




} 