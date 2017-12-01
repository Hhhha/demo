<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/28
 * Time: 16:40
 */
include "validateCode.php";
session_start();
$validateCode = new ValidateCode();
$code = $validateCode->getcode();
$_SESSION['validateCode'] = $code;
$validateCode->outimg();



