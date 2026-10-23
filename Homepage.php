<?php
include 'connect.php';

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
    echo "Data Inserted Successfully";
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Homepage</title>
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <style>
    .back {
      position: absolute;
      z-index: -1;
      height: 118vh;
      width: 100%;
    }

    .contents {
      text-align: center;
      padding-top: 100px;
    }

    .contents h1 {
      font-size: 100px;
      color: #fff;
      font-weight: 600;
      transition: 0.5s;

    }

    .contents h1:hover {
      -webkit-text-stroke: 2px #fff;
      color: transparent;
    }

    .contents a {
      text-decoration: None;
      display: inline-block;
      color: #fff;
      font-size: 25px;
      border: 2px solid #fff;
      padding: 10px 50px;
      border-radius: 50px;
      margin-top: 0px;
    }

    .contents a:hover {
      color: #fff;
      font-weight: bold;
      background-color: #5BE6FF;
      border: 2px solid #fff;
      padding: 10px 50px;
      border-radius: 50px;
      margin-top: 0px;
    }

    @media only screen and ((min-width: 480px) and (max-width: 500px)) {
      .contents {
        text-align: center;
        padding-top: 75px;
        ;
      }

      .contents h1 {
        font-size: 40px;
      }

      .back {
        top: -230px;
      }

      .contents a {
        font-size: 14px;
        padding: 5px 20px;
      }
    }

    @media only screen and ((min-width: 510px) and (max-width: 768px)) {
      .contents {
        text-align: center;
        padding-top: 75px;
        ;
      }

      .contents h1 {
        font-size: 40px;
      }

      .back {
        top: -220px;
      }

      .contents a {
        font-size: 14px;
        padding: 5px 20px;
      }
    }

    .content h1 {
      text-align: center;
      font-size: 80px;
      font-weight: bold;
      padding-top: 150px;
    }

    .abot {
      background-image: url(abot.png);
      overflow-x: hidden;
      background-repeat: no-repeat;
      background-position: relative;
      background-attachment: inherit;
    }

    .head {
      margin-top: 80px;
    }

    .about-1 {
      margin: 0px;
      padding: 0px;
    }

    .about-1 h1 {
      margin-left: 60px;
      text-align: left;
      color: #ffffff;
      font-weight: bold;
      text-shadow: 2px 5px 3px rgb(107, 107, 107);
    }

    .about-1 p {
      margin-left: 60px;
      margin-right: 60px;
      max-width: 500px;
      text-align: left;
      font-size: 25px;
      padding: 7px;
      color: rgb(255, 255, 255);
    }

    .about-item {
      margin-top: 50px;
      margin-bottom: 30px;
      background-color: rgb(98, 197, 214);
      padding: 90px 50px;
      box-shadow: 0 0 9px rgb(22, 22, 22);
      border-radius: 10px;
    }

    .about-item h3 {
      font-size: 25px;
      margin-bottom: 10px;
    }

    .about-item hr {
      width: 50px;
      height: 3px;
      background-color: rgb(0, 0, 0);
      margin: 0 auto;
      border: none;
    }

    .about-item p {
      margin-top: 20px;
      margin-left: 10px;
      margin-right: 10px;
      padding: 7px;
    }

    .about-item:hover {
      background-color: rgb(252, 252, 252);
    }

    .overlay {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.8);
      transition: opacity 500ms;
      visibility: hidden;
      opacity: 0;
    }

    .overlay:target {
      visibility: visible;
      opacity: 1;
    }

    .wrappers {
      margin: 88px auto;
      padding: 0.5%;
      background: #e7e7e7af;
      border-radius: 20px;
      width: 30%;
      position: relative;
      transition: all 1s ease-in-out;
      width: 500px;
    }

    .wrappers h2 {
      margin-left: 22px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333333;
    }

    .wrappers .close {
      position: absolute;
      top: 10px;
      right: 30px;
      cursor: pointer;
      transition: all 100ms;
      font-size: 30px;
      font-weight: bold;
      text-decoration: none;
      color: #333;
    }

    .wrappers .content {
      max-height: 20%;

    }

    .form-row {
      margin: 10px;
    }

    input[type=text] {
      height: 35px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 20px;
      box-sizing: border-box;
      resize: vertical;
    }

    .btn-appointment {
      background-color: #138496 !important;
      width: 170px;
      border-radius: 20px;
    }

    @media screen and (max-width: 575px) {
      .wrappers {
        width: 450px;
      }

      .fname {
        width: 170px;
      }

      .lname {
        width: 170px;
        float: right;
        margin-top: -35px;
      }

      .num {
        width: 170px;
      }

      .email {
        width: 170px;
        float: right;
        margin-top: -35px;
      }

      .date {
        width: 170px;
      }

      .time {
        width: 170px;
        float: right;
        margin-top: -35px;
      }
    }
  </style>
</head>

<body>
  <video autoplay loop muted plays-inline class="back">
    <source src="cleaning.mp4" type="video/mp4">
  </video>
  <div class="contents">
    <img class="rounded mx-auto d-block" src="logobareng.png" style="width:41%; height:51%;">
    <h1> Welcome to BCS</h1>
    <a href="#divOne"> Book Now</a>
  </div>

  <div class="content">
    <h1> Why Should I choose Bareng Cleaning Services ? </h1>
    <br>
  </div>

  <div class="container">
    <div class=" row">
      <div class="col-md-6">
        <div class="card card-primary elevation-4">
          <div class="card-header" style="background-color:darkblue;">
            <h3 class="card-title" style="font-size: 30px ; font-weight: bold;">
              Quality
            </h3>
          </div>

          <div class="card-body" style="font-size: 20px">
            <p>We provide high quality in terms of cleaning and of course we use quality cleaning materials and tools to be sure our services is good for our customers.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card card-primary elevation-4">
          <div class="card-header" style="background-color:darkblue;">
            <h3 class="card-title" style="font-size: 30px ; font-weight: bold;">
              Workers
            </h3>
          </div>
          <div class="card-body " style="font-size:20px ;">
            <p>Our employees are well-trained and skilled, and they also receive monthly training and seminars to ensure that the excellence of our company and service is maintained.
            </p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card card-primary elevation-4">
          <div class="card-header" style="background-color:darkblue;">
            <h3 class="card-title" style="font-size: 30px ; font-weight: bold;">
              Services
            </h3>
          </div>
          <div class="card-body" style="font-size:21px ;">
            <p>The whole of our service offerings are of high quality, and all of our items have been approved by our personnel to assure our customers' safety. </p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card card-primary elevation-4">
          <div class="card-header" style="background-color:darkblue;">
            <h3 class="card-title" style="font-size: 30px ; font-weight: bold;">
              Securties</h3>
          </div>
          <div class="card-body" style="font-size:20px ;">
            <p>In order to prevent theft and protect individuals and property, security is largely responsible for implementing security measures in business settings.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>

  <div id="about" class="abot">
    <section>
      <div class="about-1">
        <p class="head"> Our Special Service</p>
        <h1> Bareng CS</h1>
        <P> Bareng Cleaning Service is a trusted cleaning company that offers high-quality cleaning solutions. Our staff members are highly trained to meet your needs, and we guarantee the quality of our service each time you make a reservation.</P>
      </div>

      <div class="container">
        <div class=" row">
          <div class="col-md-6">
            <div class="card card-primary elevation-4" style="background-color:#5BE6FF; border-radius: 10px;">
              <div class="card-header" style="background-color:#5BE6FF; color:black;  border-radius: 10px;">
                <h3 class="card-title" style="font-size: 30px ; font-weight: bold;">
                  Mission
                </h3>
              </div>
              <div class="card-body" style="font-size: 20px">
                <p>At BCS, we're dedicated to exceeding our clients' expectations by offering the best business cleaning services available. To support and promote the usage of more valuable services through online reservations. Our goal is to establish and uphold conditions that are successful and pleasurable and that encourage performance.<Br><Br><Br></p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card card-primary elevation-4" style="background-color:#5BE6FF; border-radius: 10px;">
              <div class="card-header" style="background-color:#5BE6FF; color:black;  border-radius: 10px;">
                <h3 class="card-title" style="font-size: 30px ; font-weight: bold;">
                  Vision
                </h3>
              </div>
              <div class="card-body" style="font-size:20px ;">
                <p>We firmly believed that everyone has a right to a spotless, secure, and enjoyable place to live and work. BCS furnishes a clean, sanitize, and healthy environment for our clients’ homes and offices. Understanding our clients' and workers' needs and meeting those needs towards the greatest standards of professionalism and honesty in the company enables us to develop long-lasting connections with both groups of individuals.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
  </div>
  </div>
  </section>
  <footer class="page-footer font-small cyan darken-3" style="background-color:rgb(25, 53, 80)
; color: aliceblue;">
    <div class="container">
      <div class="row">
        <div class="col-md-12 py-5">
          <div class="mb-5" style="text-align: center ;">
            <a class="fb-ic" href="https://www.facebook.com/CvSUBacoorCityCampus/">
              <i class="fab fa-facebook-f fa-lg white-text mr-md-5 mr-3 fa-2x"> </i>
            </a>
            <a class="tw-ic" href="https://twitter.com/cvsucsg?lang=en">
              <i class="fab fa-twitter fa-lg white-text mr-md-5 mr-3 fa-2x"> </i>
            </a>
            <a class="dc-ic" href="https://discord.gg/P5YSDBb5">
              <i class="fab fa-discord fa-lg white-text mr-md-5 mr-3 fa-2x"> </i>
            </a>
          </div>
        </div>
      </div>



      <div class="col-sm-7">
        <div class="form-data">
          <form method="post">

            <div class="overlay" id="divOne">
              <div class="wrappers">
                <h2>Book Now</h2>
                <a href="#" class="close">&times;</a>
                <div class="content">


                  <div class="form-row">
                    <div class="col">
                      <label> Enter Full Name: </label>
                      <input type="text" class="form-control" name="cusname" placeholder="Surname, Firstname, M.I" autocomplete="off" style="border-radius: 20px;" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="col">
                      <label> Enter Email: </label>
                      <input type="text" class="form-control" name="cusemail" placeholder="Email Address" autocomplete="off" style="border-radius: 20px;" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="col">
                      <label> Enter Contact Number:</label>
                      <input type="tel" class="form-control" name="cuscontact" placeholder="contact no." autocomplete="off" style="border-radius: 20px;" required>
                    </div>

                    <div class="col">
                      <label>Desired Schedule Date: </label>
                      <input type="date" class="form-control" name="cusdate" autocomplete="off" style="border-radius: 20px;" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="col">
                      <label> Address: </label>
                      <input type="text" class="form-control" name="cusadd" placeholder="Lot, Blk, Street, Village" autocomplete="off" style="border-radius: 20px;" required>
                    </div>

                    <div class="col">
                      <label> Enter City:</label>
                      <input type="text" class="form-control" name="cuscity" placeholder="City" autocomplete="off" style="border-radius: 20px;" required>
                    </div>

                    <div class="col">
                      <label>Enter Province:</label>
                      <input type="text" class="form-control" name="cusprov" placeholder="Province" autocomplete="off" style="border-radius: 20px;">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="col">
                      <label> Select Services</label>
                      <select name="services" class="form-control" style="border-radius: 20px;">
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
                      <label> Note(Optional): </label>
                      <input type="text" class="form-control" name="Inquiry" placeholder="" autocomplete="off"><br>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-primary" name="submit">Book Now</button>
          </form>


        </div>
      </div>
    </div>

</body>

</html>