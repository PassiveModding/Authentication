<?php
require('Config/config.php');

function secondsToTimeInterval($seconds) {
	$remaining = round($seconds);
	$days = floor($t/86400);
	//$day_sec = $days*86400;
	$remaining -= $days*86400;
	$hours = floor( $remaining / 3600 );
	//$hour_sec = $hours*3600;
	$remaining -= $hours*3600;
	$minutes = floor( $remaining /60);
	// $min_sec = $minutes*60;
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

	// This is the currenly stored hash of the users password
	$hash = $userrow['password'];
	
	// Verify that the hash matches the user supplied password
	if (!password_verify($_POST['password'], $hash))
	{
		$response->ErrorMessage = "Invalid Password";
		$response->Success = false;
		return encodeobject($response);
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