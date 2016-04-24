<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/4/24
 * Time: 23:34
 */

require '../vendor/autoload.php';

ini_set('max_execution_time', 0);
set_time_limit(0);


$db = new PDO('mysql:host=127.0.0.1;dbname=bibi',"root","123456");
$db->query('set names utf8');


