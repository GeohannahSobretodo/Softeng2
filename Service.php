<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Services</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="pages.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <Style>
    .button {
      font-size: 1em;
      padding: 10px 25px;
      color: rgb(0, 0, 0);
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s ease-out;
      background: #5be6ff;
      border-radius: 50px;
      box-shadow: 0 0 10px 0 rgb(19, 18, 18);
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
      margin: 140px auto;
      padding: 20px;
      background: #e7e7e7af;
      border-radius: 20px;
      width: 25%;
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
      top: 20px;
      right: 30px;
      cursor: pointer;
      transition: all 100ms;
      font-size: 30px;
      font-weight: bold;
      text-decoration: none;
      color: #333;
    }

    .wrappers .content {
      max-height: 5%;
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

    img {
      max-width: 100%;
      float: right;
      border-radius: 5px;
    }

    body {
      background-image: url(img.jpg);
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
      overflow-x: hidden;
    }

    .gallery {
      padding: 0px 100px 50px 800px;
      margin-top: -455px;
      max-width: 1600px;
      float: right;
      min-height: 500px;
      margin-left: -750px;
      margin-right: -45px;
    }

    .gallery img {
      margin-right: 0px;
      margin-top: -50px;
      background-color: #ffff;
      border-radius: 5px;
      padding: 15px 15px 2px 15px;
      cursor: pointer;
    }

    #gallery-modal .modal-img .modal-content {
      width: 600px;
    }

    p {
      text-align: center;
      font-size: 20px;
      font-weight: 500;
      background-color: #ffff;
      border-radius: 5px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: rgb(0, 0, 0);
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      margin-bottom: 22px;
    }

    .content {
      margin-top: 15%;
      text-align: center;
    }

    .content h1 {
      color: #0e959d;
      margin-top: 80px;
      margin-left: 60px;
      width: 500px;
      font-size: 60px;
      text-align: left;
      text-shadow: 2px 5px 3px rgb(84, 185, 231);
    }

    .container {
      width: 400px;
      margin-top: 50px;
      margin-left: 90px;
      margin-right: 80px;
      background-color: rgba(0, 0, 0, 0.555);
      padding: 35px 55px 12px 45px;
      border-radius: 25px;
    }

    .select {
      cursor: pointer;
      text-align: center;
      height: 35px;
      padding-top: 5px;
      background-color: #b4dbdc;
      border-radius: 25px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: rgb(0, 0, 0);
      margin-bottom: 22px;
    }

    .select :hover {
      color: #043247;
      font-size: 1.466666666666rem;
      transition: .1s;
    }

    @media only screen and (max-width: 1220px) {
      .gallery {
        margin-top: 100px;
        margin-right: 60px;
        max-width: 1800px;
      }

      .content h1 {
        margin-right: -350px;
        margin-top: -5px;
        float: left;
      }

      .container {
        float: right;
        margin-right: 385px;
      }
    }

    @media only screen and (max-width: 904px) {
      .gallery {
        margin-right: 12px;
      }

      .content h1 {
        margin-right: 500px;
        margin-top: -5px;
        float: left;
      }

      .container {
        float: left;
        margin-left: 42px;
      }
    }

    @media only screen and (max-width: 692px) {
      .gallery {
        margin-right: -40px;
      }
    }
  </style>
</head>
<body class="hold-transition layout-top-nav">
  <div class="wrapper">
    <nav class="main-header navbar navbar fixed-top navbar-expand-md navbar-dark" style=" border-image: linear-gradient(to right, rgb(144, 244, 255), rgb(9, 105, 196)); background: linear-gradient(to right, rgb(144, 244, 255), rgb(9, 105, 196));">
      <a href="index3.html" class="navbar-brand">
        <img src="headerbareng.png" alt="spotless" class="brand-image img-circle elevation-2" style=" width: 45px;
        height: 45px; margin-left: 20px;">
      </a>

      <button class="navbar-toggler order-1" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a href="Homepage.php" class="nav-link">HOME</a>
          </li>

          <li class="nav-item">
            <a href="Homepage.php #about" class="nav-link">ABOUT</a>
          </li>

          <li class="nav-item">
            <a href="Service.php" class="nav-link">SERVICES</a>
          </li>

          <li class="nav-item">
            <a href="ContactUs.php" target="_blank" class="nav-link">CONTACT US</a>
          </li>
        </ul>

        <ul class="navbar-nav">
          <a href="Homepage.php #divOne" button class="button button4">Book Now</button></a>
        </ul>
      </div>
    </nav>
    <br><br><br>

    <div class="inner-layer">
      <div class="services">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="content">
              <h1>Our Services!</h1>
            </div>
          </div>
        </div>
      </div>

      <div class="container">
        <div class="select">
          <h5> House Premium Cleaning </h5>
        </div>

        <div class="select">
          <h5> Office Premuim Cleaning </h5>
        </div>

        <div class="select">
          <h5> Sofa Deep Cleaning </h5>
        </div>

        <div class="select">
          <h5> Mattress Deep Cleaning </h5>
        </div>

        <div class="select">
          <h5> Disinfection Service </h5>
        </div>
      </div>
    </div>

    <section class="gallery">
      <div class="float-end">
        <div class="container-lg float-end">
          <div class="row gy-5 row-cols-1 row-cols-sm-1 row-cols-md-2 ">
            <div class="col">
              <img src="img1 .jpg" class="gallery-item" alt="gallery">
              <p class="title">House Premium Cleaning</p>
            </div>

            <div class="cleaning">
              <img src="img2.jpg" class="gallery-item" alt="gallery">
              <p class="title">Office Premium Cleaning</p>
            </div>

            <div class="col">
              <img src="img3 .jpg" class="gallery-item" alt="gallery">
              <p class="title">Sofa Deep Cleaning</p>
            </div>

            <div class="cleaning">
              <img src="img4 .jpg" class="gallery-item" alt="gallery">
              <p class="title">Mattress Deep Cleaning</p>
            </div>

            <div class="col">
              <img src="img5 .jpg" class="gallery-item" alt="gallery">
              <p class="title">Disinfection Service</p>
            </div>
          </div>
        </div>
      </div>
  </div>
  </div>
  </section>

  <div class="modal fade" id="gallery-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered ">
      <div class="modal-content ">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="img1 .jpg" class="modal-img" alt="modal img">
        </div>
      </div>
    </div>
  </div>
  </div>

  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="dist/js/adminlte.min.js"></script>

  <script src="bootstrap.bundle.min.js"></script>
  <script src="main.js"></script>
</body>
</html>