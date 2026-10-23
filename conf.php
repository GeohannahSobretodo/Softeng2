<?php
include 'connect.php';
$t_id = $_POST['iid'];
$sql = "select * from paystat where t_id=$t_id";
$res = mysqli_query($conn,$sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="conf.css">
</head>
<body>
   <div id="cont">
    <div id="in">
 <form action="process.php" method="POST">
         <?php
    while($row = mysqli_fetch_assoc($res)){
     ?>   
     <p>
        <legend>
            <b>Name:</b><?php echo $row['name'] ?><br>
        </legend>
        <legend>
            <b>Contact:</b><?php echo $row['contact'] ?>
        </legend>
        
       <legend>
        <b>Amount to be paid:</b><?php echo $row['amount_due'] ?>
       </legend>
     </p>
     <input type="number" value=<?php echo $row['t_id'] ?> name="iid" hidden="true">
     <?php
    }
    ?>
        <input type="submit" value="confirm">
    </form>
    <p><a href="pay.php">Not your appointment?</a></p>
    </div>
   </div>
   
</body>
</html>
