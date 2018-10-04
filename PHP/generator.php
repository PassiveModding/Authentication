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

session_start();
require('config.php');

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
        $tokengenerationresponse->ErrorMessage = "Time, User Level and Quantity must have feasable values";
    }
    else
    {
        //You may want to consider changing confirmationkey to something more secure
        //This is just a small extra step to ensure that only people who know about the generator and can confirm this value are able to create licenses
        if ($_POST['verification'] == "confirmationkey")
        {
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

            $tokengenerationresponse->TokenList = $licarray;
        }
        else
        {
            $tokengenerationresponse->ErrorMessage = "Invalid parameters specified";
        }
    }
}
else
{
    $tokengenerationresponse->ErrorMessage = "Invalid parameters specified";
}

// Send a json response back to the user with the generated licenses OR the error message
if (isset($tokengenerationresponse))
{
    echo json_encode($tokengenerationresponse);
}
?>