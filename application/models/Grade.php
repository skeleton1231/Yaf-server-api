<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/11
 * Time: 上午10:58
 */

class GradeModel extends PdoDb{

    static public  $tableBrand  = 'bibi_grade';




    public function __construct(){

        parent::__construct();

//        foreach($items as $key => $item){
//
//            $this->$key = $item;
//        }
    }

    public function getBrandInfo(){

        $sql = 'SELECT
                CONCAT(t1.brand_name, t2.brand_series_name, t3.model_name) AS car_name,
                t1.brand_id,
                t1.brand_name,
                t2.brand_series_id,
                t2.brand_series_name AS `series_name`,
                t3.model_id,
                t3.model_name
                FROM
                `'.self::$tableBrand.'` AS t1 LEFT JOIN `'.self::$tableSeries.'` AS t2
                ON t1.brand_id = t2.brand_id
                LEFT JOIN `'.self::$tableModel.'` AS t3
                ON t2.brand_series_id = t3.series_id
                WHERE
                t1.brand_id = '.$this->brand_id.'
                AND t2.brand_series_id = '.$this->series_id.'
                AND t3.model_id = '.$this->model_id.'
                ';

        $info = $this->query($sql);

        return isset($info[0]) ? $info[0] : array();
    }

    public function getGradeTooneModel(){


            $sql = 'SELECT `grade`, `content`, `id`, `avatar`,`father_id` FROM `bibi_car_brand_list` WHERE `grade` = 1 ';

            $gradeM = $this->query($sql);

            if(isset($gradeM[0])){

                return $gradeM[0];
            }
            else{
                return new stdClass;
            }



    }

    public function getGradeTotwoModel($fatherId){


            $sql = 'SELECT `grade`, `content`, `id`, `avatar`,`father_id` FROM `bibi_car_brand_list` WHERE `grade` =2  AND `father_id`="'.$fatherId.'"';

            $gradeM = $this->query($sql);

            if(isset($gradeM[0])){

                return $gradeM[0];
            }
            else{
                return new stdClass;
            }


    }

    public function  getGradeTothreeModel($fatherId)
    {


            $sql = 'SELECT `grade`, `content`, `id`, `avatar`,`father_id` FROM `bibi_car_brand_list` WHERE `grade` =3  AND `father_id`="'.$fatherId.'"';

            $gradeM = $this->query($sql);

            if(isset($gradeM[0])){

                return $gradeM[0];
            }
            else{
                return new stdClass;
            }



    }

    public function getModelDetail($modelId)
    {


            $sql = 'SELECT * FROM `bibi_car_model_detail` WHERE  `model_id`='.$modelId.' ';

            $model = $this->query($sql);

            if(isset($model[0])){

                $info = $model[0];

               // $name = explode(' ', $info['model_name']);

               // $info['model_name'] = $name[0] . ' ' . $name[1] . ' ' . $name[2];

                return $info;
            }
            else{

                return new stdClass();
            }



    }




}