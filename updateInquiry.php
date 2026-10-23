<?php
include 'connect.php';
include 'session.php';
$email = $_SESSION['email'];
$id = $_SESSION['id'];

$id = $_GET['updateidinq'];
$sql = "select * from pending where id=$id";
$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);
$services = $row['services'];
$cusname = $row['cusname'];
$cusemail = $row['cusemail'];
$cusnum = $row['cusnum'];
$cusdate = $row['cusdate'];
$cusadd = $row['cusadd'];
$cuscity = $row['cuscity'];
$cusprov = $row['cusprov'];
$Inquiry = $row['Inquiry'];


if (isset($_POST['submit'])) {

  $services = $_POST['services'];
  $cusname = $_POST['cusname'];
  $cusemail = $_POST['cusemail'];
  $cusnum = $_POST['cusnum'];
  $cusdate = $_POST['cusdate'];
  $cusadd = $_POST['cusadd'];
  $cuscity = $_POST['cuscity'];
  $cusprov = $_POST['cusprov'];
  $Inquiry = $_POST['Inquiry'];


  $sql = "update pending set id='" . $id . "',services='" . $services . "',cusname='" . $cusname . "',cusemail='" . $cusemail . "',cusnum='" . $cusnum . "',cusdate='" . $cusdate . "',cusadd='" . $cusadd . "',cuscity='" . $cuscity . "',cusprov='" . $cusprov . "',Inquiry='" . $Inquiry . "' where id=$id";
  $result = mysqli_query($conn, $sql);
  if ($result) {
    //echo "Updated Successfully";
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
  <title>Update Details</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap4.min.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body class="hold-transition sidebar-mini sidebar-collapse">

  <div class="wrapper">

    <nav class="main-header navbar navbar-expand">

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
        <span class="brand-text font-weight-light">BCS | Admin</span>
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
              <h1>Update Pending</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="Dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="Admin Account.php">Admin Accounts </a></li>
                <li class="breadcrumb-item">Update </a></li>
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
                <div class="container my-3">
                  <form method="post">
                    <div class="form-row">
                      <div class="col">
                        <label> Update Name: </label>
                        <input type="text" class="form-control" name="cusname" autocomplete="off" value=<?php echo $cusname; ?>>
                      </div>
                    </div>

                    <div class="form-row">
                      <div class="col">
                        <label> Update Email: </label>
                        <input type="text" class="form-control" name="cusemail" autocomplete="off" value=<?php echo $cusemail; ?>>
                      </div>
                    </div>

                    <div class="form-row">
                      <div class="col">
                        <label> Update Contact No. :</label>
                        <input type="text" class="form-control" name="cusnum" autocomplete="off" value=<?php echo $cusnum; ?>>
                      </div>
                    </div>

                    <div class="form-row">
                      <div class="col">
                        <label> Update Desired Schedule Date : </label>
                        <input type="date" class="form-control" name="cusdate" autocomplete="off" value=<?php echo $cusdate; ?>>
                      </div>
                    </div>

                    <div class="form-row">
                      <div class="col">
                        <label> Update Address: </label>
                        <input type="text" class="form-control" name="cusadd" autocomplete="off" value=<?php echo $cusadd; ?>>
                      </div>

                      <div class="col">
                        <label> Update City:</label>
                        <input type="text" class="form-control" name="cuscity" autocomplete="off" value=<?php echo $cuscity; ?>>
                      </div>

                      <div class="col">
                        <label>Update Province:</label>
                        <input type="text" class="form-control" name="cusprov" autocomplete="off" value=<?php echo $cusprov; ?>>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col">
                        <label> Select Services</label>
                        <select name="services" class="form-control">
                          <option value=<?php echo $services; ?>><?php echo $services; ?></option>
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
                        <input type="text" class="form-control" name="Inquiry" autocomplete="off" row="3" value=<?php echo $Inquiry; ?>></textarea><br>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Update</button>
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

  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

  <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
  <script src="plugins/jszip/jszip.min.js"></script>
  <script src="plugins/pdfmake/pdfmake.min.js"></script>
  <script src="plugins/pdfmake/vfs_fonts.js"></script>
  <script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
</body>
</html>