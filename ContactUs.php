<?php
include 'connect.php';


if (isset($_POST['submit'])) {

    $FNAME = $_POST['FNAME'];
    $LNAME = $_POST['LNAME'];
    $PNUM = $_POST['PNUM'];
    $EMAIL = $_POST['EMAIL'];
    $INQUIRY = $_POST['INQUIRY'];


    $sql = "insert into inquiry (FNAME,LNAME,PNUM,EMAIL,INQUIRY) 
   values ('" . $FNAME . "','" . $LNAME . "','" . $PNUM . "','" . $EMAIL . "','" . $INQUIRY . "')";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        //echo "Data Inserted Successfully";
        //header('location:display.php');
        header('location:Homepage.php');
    } else {
        die(mysqli_error($conn));
    }
}

?>
<?php
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <title>Contact Us</title>
    <style>
        body {
            background-image: url(CONTACTUSBG.jpg);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin-left: 30px;
            overflow-x: hidden;
        }

        h1 {
            color: #1b7895;
            font-size: 52px;
            float: left;
            max-width: 500px;
            text-shadow: 2px 5px 3px rgb(221, 219, 219);
        }

        h4 {
            color: #fff;
        }

        .form-data {
            margin-top: 15%;
            height: 400px;
            background-color: rgba(190, 188, 188, 0.685);
            box-shadow: 0 0 5px rgb(39, 39, 39);
            border-radius: 20px;
            margin-left: 70px;
        }

        .form-body {
            padding: 15px 15px 15px 15px;
        }

        .form-row {
            margin: 10px;
        }

        .form-control {
            background-color: #f7f7ff;
            border-radius: 20px;
        }

        .form-control:hover {
            box-shadow: none;
            border: 2px solid #138496;
        }

        .btn-appointment {
            background-color: #138496 !important;
            width: 170px;
            border-radius: 20px;
        }

        .head {
            width: 500px;
        }

        @media screen and (max-width: 1152px) {
            .form-data {
                margin-top: 590px;
                margin-left: -320px;
            }
        }

        @media screen and (max-width: 767px) {
            .form-data {
                margin-top: 25px;
                margin-right: -300px;
                margin-left: 0px;
            }
        }

        @media screen and (max-width: 575px) {
            .form-data {
                margin-right: 0px;
            }

            .fname {
                width: 190px;
            }

            .lname {
                width: 190px;
                float: right;
                margin-top: -38px;
            }

            .num {
                width: 190px;
            }

            .email {
                width: 190px;
                float: right;
                margin-top: -35px;
            }
        }

        @media screen and (max-width: 517px) {
            .form-data {
                margin-right: 35px;
            }

            .fname {
                width: 155px;
            }

            .lname {
                width: 155px;
                float: right;
                margin-top: -38px;
            }

            .num {
                width: 155px;
            }

            .email {
                width: 155px;
                float: right;
                margin-top: -35px;
            }
        }
    </style>
</head>

<body>

    <div class='container-fluid mt-5'>
        <div class="row">
            <div class='col-md-1'>
            </div>
            <div class='col-md-3 pt-1 mt-4'>
                <div class="head">
                    <h1>Contact Us</h1><br><br><br>
                    <h4><i class="fa fa-phone mt-4" style="color:#0e959d; text-shadow: 1px 5px 2px rgb(240, 241, 241);"></i> 09066828232</h4>
                    <h4><i class="fa fa-envelope mt-4" style="color:#0e959d; text-shadow: 1px 5px 2px rgb(240, 241, 241);"></i> barengcleaning@gmail.com</h4>
                    <iframe class='mt-5' src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3865.762040236258!2d120.98000961524382!3d14.325250287604606!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d42bf6b752dd%3A0xd4cd94ab72d94a05!2sManhattan%2C%20Salawag%2C%20Dasmari%C3%B1as%2C%20Cavite!5e0!3m2!1sen!2sph!4v1639490540909!5m2!1sen!2sph" width="400" height="300" style="border:2;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
            <div class='col-md-1'>
            </div>


            <div class="col-sm-5 mt-4 ">
                <div class="form-data">
                    <form method="post">

                        <div class="form-body">
                            <div class="row form-row">
                                <div class="col-sm-6">
                                    <div class="fname">
                                        <input type="text" name="FNAME" placeholder="First Name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="lname">
                                        <input type="text" name="LNAME" placeholder="Last Name" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row form-row">
                                <div class="col-sm-6">
                                    <div class="num">
                                        <input type="text" name="PNUM" placeholder="Phone Number" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="email">
                                        <input type="text" name="EMAIL" placeholder="Email" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row form-row">
                                <div class="col-sm-12">
                                    <textarea type="text" name="INQUIRY" placeholder="Message" class="form-control" style="height: 200px;" required></textarea>
                                </div>
                            </div>

                            <div class="row form-row">
                                <div class="col-sm-8">
                                    <button type="submit" name="submit" class="btn btn-primary btn-appointment">
                                        INQUIRY
                                    </button>
                                </div>
                            </div>
                    </form>

                </div>


            </div>

</body>

</html>