<?php
/** 反射操作
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/29
 * Time: 12:51
 */

include "User.php";

$class = new ReflectionClass("User");
$user = $class->newInstance();


$methods = $class->getMethods();
foreach ($methods as $method) {
    echo $method->getName()."<br />";
}
echo "<hr />";
$attributes = $class->getProperties();
foreach ($attributes as $attr){
    echo $attr->getName() . "<br />";
}



