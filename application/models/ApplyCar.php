<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 * note  汽车定制
 */
class ApplyCarModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_apply_car';
    }

    public function saveProperties()
    {   
        
        $this->properties['info'] = $this->info;
        $this->properties['created']    =$this->created;

    }
    

     public function getloan($mobile,$contact_name,$carid){


            $sql = 'SELECT `id` FROM `bibi_car_apply_loan` WHERE `mobile` = "'.$mobile.'" AND `carid` = "'.$carid.'" ';

            $loanM = $this->query($sql);

            if(isset($loanM[0])){

                return $loanM[0];
            }
            



    }
    





}