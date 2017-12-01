<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/30
 * Time: 14:31
 */
include 'User.php';


$user = new User();
$result = $user->postAjaxDeleteUser();

echo json_encode($result);




