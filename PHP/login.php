<?php
require('Config/config.php');

function secondsToTimeInterval($seconds) {
	$remaining = round($seconds);
	$days = floor($remaining/86400);
	$remaining -= $days*86400;
	$hours = floor( $remaining / 3600 );
	$remaining -= $hours*3600;
	$minutes = floor( $remaining /60);
	$remaining -= $minutes*60;
	$sec = $remaining;
	return sprintf('%02d:%02d:%02d:%02d', $days, $hours, $minutes, $sec);
  }
  

if (!isset($response)) 
{
    $response = new stdClass();
}

if (!ALLOW_LOGIN)
{
	$response->ErrorMessage = "Administrator has denied access to this task"; 
	$response->Success = false;
	return encodeobject($response);
}

// Ensure the correct parameters have been sent
if (isset($_POST['username']) and isset($_POST['password']))
{
	// Ensure username is of correct length that it will fit in the database correctly
	if (strlen($_POST['username']) > 32 OR strlen($_POST['username']) < 3)
	{
		$response->ErrorMessage = "Invalid Username Length"; 
		$response->Success = false;
		return encodeobject($response);
	}

	// Ensure the user is using a password that isn't too short
	if (strlen($_POST['password']) < 6)
	{
		$response->ErrorMessage = "Invalid Password Length";
		$response->Success = false;
		return encodeobject($response);
	}

	// Begin the session and ensure that the config is included
	session_start();

	// Prepare the query in order to avoid sql injection attacks
	$stmt = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
	$stmt->bind_param("s", $_POST['username']);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows !== 1)
	{		
		// If there is no username match then return
		$response->ErrorMessage = "Invalid Username";
		$response->Success = false;
		return encodeobject($response);
	}

	// Get the row associated with the user
	$userrow = $result->fetch_assoc();

	if ($userrow['login_attempts'] >= MAX_LOGIN_ATTEMPTS AND MAX_LOGIN_ATTEMPTS > 0)
	{
		$response->ErrorMessage = "You have exceeded the maximum login attempts";
		$response->Success = false;

		if (NOTIFY_USER_ON_MAX_ATTEMPTS AND ALLOW_EMAIL_ACCOUNT_RECOVERY AND ($userrow['emailed_suspicious_activity'] == false))
		{
			// Get the PHPMailer stuff so we can send a recovery link to the user's email
			require('PHPMailer\\Exception.php');
			require('PHPMailer\\PHPMailer.php');
			require('PHPMailer\\SMTP.php');

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
			$mail->Subject  =  "Suspicious Activity on your ".SOFTWARE_NAME." Account";
			$mail->Body     = 'Hey, '.$userrow['username'].' We have detected suspicious login attempts on your account from the IP address:'.$_SERVER['REMOTE_ADDR'].' and subsequently locked your account, it is recommended that you reset your password immediately through the software!';		
			$mail->Send();

			// Make sure that you don't spam the user with emails
			$updatestmt = $connection->prepare("UPDATE `users` SET `emailed_suspicious_activity` = 1 WHERE `username` = ? ");
			$updatestmt->bind_param("s", $_POST['username']);
			$updatestmt->execute();

		}

		return encodeobject($response);
	}


	// This is the currenly stored hash of the users password
	$hash = $userrow['password'];
	
	// Verify that the hash matches the user supplied password
	if (!password_verify($_POST['password'], $hash))
	{
		$response->ErrorMessage = "Invalid Password";
		$response->Success = false;

		$current_attempts = $userrow['login_attempts'] + 1;

		// Update the specified user's login attempt count if they enter the wrong password
		$updatestmt = $connection->prepare("UPDATE `users` SET `login_attempts` = ? WHERE `username` = ? ");
		$updatestmt->bind_param("is", $current_attempts, $_POST['username']);
		$updatestmt->execute();
	
		return encodeobject($response);
	}
	else
	{
		// Reset if password is correct.
		$updatestmt = $connection->prepare("UPDATE `users` SET `login_attempts` = 0, `emailed_suspicious_activity` = 0, `last_ip` = ? WHERE `username` = ? ");
		$updatestmt->bind_param("ss", $_SERVER['REMOTE_ADDR'], $_POST['username']);
		$updatestmt->execute();
	}

	if (ALLOW_EMAIL_ACCOUNT_CONFIRMATION)
	{
		if ($userrow['confirmed_account'] != 1)
		{
			$response->ErrorMessage = "Account not confirmed";
			$response->Success = false;
			return encodeobject($response);
		}
	}

	// Set the response info and attach relevant data
	// NOTE: You should never include things like the password when returning data back to the user
	$response->Success = true;
	$response->Id = $userrow['id'];
	$response->UserName = $userrow['username'];
	$response->AccessLevel = $userrow['level'];
	$response->Expiry_Date = $userrow['expiry_date'];
	$response->Registration_Date = $userrow['registration_date'];
	$response->Email = $userrow['email'];				
	$response->SuccessMessage = "Successfully logged in";
	$response->Expired = (time() - strtotime($userrow['expiry_date'])) > 0;
	$response->Remaining_Time = secondsToTimeInterval(strtotime($userrow['expiry_date']) - time());

	logMessage($userrow['username']." Logged In", $_SERVER['REMOTE_ADDR'], $userrow['username'], $connection);
	
	return encodeobject($response);	
}

$response->ErrorMessage = "Invalid Parameters provided";
$response->Success = false;
return encodeobject($response);
?>