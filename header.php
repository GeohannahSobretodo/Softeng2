<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BCS</title>


  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <Style>
    .button {
      background-color: #5BE6FF;
      /* Green */
      font-weight: bold;
      border: none;
      color: black;
      padding: 6px;
      padding-left: 20px;
      padding-right: 20px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 18px;
      margin: 2px 2px;
      cursor: pointer;
    }

    .button4 {
      border-radius: 20px;
      border-style: outset;
      border-width: 1px;
      border-color: #5BE6FF;
      margin-right: 40px;
      margin-left: 30px;
    }

    .button4 {
      transition: .2s;
      box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
      border: 1px solid #5BE6FF;
    }

    ul li {
      font-size: 18px;

    }

    ul li a:hover {
      font-weight: bold;
      font-size: 19px;
      color: white;
      text-align: center;

    }

    @media screen and (max-width: 500px) {
      ul li a:hover {
        font-weight: bold;
        background-color: #211677;
        border-radius: 15px;
      }

      .button {
        margin-top: 20px;
      }
    }

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
  </style>
</head>

<body class="hold-transition layout-top-nav">

  <div class="wrapper">
    <nav class="main-header navbar navbar fixed-top navbar-expand-md navbar-dark elevation-4" style=" border-image: linear-gradient(to right, rgb(144, 244, 255), rgb(9, 105, 196)); background: linear-gradient(to right, rgb(144, 244, 255), rgb(9, 105, 196));">
      <a href="index3.html" class="navbar-brand">
        <img src="headerbareng.png" alt="bcs" class="brand-image img-circle elevation-3" style=" width: 45px; height: 45px; margin-left: 20px;">
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
            <a href="services.php" class="nav-link">SERVICES</a>
          </li>
          <li class="nav-item">
            <a href="ContactUs.php" class="nav-link">CONTACT US</a>
          </li>
        </ul>
        <ul class="navbar-nav">
          <a href="#divOne" button class="button button4">Book Now</button></a>
        </ul>
      </div>
    </nav>



    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>

</body>

</html>