<html>
<body>
<?php
$to      = 'info@avispa-project.org';
$subject = '[AVISPA] Tool Download Request';
$message = 	'Name: ' . $_POST["user_name"] . "\r\n" .
		'Institution: ' . $_POST["inst_name"] . "\r\n" .
		'Type of Institution: ' . $_POST["inst_type"] . "\r\n" .
		'Address: ' . $_POST["inst_addr"] . "\r\n" .
		'Phone/Fax: ' . $_POST["phone"] . "\r\n" .
		'Platform: ' . $_POST["platform"] . "\r\n" .
		'Purpose: ' . $_POST["purpose"] . "\r\n" .
		'Comments: ' . $_POST["comment"] . "\r\n";
if (!empty($_POST["publish_name"])) {
  		$message = $message . "Authorization: yes \r\n";
} else {
		$message = $message . "Authorization: no \r\n";
}
if (!empty($_POST["mailing_list"])) {
  		$message = $message . "Mailing List: yes \r\n";
} else {
		$message = $message . "Mailing List: no \r\n";
}		 
$headers = 'From: ' . $_POST["email"] . "\r\n" .
    'Reply-To: ' . $_POST["email"] . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?> 

<center>
<strong>Thank you, your request has been submitted.</strong>
<br>
You will receive the AVISPA tool download link at: <?php echo $_POST["email"]; ?>
</center>
</body>
</html>
