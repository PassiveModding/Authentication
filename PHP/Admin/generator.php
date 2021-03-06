<?php
function generate_serial() {
    static $max = 60466175; // ZZZZZZ in decimal
    return strtoupper(sprintf(
        "%05s-%05s-%05s-%05s",
        base_convert(rand(0, $max), 10, 36),
        base_convert(rand(0, $max), 10, 36),
        base_convert(rand(0, $max), 10, 36),
        base_convert(rand(0, $max), 10, 36)
    ));
}

if (!isset($response)) 
{
    $response = new stdClass();
}

require(__DIR__.'/../Config/config.php');

if (!ALLOW_TOKEN_CREATION)
{
	$response->ErrorMessage = "Administrator has denied access to this task"; 
	$response->Success = false;
	return encodeobject($response);
}

session_start();

// Ensure that all the values have been provided
if (isset($_POST['years']) and isset($_POST['months']) and isset($_POST['weeks']) and isset($_POST['days']) and isset($_POST['quantity']) and isset($_POST['verification']) and isset($_POST['userlevel']))
{
    // Filter out all values that would cause issues like users losing time when theyt redeem a token, no time tokens or invalid user levels etc.
    if ($_POST['userlevel'] < 0 
        OR $_POST['quantity'] <= 0 
        OR $_POST['days'] < 0 
        OR $_POST['weeks'] < 0 
        OR $_POST['months'] < 0 
        OR $_POST['years'] < 0 
        OR ($_POST['years'] <= 0 AND $_POST['months'] <= 0 AND $_POST['weeks'] <= 0 AND $_POST['days'] <= 0))
    {
        $response->ErrorMessage = "Time, User Level and Quantity must have feasable values";
        $response->Success = false;
        return encodeobject($response);
    }

    //This is just a small extra step to ensure that only people who know about the generator and can confirm this value are able to create licenses
    if ($_POST['verification'] != GENERATOR_VERIFY)
    {
        $response->ErrorMessage = "Invalid parameters specified";
        $response->Success = false;
        return encodeobject($response);
    }

    // Prepare and setup the statement to avoid sql injection risks
    $stmt = $connection->prepare("INSERT INTO `tokens` (`token`,`years`,`months`,`weeks`,`days`,`level`) VALUES ( ? , ? , ? , ? , ? , ? )");
    $stmt->bind_param('siiiii', $token, $_POST['years'], $_POST['months'], $_POST['weeks'], $_POST['days'], $_POST['userlevel']);

    $licarray = array();

    for ($i = 0; $i < $_POST['quantity']; $i++)
    {
        //For each license generate a new, unique serial
        //NOTE: You may want to consider adding more checks to ensure that in the
        //      Unlikely case that a dupe license is generated it would be filtered out
        $token = generate_serial();
        $stmt->execute();
        array_push($licarray, $token);
    }

    $response->TokenList = $licarray;
    $response->SuccessMessage = $_POST['quantity']." Tokens generated successfully";
    $response->Success = true;
    $response->Years = $_POST['years'];
    $response->Months = $_POST['months'];
    $response->Weeks = $_POST['weeks'];
    $response->Days = $_POST['days'];
    $response->Level = $_POST['userlevel'];

    logMessage("ADMIN, generated ".$_POST['quantity']." token(s)", $_SERVER['REMOTE_ADDR'], "ADMIN", $connection);

    return encodeobject($response);    
}

$response->ErrorMessage = "Invalid parameters specified";
$response->Success = false;
return encodeobject($response);
?>