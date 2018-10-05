<?php

// Check that the user provided all required data
if (isset($_POST['username']) and isset($_POST['password']) and isset($_POST['passwordconfirm']) and isset($_POST['email']))
{
	// Ensure username is of correct length that it will fit in the database correctly
	if (strlen($_POST['username']) > 32 OR strlen($_POST['username']) < 3)
	{
		$response->ErrorMessage = "Username length must be between 3 and 32 characters long"; 
		$response->Success = false;
	}
	else
	{	
		// Ensure that the password and confirmation are equal
		if ($_POST['password'] === $_POST['passwordconfirm'])
		{	
			// Ensure the user is using a password that isn't too short
			if (strlen($_POST['password']) < 6)
			{
				$response->ErrorMessage = "Password must be greater than 6 characters long"; 
				$response->Success = false;
			}
			else
			{
				// Ensure that the provided email is in a valid format
				if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
				{
					$response->ErrorMessage = "Invalid email format"; 
					$response->Success = false;
				}
				else
				{
					// Start the session and include the config
					session_start();
					require('Config/config.php');

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
					}
					else
					{		
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
						}
						else
						{
							// Hash the user supplied password
							$hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

							// Prepare the selection statement to stop sql injection attacks
							$regstmt = $connection->prepare("INSERT INTO `users`(`username`, `password`, `expiry_date`, `level`, `email`, `registration_date`) VALUES ( ? , ? , CURRENT_TIMESTAMP, 0, ? , CURRENT_TIMESTAMP)");
							$regstmt->bind_param("sss", $_POST['username'], $hash, $_POST['email']);		
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
							$response->Id = $reguser['id'];
							$response->UserName = $reguser['username'];
							$response->AccessLevel = $reguser['level'];
							$response->Expiry_Date = $reguser['expiry_date'];
							$response->Registration_Date = $reguser['registration_date'];
							$response->Email = $reguser['email'];
							$response->Success = true;
						}
					}	
				}
			}
		}
		else
		{
			$response->ErrorMessage = "Password confirm did not match";
			$response->Success = false;
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