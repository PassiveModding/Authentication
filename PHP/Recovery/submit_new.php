<?php
// Get config from the parent directory
require(__DIR__.'/../Config/config.php');

if(isset($_POST['password']) AND isset($_POST['key']) AND isset($_POST['reset']))
{
    // Ensure reset value is actually valid
    if ($_POST['reset'] == NULL)
    {
        echo "Invalid Information sent";
    }
    else
    {
        session_start();

        $stmt = $connection->prepare("SELECT * FROM `users` WHERE `email` = ? AND `resetkey` = ?");
        $stmt->bind_param("ss", $_POST['key'], $_POST['reset']);
        $stmt->execute();
        $result = $stmt->get_result();    
    
        if($result->num_rows == 1)
        {
            $row = $result->fetch_assoc();

            // Still ensure that the user is within the time period
            if ((time() - strtotime($row['resetgenerationtime'])) > 60*5)
            {
                echo "Expired";
            }
            else
            {
                // Hash the new password
                $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

                // Add a new log to the password reset logs
                $info = 'Reset password';
                $addtolog = $connection->prepare("INSERT INTO `reset_log` (`ip`, `time`, `email`, `info`) VALUES ( ? , CURRENT_TIMESTAMP , ? , ?)");
                $addtolog->bind_param("sss", $_SERVER['REMOTE_ADDR'], $_POST['key'], $info);
                $addtolog->execute();
                $addtolog->get_result();
        
                // Update the specified user's password and reset the password_reset information
                $updatestmt = $connection->prepare("UPDATE `users` SET `password` = ?, `resetkey` = NULL, `resetgenerationtime` = NULL WHERE email = ? AND `resetkey` = ?");
                $updatestmt->bind_param("sss", $hash, $_POST['key'], $_POST['reset']);
                $updatestmt->execute();

                echo "Reset Password Successfully!";
            }
        }
    }
}
?>