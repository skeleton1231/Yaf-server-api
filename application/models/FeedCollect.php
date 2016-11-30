<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/6
 * Time: 上午12:08
 */

class FeedCollectModel extends PdoDb{

    public $user_id;
    public $feed_id;
    public $created;
    public $collect_id;

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_feeds_collect';
    }

    public function saveProperties(){

        $this->properties['feed_id'] = $this->feed_id;
        $this->properties['user_id']    = $this->user_id;
        $this->properties['created']    = $this->created;

    }


    public function getList(){


    }

    public function get(){
            
        $key = 'collect_'.$this->user_id.'_'.$this->feed_id.'';
        

        $collectId = RedisDb::getValue($key);


        if(!$collectId){

            $sql = 'SELECT
                  `collect_id`
                FROM
                  `bibi_feeds_collect`
                WHERE
                  `user_id` = '.$this->user_id.' AND `feed_id` = "'.$this->feed_id.'" ';


            $item = @$this->query($sql)[0];

            if($item){

                $collectId = $item['collect_id'];
                RedisDb::setValue($key, $collectId);

                return  $collectId;
            }
            else{

                RedisDb::setValue($key, 0);

                return 0;
            }

        }
        else{

            return  $collectId;
        }


    }

    public function delete(){

        $key = 'collect_'.$this->user_id.'_'.$this->feed_id.'';

        $this->deleteByPrimaryKey(FeedCollectModel::$table, array('collect_id'=>$this->collect_id));

        RedisDb::delValue($key);

    }


    



}


