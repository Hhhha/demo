<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/28
 * Time: 15:44
 */
include "User.php";


$user = new User();
$check = $user->checkForm("login");
if ($check){
    $login = $user->checkLogin($user->username,$user->password);
    if ($login){
        echo "登录成功";
    }
}





