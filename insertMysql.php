<?php
/**
 * 统计数据插入mysql
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/30
 * Time: 17:21
 */


//
//$r = fopen("php_error.txt",'r');
//$w = fopen("sql.txt", 'w');
//while (!feof($r)){
//    $str = fgets($r);
//    $strpos = strpos($str, ' AMF ');
//    if ($strpos !== false){
//        //  有AMF出现,写入sql.txt中
//        fwrite($w,$str);
//    }
//}
//fclose($r);
//fclose($w);
//
//unset($file);
//
//echo "<br/ >写入完毕";
use demo\Config\Config;

include "Config.php";
set_time_limit(0);
$mysqli = mysqli_connect("localhost",'root','root') or die("数据库连接失败");
mysqli_select_db($mysqli,'Test');
mysqli_set_charset($mysqli,"utf-8");

$startTime = time();
$r = fopen("sql.txt","r");
$redis = new Redis();
$redis->connect(Config::$rediIp);
while (!feof($r)){
    $str = fgets($r);
    $arr = explode( " ",$str);
    $sql = "insert into statistics VALUES (NULL ,'{$arr[0]} {$arr[1]}','{$arr[2]}','{$arr[3]}','{$arr[4]}',{$arr[5]})";
//    mysqli_query($mysqli,$sql) or  die("插入失败");
    $redis->rPush("demo",$str);
}
$endTime = time();
$time = $endTime-$startTime;
echo "开始时间:{$startTime}__结束时间：{$endTime}____耗时：{$time}";














