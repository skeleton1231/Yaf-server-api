<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/3
 * Time: 上午6:15
 */


class ApiRequest{

    public static $version = '/v1';

    public static $domain = 'http://120.25.62.110';

    public static $appRegisterUrl = "/app/register";

    public static $userRegister = "/user/register";

    public static $userSendCode = "/user/sendcode";

    public static $userUpdateAll = "/user/updateall";

    public static $carCreate     = "/car/create";

    public static $publisCarCreate = "/publishcar/create";

    public $url;
    public $curl;
    public $post;
    public $faker;
    public $pdo;

    public $city_id = 93;


    public $device_id;
    public $device_resolution = '414*736';
    public $device_sys_version = '9.1';
    public $device_type = 1;
    public $device_identifier = '';
    public $session_id = '';

    public $mobile;
    public $password = 'e10adc3949ba59abbe56e057f20f883e';
    public $code = '6666';

    public $nickname = '';
    public $avatar = '';
    public $gender = 1;
    public $signature = '';
    public $birth = '';


    public $brand_id;
    public $series_id;
    public $model_id;
    public $car_no;
    public $vin_no;
    public $vin_file = 'Ft3nagsZAaGtMAPfQUoX92sCk3lj';

    //二手车字段
    public $price;
    public $mileage;
    public $car_color=1;
    public $car_status=1;
    public $contact_name;
    public $contact_address;
    public $maintain=1;
    public $is_transfer=2;
    public $insurance_due_time;
    public $check_expiration_time;
    public $exchange_time=0;
    public $car_intro;
    public $action = 1;

    public $car_id;


    public $files_id = array();
    public $files_type = '';



    public function getAppRegisterUrl(){

        return self::$domain . self::$appRegisterUrl;
    }

    public function getUserRegisterUrl(){

        return self::$domain . self::$version . self::$userRegister;
    }

    public function getUserSendCodeUrl(){

        return self::$domain . self::$version . self::$userSendCode;
    }

    public function getUserUpdateAllUrl(){

        return self::$domain . self::$version . self::$userUpdateAll;

    }

    public function getPublishCarCreateUrl(){

        return self::$domain . self::$version . self::$publisCarCreate;

    }



    private function setPostFields($fields){

        $this->post = array();

        foreach($fields as $k => $field){

            $this->post[$field] = $this->$field;
        }

        return $this->post;
    }


    public function getCarCreateUrl(){

        return self::$domain . self::$version . self::$carCreate;
    }

    public function getCurlInstance(){

        if(!$this->curl){

            $this->curl = new \Curl\Curl();
        }

        return $this->curl;
    }

    public function getFakerInstance(){

        if(!$this->faker){

            $this->faker = @Faker\Factory::create();

        }

        return $this->faker;
    }

    public function getPdoInstance(){

        if(!$this->pdo){

            $this->pdo = new PdoDb();
        }

        return $this->pdo;
    }

    public function getAvatar(){

        $avatarStr = 'YmliaS1maWxlNTY1YzBhZDZlYjgzMQ==,YmliaS1maWxlNTY1YzBhZDc2NWU0MQ==,YmliaS1maWxlNTY1YzBhZDdjZThjOA==,YmliaS1maWxlNTY1YzBhZDgxYTVjYg==,YmliaS1maWxlNTY1YzBhZDg4MGViOQ==,YmliaS1maWxlNTY1YzBhZDkzMzkzZA==,YmliaS1maWxlNTY1YzBhZDlhYjQyNw==,YmliaS1maWxlNTY1YzBhZGE0NDUwMg==,YmliaS1maWxlNTY1YzBhZGFjZmNkNA==,YmliaS1maWxlNTY1YzBhZGI0MWFjMg==,YmliaS1maWxlNTY1YzBhZGJkMmJjZA==';

        $avatars = explode(',', $avatarStr);

        $total = count($avatars);

        $num = rand(0, $total);

        return $avatars[$num];
    }

    public function request($postFields){

        $curl = $this->getCurlInstance();

        $this->setPostFields($postFields);


//        print_r($this->url);
//        echo "<br />";
//
//        print_r($this->post);
//        echo "<br />";
//
//
//        exit;


        $response = $curl->post($this->url,$this->post);

        return $response->data;

    }

    public function appRegister(){
        $postFields = array('device_id','device_resolution','device_sys_version','device_type');
        $this->url = $this->getAppRegisterUrl();
        $this->device_id = uniqid('test') . rand(0000,9999);

        $data = $this->request($postFields);

        $this->device_identifier = $data->device_identifier;
        
    }

    public function userSendCode(){

        $postFields = array('device_identifier' , 'mobile');

        $this->url  = $this->getUserSendCodeUrl();

        $faker = $this->getFakerInstance();

        $this->mobile = $faker->phoneNumber;

        $this->request($postFields);

    }


    public function userRegister(){

        $postFields = array('device_identifier','mobile','password','code','nickname');

        $this->url  = $this->getUserRegisterUrl();

        $faker = $this->getFakerInstance();

        //$this->mobile = $faker->phoneNumber;
        $this->nickname = $faker->lastName;

        $data = $this->request($postFields);

        $this->session_id = $data->session_id;

    }

    public function userUpdateAll(){

        $postFields = array('device_identifier','session_id','gender','nickname','birth','avatar','signature');

        $this->url  = $this->getUserUpdateAllUrl();

        $this->getFakerInstance();
//
//        //$this->nickname = $faker->name;
        $this->gender = rand(1,2);

        $this->birth = $this->faker->date();

        $this->signature = $this->faker->email;

        $this->avatar = $this->getAvatar();

        $this->request($postFields);

    }


    public function carCreate(){

        $postFields = array(
            'device_identifier',
            'session_id',
            'brand_id',
            'series_id',
            'model_id',
            'city_id',
            'car_no',
            'vin_no',
            'vin_file',
            'files_id',
            'files_type',
        );

        $this->url  = $this->getCarCreateUrl();


        $pdo = $this->getPdoInstance();

        $rand = rand(1, 999);

        $sql = "
                SELECT
	              *
                FROM
	            `bibi`.`bibi_car_selling_list`
                WHERE
	            `car_type` = '2'
                AND `files` <> ''
                AND `brand_id` != 0
                AND `series_id` != 0
                LIMIT {$rand}, 1
                ";

        $car = $pdo->query($sql)[0];

        $files = unserialize($car['files']);

        $this->files_id = array();

        foreach($files as $k => $file){

            if($k < 4){

                $this->files_id[] = $file['key'];

            }
        }

        $this->files_id = json_encode($this->files_id);

        $this->brand_id = $car['brand_id'];
        $this->series_id = $car['series_id'];

        $sql = "SELECT
	              `model_id`
                FROM
	              `bibi`.`bibi_car_series_model`
                WHERE
	              `series_id` = '{$this->series_id}'
                ORDER BY `model_year` DESC , RAND()
                LIMIT 0 , 1";

        $this->model_id = $pdo->query($sql)[0]['model_id'];

        $this->car_no = '粤B' . Common::randomkeys(5);

        $this->vin_no = uniqid('vin_no') . uniqid();

        $this->vin_file = 'Ft3nagsZAaGtMAPfQUoX92sCk3lj';

        $this->files_type = '[1,2,3,4]';

        $this->request($postFields);


    }

    public function publishCarCreate(){

        $postFields = array(
            'device_identifier',
            'session_id',
            'brand_id',
            'series_id',
            'model_id',
            'price',
            'board_time',
            'mileage',
            'car_status',
            'city_id',
            'car_color',
            'car_no',
            'vin_no',
            'contact_name',
            'contact_address',
            'maintain',
            'is_transfer',
            'insurance_due_time',
            'check_expiration_time',
            'files_id',
            'exchange_time',
            'car_intro',
            'vin_file',
            'files_type',
            'action',
        );

        $this->url  = $this->getPublishCarCreateUrl();


        $pdo = $this->getPdoInstance();

        $rand = rand(1, 999);

        $sql = "
                SELECT
	              *
                FROM
	            `bibi`.`bibi_car_selling_list`
                WHERE
	            `car_type` = '2'
                AND `files` <> ''
                AND `brand_id` != 0
                AND `series_id` != 0
                LIMIT {$rand}, 1
                ";

        $car = $pdo->query($sql)[0];

        $files = unserialize($car['files']);

        $this->files_id = array();

        foreach($files as $k => $file){

            if($k < 11){

                $this->files_id[] = $file['key'];

            }
        }

        $this->files_id = json_encode($this->files_id);

        $this->brand_id = $car['brand_id'];
        $this->series_id = $car['series_id'];

        $sql = "SELECT
	              `model_id`
                FROM
	              `bibi`.`bibi_car_series_model`
                WHERE
	              `series_id` = '{$this->series_id}'
                ORDER BY `model_year` DESC , RAND()
                LIMIT 0 , 1";

        $this->model_id = $pdo->query($sql)[0]['model_id'];

        $this->car_no = '粤B' . Common::randomkeys(5);

        $this->vin_no = uniqid('vin_no') . uniqid();

        $this->vin_file = 'Ft3nagsZAaGtMAPfQUoX92sCk3lj';

        $this->files_type = '[1,2,3,4,5,6,7,8,9,10]';

        $this->city_id = 93;

        $this->price = $car['price'] - 3;

        $month = rand(1, 12);
        $day   = rand(1, 28);
        $this->board_time = $car['board_time'] . '-' . $month . '-' . $day;

        $this->mileage = rand(10000, 30000);

        $this->car_status = 1;

        $this->car_color = 1;

        $name_array = array('黄','陈','刘','周','李','何','吴','张');

        $this->contact_name = $name_array[array_rand($name_array)]. '先生';

        $strict = array('福田区','南山区','宝安区','罗湖区');

        $this->contact_address = '深圳市' . $strict[array_rand($strict)];

        $this->maintain = 1;

        $this->is_transfer = 1;

        $this->insurance_due_time = ($car['board_time'] + 1) . '-' . $month . '-' . $day;;

        $this->check_expiration_time = ($car['board_time'] + 1) . '-' . $month . '-' . $day;;

        $this->exchange_time = 0;

        $this->car_intro = '希望和你做成交易';

        $this->action = 1;

        $this->request($postFields);


    }


}