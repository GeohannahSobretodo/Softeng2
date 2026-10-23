<?php
$conn = new mysqli("localhost","root","","crud");
if($conn->connect_error){
    die("connection failed".$conn->connect_error);
}
$t_id = $_POST['iid'];
$sql = "update paystat set pay_stat ='paid' where t_id=$t_id";
$res = mysqli_query($conn,$sql);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="process.css">
</head>
<body>
<div id="cont">
    <div id="in">
    <h1>PAYMENT SUCCESSFUL!!!</h1>
    </div>
    
</div>
    
</body>
</html>