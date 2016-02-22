<?php
$db_host='localhost:3306';
$db_database='ooyby';
$db_username='root';
$db_password='10IDCcom';
$connection=mysql_connect($db_host,$db_username,$db_password);//连接到数据库
mysql_query("set names 'utf8'");//编码转化
if(!$connection){
    die("could not connect to the database:</br>".mysql_error());//诊断连接错误

}
else
{
    echo ("congratulitions");
}
$db_selecct=mysql_select_db($db_database);//选择数据库
if(!$db_selecct)
{
    die("could not to the databases</br>".mysql_error());
}
$query="select * from xy_agent";//构建查询语句
$result=mysql_query($query);//执行查询
if(!$result)
{
    die("could not to the database</br>".mysql_error());

}
//	array mysql_fetch_row(resource $result);
while($result_row=mysql_fetch_row(($result)))//取出结果并显示
{
    $num=$result_row[0];
    $age=$result_row[1];
    $name=$result_row[2];
    echo "<tr>";
    echo "<td>$num</td>";
    echo "<td>$age</td>";
    echo "<td>$name</td>";
    echo "</tr>";
}


mysql_close($connection);//关闭连接
?>