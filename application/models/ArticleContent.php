<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class ArticleContentModel extends PdoDb
{


    public $article_id;
    public $content;
     


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_article_content';
    }

    public function saveProperties()
    {

       
        $this->properties['article_id'] = $this->article_id;
        $this->properties['content'] = $this->content;
    }


}