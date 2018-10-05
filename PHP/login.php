<?php
// Ensure the correct parameters have been sent
if (isset($_POST['username']) and isset($_POST['password']))
{
	// Ensure username is of correct length that it will fit in the database correctly
	if (strlen($_POST['username']) > 32 OR strlen($_POST['username']) < 3)
	{
		$response->ErrorMessage = "Invalid Username Length"; 
		$response->Success = false;
	}
	else
	{	
		// Ensure the user is using a password that isn't too short
		if (strlen($_POST['password']) < 6)
		{
			$response->ErrorMessage = "Invalid Password Length";
			$response->Success = false;
		}
		else
		{ 
			// Begin the session and ensure that the config is included
			session_start();
			require('Config/config.php');

			// Prepare the query in order to avoid sql injection attacks
			$stmt = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
			$stmt->bind_param("s", $_POST['username']);
			$stmt->execute();
			$result = $stmt->get_result();

			// If there is only one result then try to login
			if ($result->num_rows === 1)
			{		
				// Get the row associated with the user
				$userrow = $result->fetch_assoc();

				// This is the currenly stored hash of the users password
				$hash = $userrow['password'];
				
				// Verify that the hash matches the user supplied password
				if (password_verify($_POST['password'], $hash))
				{
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
				}
				else
				{
					$response->ErrorMessage = "Invalid Password";
					$response->Success = false;
				}
			}
			else
			{		
				$response->ErrorMessage = "Invalid Username";
				$response->Success = false;
			}	
		}
	}
}
else
{
	$response->ErrorMessage = "Invalid Parameters provided";
	$response->Success = false;
}

// Respond with either the error message or relevant user details
if (isset($response))
{
	$text = json_encode($response);
	$crypt = openssl_encrypt($text, 'AES-256-CBC', ENCRYPT_KEY);
	echo($crypt);
}

?>