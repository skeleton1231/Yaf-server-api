<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class HascheckcarModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_hascheck_car';
    }

    public function saveProperties()
    {
        $this->properties['user_id'] = $this->user_id;
        $this->properties['city'] = $this->city;
        $this->properties['hphm']   = $this->hphm;
        $this->properties['engineno'] = $this->engineno;
        $this->properties['classno'] = $this->classno;
        $this->properties['created'] = $this->created;

    }

    





}