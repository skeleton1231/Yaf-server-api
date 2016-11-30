<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/22
 * Time: 下午4:49
 * phares car json
 */



require '../vendor/autoload.php';

ini_set('max_execution_time', 0);
set_time_limit(0);


$db = new PDO('mysql:host=127.0.0.1;dbname=bibi',"root","123");

$sql = '
                 SELECT
                      hash,
                      board_time
                    FROM
                      `bibi_car_selling_list` 
                    WHERE
                    `car_type` = 1 
        ';
$data = $db->query($sql);

print_r($data);

exit;



