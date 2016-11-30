<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/6/18
 * Time: 01:23
 */
class DreamCarModel extends Model
{
    static public $table = 'bibi_dream_car';

    public function __construct(){
        parent::__construct(self::$table);
    }

}