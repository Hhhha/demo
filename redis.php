<?php
/** 测试redis
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/29
 * Time: 17:57
 */
use demo\Config\Config;

include "Config.php";

$redis = new Redis();
$redis->connect(Config::$rediIp);
echo "Server is running" . $redis->ping() . "<br />";
$lRange = $redis->lRange("user", 0, 100);
var_dump($lRange);