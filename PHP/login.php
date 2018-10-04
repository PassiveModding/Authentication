<?php
// Ensure the correct parameters have been sent
if (isset($_POST['username']) and isset($_POST['password']))
{
	// Begin the session and ensure that the config is included
	session_start();
	require('config.php');

	// Prepare the query in order to avoid sql injection attacks
	$stmt = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
	$stmt->bind_param("s", $_POST['username']);
	$stmt->execute();
	$result = $stmt->get_result();

	// If there is only one result then try to login
	if ($result->num_rows === 1)
	{		
		// Get the row associated with the user
		$row = $result->fetch_assoc();

		// This is the currenly stored hash of the users password
		$hash = $row['password'];
		
		// Verify that the hash matches the user supplied password
		if (password_verify($_POST['password'], $hash))
		{
			// Set the response info and attach relevant data
			// NOTE: You should never include things like the password when returning data back to the user
			$response->SuccessMessage = "Successfully logged in";
			$response->UserName = $row['username'];
			$response->Id = $row['id'];
			$response->ExpiryTime = $row['expirytime'];
			$response->AccessLevel = $row['level'];
		}
		else
		{
			$response->ErrorMessage = "Invalid Password";
		}
	}
	else
	{		
		$response->ErrorMessage = "Invalid Username";
	}	
}
else
{
	$response->ErrorMessage = "Invalid Parameters provided";
}

// Respond with either the error message or relevant user details
if (isset($response))
{
	echo json_encode($response);
}

?>