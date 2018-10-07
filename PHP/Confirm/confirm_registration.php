<?php
// Get config from the parent directory
require(__DIR__.'/../Config/config.php');

if (!ALLOW_EMAIL_ACCOUNT_CONFIRMATION)
{
	echo "Administrator has denied access to this task"; 
	return;
}

// Ensure the correct parameters have been sent
if (isset($_GET['email']) AND isset($_GET['registration_key']))
{
	// Begin the session and ensure that the config is included
    session_start();

	// Prepare the query in order to avoid sql injection attacks
	$stmt = $connection->prepare("SELECT * FROM `users` WHERE email = ? AND registration_key = ? AND confirmed_account = false");
	$stmt->bind_param("ss", $_GET['email'], $_GET['registration_key']);
	$stmt->execute();
	$result = $stmt->get_result();

	// If there is only one result then try to login
	if ($result->num_rows !== 1)
	{		
        // NOTE This is a lie
        // However we return this with any email provided in order to try and deny malicious users
        // Any any possible information about user emails
        echo "Invalid Email or Confirmation Key";
        return;
    }

    // Get the row associated with the user
    $userrow = $result->fetch_assoc();
    
    // Update reset data in user profile
    $updatestmt = $connection->prepare("UPDATE `users` SET `confirmed_account` = true WHERE `id` = ? ");
    $updatestmt->bind_param('s', $userrow['id']);
    $updatestmt->execute();

    logMessage($userrow['username']." Confirmed Their Email", $_SERVER['REMOTE_ADDR'], $userrow['username'], $connection);

    echo "Successfully confirmed email!";
    return;
}

echo "Invalid Parameters provided";
return;
?>