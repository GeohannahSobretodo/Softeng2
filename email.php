<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Send e-mail to someone@example.com: holxpqemmmhhgaud</h2>

    <form action="mailto:someone@example.com" method="post" enctype="text/plain">
        <input type="text" name="name" id="name" value="George">
        <input type="text" name="mail" id="mail" value="geordansobretodo@gmail.com">
        <input type="text" name="comment" id="comment" size="50" value="This is the subject">
        <input type="text" name="subject" id="subject" size="50" value="This is the body">
        <button id="btn" type="submit">Send</button>
    </form>
 
</body>
<script src="https://smtpjs.com/v3/smtp.js"></script>
<script>
    var btn = document.getElementById('btn');
    btn.addEventListener('click', function (e) {
        e.preventDefault()
        
        var name = document.getElementById('name').value;
        var mail = document.getElementById('mail').value;
        var subject = document.getElementById('subject').value;        
        var comment = document.getElementById('comment').value;
        var body = 'name: ' + name + '<br>email: ' + email + '<br>subject: ' + subject + '<br>mesage: ' + comment;

        Email.send({
            Host: "smtp.gmail.com",
            Username: "geordansobretodo@gmail.com",
            Password: "Geordan072102",
            To: mail,
            From: mail,
            Subject: subject,
            Body: body
        }).then(
            message => alert(message)
        );

    })
</script>
</html> -->
<?php
include 'connect.php';

// $query = mysqli_query($conn, "SELECT LAST('cusemail') FROM `pending`") or die(mysqli_error());
// $fetch = mysqli_fetch_array($query); 
// $cusemail = $fetch['cusemail'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master\src\Exception.php';
require 'PHPMailer-master\src\PHPMailer.php';
require 'PHPMailer-master\src\SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'geordansobretodo@gmail.com';
    $mail->Password   = 'holxpqemmmhhgaud';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('geordansobretodo@gmail.com', 'Bareng Cleaning Service');
    $mail->addAddress('bc.johngodwin.tejuco@cvsu.edu.ph', '');

    $mail->isHTML(true);
    $mail->Subject = 'Automatic Email';
    $mail->Body    = 'You have successfully booked.';
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>