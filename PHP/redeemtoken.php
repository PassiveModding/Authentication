<?php

// Ensure the correct data has been provided
if (isset($_POST['username']) and isset($_POST['token']))
{
    // Start the session and include the config
    session_start();
    require('config.php');

    // Prepare the selection to avoid sql injection issues
    $stmt = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $userresult = $stmt->get_result();

    // Check that a user was returned
	if ($userresult->num_rows === 1)
	{		
        // Prepare the selection to avoid sql injection issues
        $tokenstmt = $connection->prepare("SELECT * FROM `tokens` WHERE token= ? ");
        $tokenstmt->bind_param("s", $_POST['token']);
        $tokenstmt->execute();
        $tokenresult = $tokenstmt->get_result();

        // Check that the token actually exists
        if ($tokenresult->num_rows == 1)
        {
		    $userrow = $userresult->fetch_assoc();
            $tokenrow = $tokenresult->fetch_assoc();

            // If the current expiry time is less that the current time
            // Ensure to use the most up to date time
            if(strtotime($userrow['expirytime']) > time())
            {
                $timetoupdate = strtotime($userrow['expirytime']);
            }
            else
            {
                $timetoupdate = time();
            }

            $addontime = 0;

            // Convert all provided values into seconds
            if ($tokenrow['years'] > 0)
            {
                $addontime += 86400 * 365 * $tokenrow['years'];
            }

            if ($tokenrow['months'] > 0)
            {
                $addontime += 86400 * 30 * $tokenrow['months'];
            }

            if ($tokenrow['weeks'] > 0)
            {
                $addontime += 86400 * 7 * $tokenrow['weeks'];
            }

            if ($tokenrow['days'] > 0)
            {
                $addontime += 86400 * $tokenrow['days'];
            }

            // Create a date value based on the original time + token redeemed time
            $date = date('Y-m-d H:i:s', $timetoupdate + $addontime);

            // Update the user's row
            $updatestmt = $connection->prepare("UPDATE `users` SET `expirytime` = ?, `level` = ? WHERE username = ? ");
            $updatestmt->bind_param("sis", $date, $tokenrow['level'], $_POST['username']);
            $updatestmt->execute();

            $response->SuccessMessage = "Updated Expiry Time";
			$response->UserName = $userrow['username'];
            $response->Id = $userrow['id'];
            
            // Ensure to update the returned expiry time and access level
            // This avoids having to query the database again 
			$response->ExpiryTime = $date;
            $response->AccessLevel = $tokenrow['level'];
            
            // Delete the token that was redeemed
            $removestmt = $connection->prepare("DELETE FROM `tokens` WHERE token = ? ");
            $removestmt->bind_param("s", $_POST['token']);
            $removestmt->execute();
        }
        else
        {
            $response->ErrorMessage = "Invalid token";
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

// Return the updated user data OR an error message
if (isset($response))
{
	echo json_encode($response);
}

?>