<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class ApplyLoanModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_apply_loan';
    }

    public function saveProperties()
    {   
        
        $this->properties['carid'] = $this->carid;
        $this->properties['contact_name']   = $this->contact_name;
        $this->properties['mobile'] = $this->mobile;
        $this->properties['pay_scale'] = $this->pay_scale;
        $this->properties['pay_stages'] = $this->pay_stages;
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