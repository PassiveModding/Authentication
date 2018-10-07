<?php
require('Config/config.php');

if (!isset($response)) 
{
    $response = new stdClass();
}

if (!ALLOW_TOKEN_REDEMTION)
{
	$response->ErrorMessage = "Administrator has denied access to this task"; 
	$response->Success = false;
	return encodeobject($response);
}

// Ensure the correct data has been provided
if (isset($_POST['username']) and isset($_POST['token']))
{
    // Start the session and include the config
    session_start();

    // Prepare the selection to avoid sql injection issues
    $stmt = $connection->prepare("SELECT * FROM `users` WHERE username= ? ");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $userresult = $stmt->get_result();

    // Check that a user was returned
	if ($userresult->num_rows !== 1)
	{	
        $response->ErrorMessage = "Invalid Username";
        $response->Success = false;
        return encodeobject($response);
    }

    $userrow = $userresult->fetch_assoc();

    if (ALLOW_EMAIL_ACCOUNT_CONFIRMATION)
    {
        if ($userrow['confirmed_account'] == false)
        {
            $response->ErrorMessage = "Account must be confirmed in order to redeem tokens";
            $response->Success = false;
            return encodeobject($response);
        }
    }

    // Prepare the selection to avoid sql injection issues
    $tokenstmt = $connection->prepare("SELECT * FROM `tokens` WHERE token= ? ");
    $tokenstmt->bind_param("s", $_POST['token']);
    $tokenstmt->execute();
    $tokenresult = $tokenstmt->get_result();

    // Check that the token actually exists
    if ($tokenresult->num_rows != 1)
    {
        $response->ErrorMessage = "Invalid token";
        $response->Success = false;
        return encodeobject($response);
    }

    $tokenrow = $tokenresult->fetch_assoc();

    // If the current expiry time is less that the current time
    // Ensure to use the most up to date time
    if(strtotime($userrow['expiry_date']) > time())
    {
        $timetoupdate = strtotime($userrow['expiry_date']);
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
    $updatestmt = $connection->prepare("UPDATE `users` SET `expiry_date` = ?, `level` = ? WHERE username = ? ");
    $updatestmt->bind_param("sis", $date, $tokenrow['level'], $_POST['username']);
    $updatestmt->execute();

    $response->Success = true;
    $response->SuccessMessage = "Updated Expiry Time";
    $response->UserName = $userrow['username'];
    $response->Token_Redeemed = $_POST['token'];
    $response->Expiry_Date = $date;
    $response->AccessLevel = $userrow['level'];
    $response->Years = $tokenrow['years'];
    $response->Months = $tokenrow['months'];
    $response->Weeks = $tokenrow['weeks'];
    $response->Days = $tokenrow['days'];

    // Delete the token that was redeemed
    $removestmt = $connection->prepare("DELETE FROM `tokens` WHERE token = ? ");
    $removestmt->bind_param("s", $_POST['token']);
    $removestmt->execute();
    return encodeobject($response);
}

$response->ErrorMessage = "Invalid Parameters provided";
$response->Success = false;
return encodeobject($response);
?>