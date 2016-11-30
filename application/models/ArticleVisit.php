<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/19
 * Time: 下午4:15
 */

class ArticleVisitModel extends PdoDb {


    public $tableName = "bibi_article_visit";
    public $id;
    public $user_id;
    public $article_id;
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
                  `user_id` = '.$this->user_id.' AND `article_id` = "'.$this->article_id.'" ';


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
        
        $key = 'articlevisit_'.$this->user_id.'_'.$this->article_id.'';

        $visitId = RedisDb::getValue($key);
          


        if(!$visitId){

            $sql = 'SELECT
                  `id`
                FROM
                  '.$this->tableName.'
                WHERE
                  `user_id` = '.$this->user_id.' AND `article_id` = "'.$this->article_id.'" ';


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