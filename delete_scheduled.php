<?php
include 'connect.php';
if (isset($_GET['deletesched'])) {
    $id = $_GET['deletesched'];

    $sql = "delete from `pending_scheduled` where id=$id";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        //echo " Delete Successfull";
        header('location:Scheduled_Customers.php');
    } else {
        die(mysqli_error($conn));
    }
}
