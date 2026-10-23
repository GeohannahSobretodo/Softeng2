<?php
include 'connect.php';
if (isset($_GET['deleteid'])) {
    $id = $_GET['deleteid'];

    $sql = "delete from `pending` where id=$id";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        //echo " Delete Successfull";
        header('location:Pending.php');
    } else {
        die(mysqli_error($conn));
    }
}
