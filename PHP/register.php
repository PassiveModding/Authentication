<?php

// Check that the user provided all required data
if (isset($_POST['username']) and isset($_POST['password']) and isset($_POST['passwordconfirm']))
{
	// Ensure that the password and confirmation are equal
	if ($_POST['password'] === $_POST['passwordconfirm'])
	{	
		// Start the session and include the config
		session_start();
		require('config.php');

		// Prepare the selection statement to stop sql injection attacks
		$stmt = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
		$stmt->bind_param("s", $_POST['username']);	
		$stmt->execute();
		$result = $stmt->get_result();

		// Ensure that there isn't a user with that account name already registered
		if ($result->num_rows > 0)
		{
			$response->ErrorMessage = "User with that username is already registered";
		}
		else
		{		
			// Hash the user supplied password
			$hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

			// Prepare the selection statement to stop sql injection attacks
			$regstmt = $connection->prepare("INSERT INTO `users`(`username`, `password`, `expirytime`, `level`) VALUES ( ? , ? , CURRENT_TIMESTAMP, 0)");
			$regstmt->bind_param("ss", $_POST['username'], $hash);		
			$regstmt->execute();
			$regresult = $regstmt->get_result();

			// Prepare the selection statement to stop sql injection attacks
			$getuser = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
			$getuser->bind_param("s", $_POST['username']);		
			$getuser->execute();
			$getuserresult = $getuser->get_result();

			// Get and return the newly registered user's info (not including password hash)
			$reguser = $getuserresult->fetch_assoc();			
			$response->SuccessMessage = "Successfully registered";
			$response->UserName = $reguser['username'];
			$response->Id = $reguser['id'];
			$response->ExpiryTime = $reguser['expirytime'];
			$response->AccessLevel = $reguser['level'];
		}	
	}
	else
	{
		$response->ErrorMessage = "Password confirm did not match";
	}
}
else
{
	$response->ErrorMessage = "Invalid Parameters provided";
}

// Return the relevant response
if (isset($response))
{
	echo json_encode($response);
}

?>