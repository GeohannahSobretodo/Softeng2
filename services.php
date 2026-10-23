<?php
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title> Services </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

</head>
<style>
  body {
    background-image: url(services.jpg);
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    background-attachment: fixed;
  }

  img {
    max-width: 100%;
  }

  .gallery {
    background-color: #377c9f00;
    padding: 40px 0;
  }

  .gallery img {
    background-color: #f7f7f7;
    padding: 15px 15px 45px 15px;
    width: 100%;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    cursor: pointer;
  }

  .content h1 {
    padding-top: 50px;
    text-align: center;
    color: #64eaf1;
    font-size: 50px;
    margin-bottom: 40px;
    text-shadow: 2px 5px 3px rgb(15, 66, 90);
  }

  p {
    text-align: center;
    font-size: 15px;
    font-weight: 400;
    padding-left: 15px;
    padding-right: 15px;
    padding-bottom: 15px;
    background-color: #ffff;
    margin-top: -35px;
    border-radius: 5px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: rgb(0, 0, 0);
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);

  }

  span {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 18px;
    color: rgb(0, 51, 85);
    font-weight: 600;
    letter-spacing: 2px;
  }

  .read {
    text-align: center;
    margin-top: 24px;
    margin-bottom: 15px;

    font-size: 20px;
    background-color: #00000069;
    padding-left: -23px;
  }

  .submit {
    border-radius: 20px;
    border: none;
    width: 150px;
    margin-top: 220px;
  }

  .submit:hover {
    box-shadow: 1px 5px 7px 1px rgba(0, 0, 0, 0.2);
  }
</style>
<body>
  <section class="gallery min-vh-100">
    <div class="content">
      <h1>Our Services!</h1>
    </div>

    <div class="container-lg">
      <div class="row gy-4 row-cols-1 row-cols-sm-2 row-cols-md-3">
        <div class="col">
          <img src="img1 .jpg" class="gallery-item" alt="gallery">
          <p><span>House Premium Cleaning</span> <br><br>
            The highest quality of house service that we can offer. The furniture and the entire house will be cleaned. Keeping your house clean will stop the spread of germs and help keep you and your family healthy.
          </p>
        </div>

        <div class="col">
          <img src="img2.jpg" class="gallery-item" alt="gallery">
          <p><span>Office Premium Cleaning</span> <br><br>
            Keeping your workplace clean can lower sickness, which in turn lowers missed work days. The best work environment service we can offer. Start with the tools, materials, furnishings, and equipment at the workplace.
          </p>
        </div>

        <div class="col">
          <img src="img3 .jpg" class="gallery-item" alt="gallery">
          <p><span>Sofa Deep Cleaning</span> <br><br>
            Our regular deep sofa cleaning service helps to maintain its appearance longer and prevents damage and stains from detracting from it. Keeping the colors vibrant and the fabrics as they should be.
          </p>
        </div>

        <div class="col">
          <img src="img4 .jpg" class="gallery-item" alt="gallery">
          <p><span>Mattress Deep Cleaning</span> <br><br>
            We are offering a service that professionally cleans your mattress, which will help you sleep much better and can extend the life of your mattress by several years. It is to prevent severe odors, bacterial growth, and heavy stains from accumulating on your mattress.
          </p>
        </div>

        <div class="col">
          <img src="img5 .jpg" class="gallery-item" alt="gallery">
          <p><span>Disinfection Service</span> <br><br>
            Disinfection is a must, and one should regularly clean and disinfect surfaces and objects to prevent the spread of infections. We provide the best disinfection services, and you can rest assured that we've cleansed, sanitized, and disinfected every corner of your place.
          </p>
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

  <script src="bootstrap.bundle.min.js"></script>
  <script src="main.js"></script>
</body>
</html>