<?php
require(__DIR__.'/../Config/config.php');

if (!isset($response)) 
{
    $response = new stdClass();
}

if (!ALLOW_PASSWORD_RESET)
{
	$response->ErrorMessage = "Administrator has denied access to this task"; 
	$response->Success = false;
	return encodeobject($response);
}

// Ensure the correct parameters have been sent
if (isset($_POST['username']) and isset($_POST['current_password']) and isset($_POST['new_password']) and isset($_POST['new_password_confirm']))
{
	// Ensure username is of correct length that it will fit in the database correctly
	if (strlen($_POST['username']) > 32 OR strlen($_POST['username']) < 3)
	{
		$response->ErrorMessage = "Invalid Username Length"; 
		$response->Success = false;
		return encodeobject($response);
	}

    // Ensure that the new password and confirm are the same
    if ($_POST['new_password'] != $_POST['new_password_confirm'])
    {
        $response->ErrorMessage = "New Password does not match confirmed password";
		$response->Success = false;
		return encodeobject($response);
    }

	// Ensure the user is using a password that isn't too short
	if (strlen($_POST['current_password']) < 6)
	{
		$response->ErrorMessage = "Invalid Password Length";
		$response->Success = false;
		return encodeobject($response);
    }
    
    // Ensure that the new password isn't too short
    if (strlen($_POST['new_password']) < 6)
    {
        $response->ErrorMessage = "New password is too short (must be greater than 6 characters long)";
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

	// Verify that the hash matches the user supplied original password
	if (!password_verify($_POST['current_password'], $userrow['password']))
	{
		$response->ErrorMessage = "Invalid Password";
		$response->Success = false;
		return encodeobject($response);
	}
	
	if ($_POST['current_password'] == $_POST['new_password'])
	{
		$response->ErrorMessage = "New password cannot be the same as old password";
		$response->Success = false;
		return encodeobject($response);
	}
    
    // Hash the new password
    $hash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Update the specified user's password and reset the password_reset information
    $updatestmt = $connection->prepare("UPDATE `users` SET `password` = ? WHERE username = ? ");
    $updatestmt->bind_param("ss", $hash, $_POST['username']);
    $updatestmt->execute();
	
	logMessage($userrow['username']." Reset their password", $_SERVER['REMOTE_ADDR'], $userrow['username'], $connection);  

	// Set the response info and attach relevant data
	// NOTE: You should never include things like the password when returning data back to the user
	$response->Success = true;
	$response->UserName = $userrow['username'];			
	$response->SuccessMessage = "Password Successfully reset";
	return encodeobject($response);	
}

$response->ErrorMessage = "Invalid Parameters provided";
$response->Success = false;
return encodeobject($response);
?>