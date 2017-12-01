<?php
/**
 * 统计数据并保存excel
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/12/1
 * Time: 16:32
 */
include "Classes/PHPExcel.php";
include "Classes/PHPExcel/Writer/Excel2007.php";
include "Classes/PHPExcel/IOFactory.php";

$excel = new PHPExcel();


$mysqli = mysqli_connect("localhost", "root", 'root', 'Test');
mysqli_set_charset($mysqli,"utf-8");
$sql = "SELECT function_name , avg(exec_time) , max(exec_time) , min(exec_time) , count(exec_time) , count(exec_time) / (SELECT count(id) FROM statistics)  from statistics t1 GROUP BY function_name ";
$mysqli_result = mysqli_query($mysqli, $sql);
//  写表头
$excel->getActiveSheet()->setCellValue("A1","接口名称");
$excel->getActiveSheet()->setCellValue("B1","平均时间");
$excel->getActiveSheet()->setCellValue("C1","最大时间");
$excel->getActiveSheet()->setCellValue("D1","最小时间");
$excel->getActiveSheet()->setCellValue("E1","执行总次数");
$excel->getActiveSheet()->setCellValue("F1","执行次数比例");
$i = 2;
while ($row = $mysqli_result->fetch_array()){
    $excel->getActiveSheet()->setCellValue("A{$i}",$row[0]);
    $excel->getActiveSheet()->setCellValue("B{$i}",$row[1]);
    $excel->getActiveSheet()->setCellValue("C{$i}",$row[2]);
    $excel->getActiveSheet()->setCellValue("D{$i}",$row[3]);
    $excel->getActiveSheet()->setCellValue("E{$i}",$row[4]);
    $excel->getActiveSheet()->setCellValue("F{$i}",$row[5]*100 . "%");
    $i++;
}


//  保存excel

$writer = PHPExcel_IOFactory::createWriter($excel, "Excel2007");
$writer->save("./统计分析.xlsx");
echo "保存成功";



