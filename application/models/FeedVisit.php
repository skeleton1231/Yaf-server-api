<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/19
 * Time: 下午4:15
 */

class FeedVisitModel extends PdoDb {


    public $tableName = "bibi_feeds_visit";
    public $id;
    public $user_id;
    public $feed_id;
    public $created;

    public function __construct(){


        parent::__construct();

    }

    public function get(){
         
         $sql = 'SELECT
                  `id`
                FROM
                  '.$this->tableName.'
                WHERE
                  `user_id` = '.$this->user_id.' AND `feed_id` = "'.$this->feed_id.'" ';


         $item = @$this->query($sql)[0];

         $key = 'feedvisit_'.$this->user_id.'_'.$this->feed_id.'';
            if($item){
                
                $visitId = $item['id'];
                $result=RedisDb::setValue($key,$visitId);
               
                return $visitId;
            }
            else{

                RedisDb::setValue($key, 0);

                return 0;
            }
        
        

        $visitId = RedisDb::getValue($key);
          
        if(!$visitId){

            $sql = 'SELECT
                  `id`
                FROM
                  '.$this->tableName.'
                WHERE
                  `user_id` = '.$this->user_id.' AND `feed_id` = "'.$this->feed_id.'" ';


            $item = @$this->query($sql)[0];
           
            if($item){

                $visitId = $item['id'];
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