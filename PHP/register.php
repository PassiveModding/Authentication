<?php
require('Config/config.php');

if (!isset($response)) 
{
    $response = new stdClass();
}

if (!ALLOW_REGISTRATION)
{
	$response->ErrorMessage = "Administrator has denied access to this task"; 
	$response->Success = false;
	return encodeobject($response);
}

// Check that the user provided all required data
if (isset($_POST['username']) and isset($_POST['password']) and isset($_POST['passwordconfirm']) and isset($_POST['email'])) 
{	
	// Ensure username is of correct length that it will fit in the database correctly
	if (strlen($_POST['username']) > 32 or strlen($_POST['username']) < 3) 
	{
		$response->ErrorMessage = "Username length must be between 3 and 32 characters long";
		$response->Success = false;
		return encodeobject($response);
	}
	
	// Ensure that the password and confirmation are equal
	if ($_POST['password'] !== $_POST['passwordconfirm']) 
	{
		$response->ErrorMessage = "Password confirm did not match";
		$response->Success = false;
		return encodeobject($response);

	}

	// Ensure the user is using a password that isn't too short
	if (strlen($_POST['password']) < 6) 
	{
		$response->ErrorMessage = "Password must be greater than 6 characters long";
		$response->Success = false;
		return encodeobject($response);
	}

	// Ensure that the provided email is in a valid format
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
	{
		$response->ErrorMessage = "Invalid email format";
		$response->Success = false;
		return encodeobject($response);
	}

	// Start the session and include the config
	session_start();

	// Prepare the selection statement to stop sql injection attacks
	$stmt = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
	$stmt->bind_param("s", $_POST['username']);
	$stmt->execute();
	$result = $stmt->get_result();

	// Ensure that there isn't a user with that account name already registered
	if ($result->num_rows > 0) 
	{
		$response->ErrorMessage = "User with that username is already registered";
		$response->Success = false;
		return encodeobject($response);
	}
		
	// Prepare the selection statement to stop sql injection attacks
	$emstmt = $connection->prepare("SELECT * FROM `users` WHERE email= ? ");
	$emstmt->bind_param("s", $_POST['email']);
	$emstmt->execute();
	$emresult = $emstmt->get_result();

	// Check to see if a user has already registered with the provided email address
	if ($emresult->num_rows > 0) 
	{
		$response->ErrorMessage = "User with that email is already registered";
		$response->Success = false;
		return encodeobject($response);
	}

	// Hash the user supplied password
	$hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
	$account_confirmation = bin2hex(random_bytes(16));

	if (ALLOW_EMAIL_ACCOUNT_CONFIRMATION)
	{
		$confirmed = false;
	}
	else
	{
		$confirmed = true;
	}

	// Prepare the selection statement to stop sql injection attacks
	$regstmt = $connection->prepare("INSERT INTO `users`(`username`, `password`, `expiry_date`, `level`, `email`, `registration_date`, `confirmed_account`, `registration_key`) VALUES ( ? , ? , CURRENT_TIMESTAMP, 0, ? , CURRENT_TIMESTAMP, ? , ?)");
	$regstmt->bind_param("sssis", $_POST['username'], $hash, $_POST['email'], $confirmed, $account_confirmation);
	$regstmt->execute();
	$regresult = $regstmt->get_result();

	// Prepare the selection statement to stop sql injection attacks
	$getuser = $connection->prepare("SELECT * FROM `users` WHERE `id` = $regstmt->insert_id ");
	$getuser->execute();
	$getuserresult = $getuser->get_result();

	// Get and return the newly registered user's info (not including password hash)
	$reguser = $getuserresult->fetch_assoc();

	$response->SuccessMessage = "Successfully registered";
	$response->Id = $reguser['id'];
	$response->UserName = $reguser['username'];
	$response->AccessLevel = $reguser['level'];
	$response->Expiry_Date = $reguser['expiry_date'];
	$response->Registration_Date = $reguser['registration_date'];
	$response->Email = $reguser['email'];
	$response->Success = true;

	if (ALLOW_EMAIL_ACCOUNT_CONFIRMATION)
	{
		// Please change the href to your website in order for this to work
		$link="<a href='".CONFIRM_PHP_URL."?email=".$reguser['email']."&registration_key=".$account_confirmation."'>Click To Confirm Your Account</a>";

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
		$mail->Username = CONFIRM_EMAIL;
		// The password for that email address
		$mail->Password = CONFIRM_EMAIL_PASSWORD;
		// The host of your email ie. SMTP.gmail.com
		$mail->Host = CONFIRM_EMAIL_HOST;
		// The port used by your host
		$mail->Port = CONFIRM_EMAIL_PORT;
		// This should be the same as your username that was provided
		$mail->From = CONFIRM_EMAIL;
		// The name of the user sending
		$mail->FromName = CONFIRM_EMAIL_DISPLAYNAME;
		// The user to send to (you don't need to change this one)
		$mail->AddAddress($reguser['email'], $reguser['username']);
		// The email subject and body
		$mail->Subject  =  CONFIRM_EMAIL_SUBJECT;
		$mail->Body    = 'Hey, '.$reguser['username'].' Click On This Link to Confirm your account! '.$link.'';		

		    
		if($mail->Send())
		{
			$response->SuccessMessage = "Confirmation email has been sent";
			$response->Success = true;
		}
		else
		{
			$response->ErrorMessage = "Email error, please contact an administrator";
			$response->Success = false;
		}
	}

	return encodeobject($response);
} 

$response->ErrorMessage = "Invalid Parameters provided";
$response->Success = false;
return encodeobject($response);
?>