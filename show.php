<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/29
 * Time: 16:05
 */
include "User.php";

$user = new User();
$allUser = $user->getUser($_GET['page'],$_GET['limit']);
$data["count"] = count($allUser);
$data['msg'] = "";
$data['code'] = "0";
$data['data'] = $allUser;
echo json_encode($data);
