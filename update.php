<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/30
 * Time: 16:10
 */

include "User.php";
$user = new User();
if (empty($_POST)){
    //  不是post访问
    $id = $_GET['id'];
    $data = $user->findUserById($id);
    include "update.html";
}else{
    $result = $user->postAjaxUpdateUser();
    if ($result['result'] === true){
        $icon = 6;
    }else{
        $icon = 5;
    }
    $a = <<<ABCD
        <script type="text/javascript"> 
        //当你在iframe页面关闭自身时
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.tt.reload();
        parent.layer.msg("{$result['info']}",{icon:{$icon}})
        parent.layer.close(index); //再执行关闭   
        </script>
ABCD;
    echo $a;

}





