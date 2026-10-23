<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Book Now </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <style>
    body {
      font-family: Verdana, Geneva, Tahoma, sans-serif;
      background: #e7e7e7;
      height: 100vh;
    }

    .box {
      text-align: center;
      margin-top: 15%;
    }

    .button {
      font-size: 1em;
      padding: 15px 35px;
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

<body>
  <div class="box">
    <a href="#divOne" class="button">Book Now</a>
  </div>

  <div class="col-sm-7">
    <div class="form-data">
      <form action="">

        <div class="overlay" id="divOne">
          <div class="wrappers">
            <h2>Book Now</h2>
            <a href="#" class="close">&times;</a>
            <div class="content">

              <div class="form-body">
                <div class="row form-row">
                  <div class="col-sm-6">
                    <div class="fname">
                      <input type="text" placeholder="First Name" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="lname">
                      <input type="text" placeholder="Last Name" class="form-control" required>
                    </div>
                  </div>
                </div>

                <div class="row form-row">
                  <div class="col-sm-6">
                    <div class="num">
                      <input type="text" placeholder="Phone Number" class="form-control" required>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="email">
                      <input type="text" placeholder="Email" class="form-control" required>
                    </div>
                  </div>
                </div>

                <div class="row form-row">
                  <div class="col-sm-12">
                    <input type="text" placeholder="Complete Address" class="form-control" required>
                  </div>
                </div>

                <div class="row form-row">
                  <div class="col-sm-6">
                    <div class="date">
                      <input type="date" class="form-control" style="border-radius: 20px;" required>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="time">
                      <input type="time" class="form-control" style="border-radius: 20px;" required>
                    </div>
                  </div>
                </div>

                <div class="row form-row">
                  <div class="col-sm-12">
                    <div class="select">
                      <select class="form-select form-select-sm" aria-label=".form-select-sm example" style="border-radius: 20px">
                        <option selected disabled>Choose Service</option>
                        <option value="1">Premium House Cleaning</option>
                        <option value="2">Premium Office Cleaning</option>
                        <option value="3">Sofa Deep Cleaning</option>
                        <option value="4">Mattress Deep Cleaning</option>
                        <option value="5">Disinfetion Service</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row form-row">
                  <div class="col-sm-12">
                    <input id="message" type="text" placeholder="Message" class="form-control">
                  </div>
                </div>

                <div class="row form-row">
                  <div class="col-sm-8">
                    <button type="submit" class="btn btn-primary btn-appointment">
                      Book Now
                    </button>
                  </div>
                </div>
      </form>

    </div>
  </div>

</body>