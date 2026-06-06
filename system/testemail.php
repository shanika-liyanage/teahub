<?php
include '../init.php';
$body = "
<html>
<body style='font-family: Arial;'>
   <h2 style='color: blue;'>Welcome John Doe</h2>
   <p>This is a styled email sent from PHP.</p>
   <a href='#' style='background: green; color: white; padding: 10px;'>Click Here</a>
</body>
</html>
";


$result = sendEmail(
   'wickramamawatha@gmail.com',
   'John Doe',
   'Test Email',
   '<h3>This is a test email</h3>'//   $body 
);

if ($result) {
   echo "Email sent successfully!";
} else {
   echo "Email sending failed!";
}

?>