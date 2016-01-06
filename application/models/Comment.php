<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:44
 */


class CommentModel extends PdoDb {


    public $comment_id;
    public $post_id;
    public $content;
    public $from_id;
    public $to_id;
    public $file_url = '';
    public $file_type = 1;
    public $created;

    public function __construct(){

        parent::__construct();
    }

    public function saveProperties(){

        $this->properties['comment_id'] = $this->comment_id;
        $this->properties['post_id']    = $this->post_id;
        $this->properties['content']    = $this->content;
        $this->properties['from_id']    = $this->from_id;
        $this->properties['to_id']      = $this->to_id;
        $this->properties['file_url']   = $this->file_url;
        $this->properties['file_type']  = $this->file_type;
        $this->properties['created']    = $this->created;
    }
}