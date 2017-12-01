<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/29
 * Time: 11:38
 */

include "User.php";


$user = new User();
if ($user->checkForm("register")){
    //  验证通过
    if ($user->addUser()){
        echo json_encode(['result'=>true,'info'=>"添加成功"]);
    }
}




