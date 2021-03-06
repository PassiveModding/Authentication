<?php
// Get config from the parent directory
require(__DIR__.'/../Config/config.php');

if (!ALLOW_PASSWORD_RECOVERY OR !ALLOW_EMAIL_ACCOUNT_RECOVERY)
{
  echo "Administrator has denied access to this task"; 
  return;
}

if($_GET['key'] && $_GET['reset'])
{
  if ($_GET['reset'] == NULL)
  {
    echo "Invalid Information sent";
  }
  else
  {
    session_start();
    
    $stmt = $connection->prepare("SELECT * FROM `users` WHERE `email` = ? AND `resetkey` = ?");
    $stmt->bind_param("ss", $_GET['key'], $_GET['reset']);
    $stmt->execute();
    $result = $stmt->get_result();


    if($result->num_rows == 1)
    {
      $row = $result->fetch_assoc();

      // Expire the token if time outside of 5 mins from generation
      if ((time() - strtotime($row['resetgenerationtime'])) > 60*5)
      {    
        // Insert a new log event into out reset logs
        logMessage($row['username']." Visited Expired Reset Link", $_SERVER['REMOTE_ADDR'], $row['username'], $connection);  

        // Update the user to make sure that people cannot re-use the reset key
        echo "Expired";
        $expstmt = $connection->prepare("UPDATE `users` SET `resetkey` = NULL, `resetgenerationtime` = NULL WHERE email = ? AND `resetkey` = ?");
        $expstmt->bind_param("ss", $_GET['key'], $_GET['reset']);
        $expstmt->execute();
        $result = $expstmt->get_result();
      }
      else
      {
        logMessage($row['username']." Visited Reset Link", $_SERVER['REMOTE_ADDR'], $row['username'], $connection);  

        // This is the content of the page that will be displayed to the user if they are still resetting the password
        ?>
        <form method="post" action="submit_new.php">
        <input type="hidden" name="key" value="<?php echo $_GET['key'];?>">
        <input type="hidden" name="reset" value="<?php echo $_GET['reset'];?>">
        <p>Enter New password</p>
        <input type="password" name='password'>
        <input type="submit" name="submit_password">
        </form>
        <?php
      }
    }  
  }


}
else
{
  echo "Invalid Information sent";
}
?>