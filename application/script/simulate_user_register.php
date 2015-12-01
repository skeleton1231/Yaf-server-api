<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/30
 * Time: 下午4:17
 */

require '../vendor/autoload.php';
require '../configs/Constants.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$curl = new \Curl\Curl();

$domain = 'http://120.25.62.110';

$urls = array(
    '/app/register',
    '/v1/user/sendcode',
    '/v1/user/register',
    '/app/upload',
    '/v1/user/updateall',
);

$avatarStr = 'YmliaS1maWxlNTY1YzBhZDZlYjgzMQ==,YmliaS1maWxlNTY1YzBhZDc2NWU0MQ==,YmliaS1maWxlNTY1YzBhZDdjZThjOA==,YmliaS1maWxlNTY1YzBhZDgxYTVjYg==,YmliaS1maWxlNTY1YzBhZDg4MGViOQ==,YmliaS1maWxlNTY1YzBhZDkzMzkzZA==,YmliaS1maWxlNTY1YzBhZDlhYjQyNw==,YmliaS1maWxlNTY1YzBhZGE0NDUwMg==,YmliaS1maWxlNTY1YzBhZGFjZmNkNA==,YmliaS1maWxlNTY1YzBhZGI0MWFjMg==,YmliaS1maWxlNTY1YzBhZGJkMmJjZA==';

$avatars = explode(',', $avatarStr);

for($i = 1; $i<15; $i++){

    $faker = Faker\Factory::create();

    //获取device_identifier
    $url = $domain . $urls[0];

    $data = array(
        'device_id'=>'3126D87B-EBD0-4D00-BA0F-33FEF6528D0B'.$i.'',
        'device_resolution'=>'414*736',
        'device_sys_version'=>'ios9.1',
        'device_type'=>1
    );

    $response = $curl->post($url, $data);

    $response = $response['data'];


    $device_identifier = $response['device_identifier'];

    $curl = $domain . $urls[1];

    $data = array(
        'device_identifier'=>$device_identifier,
        'mobile'           =>$faker->phoneNumber,
    );

    $curl->post($url, $data);


    $data = array(



    );



}

$faker = Faker\Factory::create();

echo $faker->name;
echo "\n";
echo $faker->phoneNumber(false);

exit;

