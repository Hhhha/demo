<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/28
 * Time: 15:56
 */
use demo\Config\Config;

include "Config.php";
class User
{
    private $result = ['result' => false, 'info' => ""];
    private $dbType = "mysql";
    private $host = "localhost";
    private $dbName = "Test";
    private $dbUser = "root";
    private $dbPassword = "root";
    private $dsn;
    private $pdo;
    private $checkType ;

    public $username;
    public $password;

    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect(Config::$rediIp);
        $struts= $this->redis->ping();
        if ($struts != "+PONG"){
            echo $struts;
        }

        session_start();
        $this->dsn = "{$this->dbType}:host={$this->host};dbname={$this->dbName}";
        $this->pdo = new PDO($this->dsn, $this->dbUser, $this->dbPassword);

        $this->username = !empty($_POST['username'])?htmlspecialchars($_POST['username']):false;
        $this->password = !empty($_POST['password'])?htmlspecialchars($_POST['password']):false;
//        echo "连接成功";
    }

    /**
     * @param String $type login/register
     * @return bool
     */
    public function checkForm(String $type)
    {

        $this->checkType =  $type;
        if ($type == "login"){
            $code = strtoupper($_POST['validateCode']); //  用户提交的验证码， 转换大写
            $this->checkValidateCode($code); //  检查验证码是否正确
        }elseif ($type == "register"){
            $this->checkUnique($this->username);       //  检查用户名是否唯一
        }
        $this->checkDataFormat();   //  检查格式是否正确

        return true;
    }

    /**
     * 检查数据格式是否正确 调用htmlspecialchars过滤
     */
    private function checkDataFormat(){
        $validateCode = true;
        if ($this->checkType == "login"){
            echo 123;
            $validateCode = !empty($_POST['validateCode'])?htmlspecialchars($_POST['validateCode']):false;
        }
        if ( !($this->username && $this->password && $validateCode) ){
            $this->result['info'] = "数据异常";
            echo json_encode($this->result);
            die;
        }
    }

    /**
     * @param $username
     */
    private function checkUnique($username){
        $sql = "select * from demo WHERE username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([":username" => $username]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)){
            $this->result['info'] = "用户名已存在";
            echo json_encode($this->result);
            die;
        }
    }

    /**
     * @param $code
     */
    private function checkValidateCode($code){
        $validateCode = $_SESSION['validateCode'];
        if ($code != $validateCode){
            $this->result['info'] = "验证码输入错误";
            echo json_encode($this->result);
            die;
        }
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function checkLogin($username,$password)
    {
        $sql = "SELECT * FROM `demo` WHERE `username` = :username AND `password` = :password";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':username'=>"$username",':password'=>"$password"));
        if (!$row = $stmt->fetch()){
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            return true;
        }else{

            //  验证码失败
            $this->result['info'] = "账户名或密码错误";
            echo json_encode($this->result);
            die;
        }

    }

    /**
     * mysql添加同时添加到redis中
     * @return bool
     */
    public function addUser()
    {

        $sql = "insert into demo (`username`,`password`)VALUE (:username,:password)";
        $stmt = $this->pdo->prepare($sql);
        $b = $stmt->execute([':username' => $this->username, ':password' => $this->password]);
        if (!$b){
            echo "<h1>添加失败{$stmt->errorInfo()}</h1>";
        }
        if ($stmt->rowCount()>0){
            //  查询数据添加到redis中
            $id = $this->pdo->lastInsertId();

            //  redis中没有数据就添加数据库
            $lLen = $this->redis->lLen("user");
            if (!($lLen >= 0)){
                $sql = "select * from demo";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $this->redis->rPush("user",$row);
                }
            }else{
                //  添加本条数据
                $sql = "select * from demo where id = $id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->redis->rPush("user",$row);
            }
            die;
            return true;
        }else{
            return false;
        }
    }

    /**
     * redis中没有数据会查询数据库
     * @param $page int 当前页面
     * @param $offset int  每页显示条数
     * @return array
     */
    public function getUser($page,$offset)
    {
        $limit = ($page-1) * $offset;
        $offset = $limit + $offset;


        //  redis中没有数据就添加数据库
        $lLen = $this->redis->lLen("user"); //  user长度
        if ($lLen <= 0){
            $sql = "select * from demo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $this->redis->rPush("user",json_encode($row));  //  单条数据存储
            }
        }



        if ($lLen<=0){
            $sql = "select * from demo LIMIT {$limit} , {$offset}";
            $stmt = $this->pdo->prepare($sql);
            $b = $stmt->execute();
            if (!$b){
                var_dump($stmt->errorInfo());
            }
            $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $all = [];
            //  取数据
            for ($i=0;$i<$lLen;$i++){
                $value = $this->redis->lIndex("user",$i);
                $all[] = json_decode($value,true);  //  单条json转换成php数组
            }
        }
        return $all;
    }

    /**
     * @param string username
     * @param string password
     * @param int id
     * @return array ['info'=>'信息','result'=>true/false]
     */
    public function postAjaxUpdateUser()
    {
        $result['result'] = true;
        $result['info'] = "修改成功";
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $id = htmlspecialchars($_POST['id']);
        $username = empty($username)?:$username;
        if (empty($username) && empty($password)){
            //  没有
            $result['result'] = false;
            $result['info'] = "数据格式不正确";
            return $result;
        }
        $sql = "update demo set username = :username,password = :password where id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([":username"=>$username,":password"=>$password,":id"=>$id]);
        $rowCount = $stmt->rowCount();
        if (!($rowCount > 0) ){
            //  修改失败
            $result['info'] = '修改失败';
            $result['result'] = false;
        }else{
            //  修改成功，操作redis中数据
            $this->redis->lTrim("user",1,0);    //  清空user所有数据

            //  添加redis 数据
            $lLen = $this->redis->lLen("user"); //  user长度
            if ($lLen <= 0){
                $sql = "select * from demo";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $this->redis->rPush("user",json_encode($row));  //  单条数据存储
                }
            }

        }
        return $result;

    }

    /**
     * @return array ['info'=>'信息','result'=>true/false]
     */
    public function postAjaxDeleteUser()
    {
        $result['result'] = true;
        $result['info'] = "修改成功";
        $id = htmlspecialchars($_POST['id']);
        $sql = "delete from demo where id = :id";
        $stmt = $this->pdo->prepare($sql);
        $b = $stmt->execute([':id'=>$id]);
        $rowCount = $stmt->rowCount();
        if ( !($rowCount> 0) ){
            //  删除失败
            $result['info'] = '删除失败';
            $result['result'] = false;
        }else{
            //  删除成功，事务操作redis
            $this->redis->multi(Redis::MULTI);
            $this->redis->lSet("user",$id-1,Config::$reidsDelValue);
            $this->redis->lRem("user",Config::$reidsDelValue,1);
            $this->redis->exec();
        }
        return $result;
    }

    public function findUserById($id)
    {
        $sql = "select * from demo where id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id'=>$id]);
        $first = $stmt->fetch(PDO::FETCH_ASSOC);
        return $first;
    }

    public function __destruct()
    {
        unset($this->pdo);
    }
}