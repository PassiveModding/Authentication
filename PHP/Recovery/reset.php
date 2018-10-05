<?php
if($_GET['key'] && $_GET['reset'])
{
  if ($_GET['reset'] == NULL)
  {
    echo "Invalid Information sent";
  }
  else
  {
    session_start();
    // Get config from the parent directory
    require(__DIR__.'/../Config/config.php');
    
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
        $info = 'Visited expired reset link';
        $addtolog = $connection->prepare("INSERT INTO `reset_log` (`ip`, `time`, `email`, `info`) VALUES ( ? , CURRENT_TIMESTAMP , ? , ?)");
        $addtolog->bind_param("sss", $_SERVER['REMOTE_ADDR'], $_POST['key'], $info);
        $addtolog->execute();
        $addtolog->get_result();
  
        // Update the user to make sure that people cannot re-use the reset key
        echo "Expired";
        $expstmt = $connection->prepare("UPDATE `users` SET `resetkey` = NULL, `resetgenerationtime` = NULL WHERE email = ? AND `resetkey` = ?");
        $expstmt->bind_param("ss", $_GET['key'], $_GET['reset']);
        $expstmt->execute();
        $result = $expstmt->get_result();
      }
      else
      {

        // Insert a new log event into the reset logs
        $info = 'Visited reset link';
        $addtolog = $connection->prepare("INSERT INTO `reset_log` (`ip`, `time`, `email`, `info`) VALUES ( ? , CURRENT_TIMESTAMP , ? , ?)");
        $addtolog->bind_param("sss", $_SERVER['REMOTE_ADDR'], $_POST['key'], $info);
        $addtolog->execute();
        $addtolog->get_result();
  

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