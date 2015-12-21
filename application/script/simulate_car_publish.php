<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/16
 * Time: 下午9:21
 */


require '../vendor/autoload.php';
require '../configs/Constants.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$curl = new \Curl\Curl();

$domain = 'http://120.25.62.110';