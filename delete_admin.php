<?php
include 'connect.php';
if (isset($_GET['deleteaccount'])) {
    $id = $_GET['deleteaccount'];

    $sql = "delete from `admin` where id=$id";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        //echo " Delete Successfull";
        header('location:Admin Account.php');
    } else {
        die(mysqli_error($conn));
    }
}
