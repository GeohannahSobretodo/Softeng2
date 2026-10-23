<?php
include 'connect.php';
include 'session.php';
$email = $_SESSION['email'];
$id = $_SESSION['id'];

if (isset($_POST['submit'])) {

  $services = $_POST['services'];
  $cusname = $_POST['cusname'];
  $cusemail = $_POST['cusemail'];
  $cuscontact = $_POST['cuscontact'];
  $cusdate = $_POST['cusdate'];
  $cusadd = $_POST['cusadd'];
  $cuscity = $_POST['cuscity'];
  $cusprov = $_POST['cusprov'];
  $Inquiry = $_POST['Inquiry'];

  $sql = "insert into pending (services,cusname,cusemail,cusnum,cusdate,cusadd,cuscity,cusprov,Inquiry) 
   values ('" . $services . "','" . $cusname . "','" . $cusemail . "','" . $cuscontact . "','" . $cusdate . "','" . $cusadd . "','" . $cuscity . "','" . $cusprov . "','" . $Inquiry . "')";
  $result = mysqli_query($conn, $sql);
  if ($result) {
    //echo "Data Inserted Successfully";
    //header('location:display.php');
    header('location:Pending.php');
  } else {
    die(mysqli_error($conn));
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pending</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
</head>

<body class="hold-transition sidebar-mini sidebar-collapse">

  <div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">

      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
      </ul>

      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="logout.php" role="button" style="color: red;">
            <p> Log out <i class="ion ion-log-out"></i></p>
          </a>
          <div class="navbar-search-block">
        </li>
      </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color:#0e386e;">

      <a href="#" class="brand-link">
        <img src="headerbareng.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light">BCS | Admin </span>
      </a>

      <div class="sidebar">

        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-item">
              <a href="Dashboard.php" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="Scheduled_Customers.php" class="nav-link">
                <i class="nav-icon fas fa-table"></i>
                <p>
                  Scheduled Customer
                </p>
              </a>
            </li>

            <li class="nav-item">
              <a href="Pending.php" class="nav-link">
                <i class="nav-icon fas fa-edit"></i>
                <p>
                  Pending
                </p>
              </a>
            </li>

            <li class="nav-item">
              <a href="Inquiry.php" class="nav-link">
                <i class="nav-icon fas fa-users "></i>
                <p>
                  Inquiry
                </p>
              </a>
            </li>

            <li class="nav-item">
              <a href="Admin Account.php" class="nav-link">
                <i class="nav-icon fas fa-user"></i>
                <p>
                  Admin Account
                </p>
              </a>
            </li>

          </ul>
        </nav>
      </div>
    </aside>

    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Scheduling Customer</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="Dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="Pending.php">Pending Customer </a></li>
                <li class="breadcrumb-item active"> View </li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">

        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">




                <div class="container my-5">
                  <form method="post">


                    <div class="form-row">
                      <div class="col">
                        <label> Enter Full Name: </label>
                        <input type="text" class="form-control" name="cusname" placeholder="Surname, Firstname, M.I" autocomplete="off" required>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col">
                        <label> Enter Email: </label>
                        <input type="text" class="form-control" name="cusemail" placeholder="Email Address" autocomplete="off" required>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col">
                        <label> Enter Contact Number</label>
                        <input type="tel" class="form-control" name="cuscontact" placeholder="contact no." autocomplete="off" required>
                      </div>

                      <div class="col">
                        <label>Desired Schedule Date: </label>
                        <input type="date" class="form-control" name="cusdate" autocomplete="off" required>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col">
                        <label> Address: </label>
                        <input type="text" class="form-control" name="cusadd" placeholder="Lot, Blk, Street, Village" autocomplete="off" required>
                      </div>

                      <div class="col">
                        <label> Enter City</label>
                        <input type="text" class="form-control" name="cuscity" placeholder="City" autocomplete="off" required>
                      </div>

                      <div class="col">
                        <label>Enter Province</label>
                        <input type="text" class="form-control" name="cusprov" placeholder="Province" autocomplete="off">
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col">
                        <label> Select Services</label>
                        <select name="services" class="form-control">
                          <option value="" selected disabled> --Select--</option>
                          <option value="House_Premium_Cleaning"> House Premium Cleaning</option>
                          <option value="Office_Premium_Cleaning"> Office Premium Cleaning</option>
                          <option value="Sofa_Deep_Cleaning"> Sofa Deep Cleaning</option>
                          <option value="Mattress_Deep_Cleaning"> Mattress Deep Cleaning</option>
                          <option value="Disinfection_Services "> Disinfection Services </option>
                        </select>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col">
                        <label> Specific service: </label>
                        <textarea class="form-control" name="Inquiry" placeholder="" autocomplete="off" row="3"></textarea><br>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Submit</button>
                    <button class="btn btn-danger"><a href="Pending.php" class="text-light">Back</a></button>
                  </form>
                </div>

              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <footer class="main-footer">
      <strong>Copyright &copy; 2023 <a href="#">BCS</a>.</strong> All rights reserved.
    </footer>

    <aside class="control-sidebar control-sidebar-dark">
    </aside>
  </div>

  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <script src="dist/js/adminlte.min.js"></script>
</body>

</html>