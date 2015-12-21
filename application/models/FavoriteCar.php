<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/6
 * Time: 上午12:08
 */

class FavoriteCarModel extends PdoDb{

    public $user_id;
    public $car_id;
    public $created;
    public $favorite_id;

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_favorite_car';
    }


    public function getList(){




    }

    public function get(){

        $key = 'favorite_'.$this->user_id.'_'.$this->car_id.'';


        $favId = RedisDb::getValue($key);


        if(!$favId){

            $sql = 'SELECT
                  `favorite_id`
                FROM
                  `bibi_favorite_car`
                WHERE
                  `user_id` = '.$this->user_id.' AND `car_id` = "'.$this->car_id.'" ';


            $item = @$this->query($sql)[0];

            if($item){

                $favId = $item['favorite_id'];
                RedisDb::setValue($key,$favId);

                return $favId;
            }
            else{

                RedisDb::setValue($key, 0);

                return 0;
            }

        }
        else{

            return $favId;
        }


    }

    public function delete(){

        $key = 'favorite_'.$this->user_id.'_'.$this->car_id.'';

        $this->deleteByPrimaryKey(FavoriteCarModel::$table, array('favorite_id'=>$this->favorite_id));

        RedisDb::delValue($key);

    }




}


