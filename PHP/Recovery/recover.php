<?php
// Get config from the parent directory
require(__DIR__.'/../Config/config.php');

// Ensure the correct parameters have been sent
if (isset($_POST['email']))
{
	// Begin the session and ensure that the config is included
    session_start();

	// Prepare the query in order to avoid sql injection attacks
	$stmt = $connection->prepare("SELECT * FROM `users` WHERE email = ? ");
	$stmt->bind_param("s", $_POST['email']);
	$stmt->execute();
	$result = $stmt->get_result();

	// If there is only one result then try to login
	if ($result->num_rows !== 1)
	{		
        // NOTE This is a lie
        // However we return this with any email provided in order to try and deny malicious users
        // Any any possible information about user emails
        $response->SuccessMessage = "Email has been sent";
        $response->Success = true;
        return encodeobject($response);
    }

    // Get the row associated with the user
    $userrow = $result->fetch_assoc();

    $time = time();

    $reset = bin2hex(random_bytes(16));

    // This is the reset link that we will be using to reset the user's password
    // Please change the href to your website in order for this to work
    $link="<a href='".RESET_PHP_URL."?key=".$userrow['email']."&reset=".$reset."'>Click To Reset password</a>";

    $date = date('Y-m-d H:i:s', $time);

    // Update reset data in user profile
    $updatestmt = $connection->prepare("UPDATE `users` SET `resetkey` = ?, `resetgenerationtime` = ? WHERE email = ? ");
    $updatestmt->bind_param("sss", $reset, $date, $_POST['email']);
    $updatestmt->execute();

    // Update the reset log table with new data
    $info = 'Requested password reset';
    $addtolog = $connection->prepare("INSERT INTO `reset_log` (`ip`, `time`, `email`, `info`) VALUES ( ? , CURRENT_TIMESTAMP , ? , ?)");
    $addtolog->bind_param("sss", $_SERVER['REMOTE_ADDR'], $_POST['email'], $info);
    $addtolog->execute();

    // Get the PHPMailer stuff so we can send a recovery link to the user's email
    require(__DIR__.'/../PHPMailer\\Exception.php');
    require(__DIR__.'/../PHPMailer\\PHPMailer.php');
    require(__DIR__.'/../PHPMailer\\SMTP.php');

    $mail = new PHPMailer\PHPMailer\PHPMailer;
    $mail->CharSet =  "utf-8";
    $mail->IsSMTP();
    $mail->SMTPAuth = true;   
    $mail->SMTPSecure = "ssl";  
    $mail->IsHTML(true);

    // Your email address that will be sending recovery emails
    $mail->Username = RESET_EMAIL;
    // The password for that email address
    $mail->Password = RESET_EMAIL_PASSWORD;
    // The host of your email ie. SMTP.gmail.com
    $mail->Host = RESET_EMAIL_HOST;
    // The port used by your host
    $mail->Port = RESET_EMAIL_PORT;
    // This should be the same as your username that was provided
    $mail->From = RESET_EMAIL;
    // The name of the user sending
    $mail->FromName = RESET_EMAIL_DISPLAYNAME;
    // The user to send to (you don't need to change this one)
    $mail->AddAddress($userrow['email'], $userrow['username']);
    // The email subject and body
    $mail->Subject  =  RESET_EMAIL_SUBJECT;
    $mail->Body    = 'Hey, '.$userrow['username'].' Click On This Link to Reset Password '.$link.'';
    
    if($mail->Send())
    {
        $response->SuccessMessage = "Email has been sent";
        $response->Success = true;
    }
    else
    {
        $response->ErrorMessage = "Email error, please contact an administrator";
        $response->Success = false;
    }

    return encodeobject($response);
}

$response->ErrorMessage = "Invalid Parameters provided";
$response->Success = false;
return encodeobject($response);
?>