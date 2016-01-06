<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 上午11:13
 */

class PostModel extends PdoDb{


    static public $table = 'bibi_posts';

    public $post_id;
    public $post_content;
    public $user_id;
    public $comment_num;
    public $like_num;
    public $files;
    public $created;
    public $updated;

    public function __construct(){

        parent::__construct();
    }

    public function saveProperties(){

        $this->properties['post_id']        = $this->post_id;
        $this->properties['post_content']   = $this->post_content;
        $this->properties['user_id']        = $this->user_id;
        $this->properties['comment_num']    = $this->comment_num;
        $this->properties['like_num']       = $this->like_num;
        $this->properties['files']          = $this->files;
        $this->properties['created']        = $this->created;
        $this->properties['updated']        = $this->updated;


    }



}