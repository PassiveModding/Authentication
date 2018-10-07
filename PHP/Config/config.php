<?php
//------------------------------//
//     SOFTWARE/PRODUCT INFO    //
//------------------------------//
define('SOFTWARE_NAME', 'MY_SOFTWARE_NAME');

//------------------------------//
//        DATABASE INFO         //
//------------------------------//
// Your server url it mydatabase.website.com
// You may need to specify a port if the server is at a location different to your files
define('DB_SERVER', 'PUT_SERVER_URL_HERE');
// Your server login username and password
// This account must have update, insert, delete and select permissions
define('DB_USERNAME', 'LOGIN_USERNAME_HERE');
define('DB_PASSWORD', 'LOGIN_PASSWORD_HERE');
// Your server database name, ie. passive_database
define('DB_DATABASE', 'DATABASE_NAME_HERE');

// Update this with a secure key that you want to encrypt your data with (this will be the same key you use in the c# app)
define('ENCRYPT_KEY', 'ANY_RANDOM_STRING');

//------------------------------//
//        PERMISSION INFO       //
//------------------------------//
define('ALLOW_REGISTRATION', true);
define('ALLOW_LOGIN', true);
define('ALLOW_TOKEN_REDEMTION', true);
define('ALLOW_TOKEN_CREATION', true);
define('ALLOW_PASSWORD_RECOVERY', true);
define('ALLOW_PASSWORD_RESET', true);

//------------------------------//
//       RESET MAIL INFO        //
//------------------------------//
// Update this content with the information that will be used for password recovery emails
define('ALLOW_EMAIL_ACCOUNT_RECOVERY', true);
define('RESET_EMAIL', 'RECOVERY@WEBSITE.COM');
define('RESET_EMAIL_PASSWORD', 'RESET_EMAIL_PASSWORD_HERE');
define('RESET_EMAIL_HOST', 'EMAIL_HOST_HERE');
define('RESET_EMAIL_PORT', 'EMAIL_PORT_HERE');

define('RESET_EMAIL_DISPLAYNAME', SOFTWARE_NAME.' Password Recovery');
define('RESET_EMAIL_SUBJECT', 'Reset your '.SOFTWARE_NAME.' Password');

// Update this with the relative URLs of files
define('RESET_PHP_URL', 'reset.php URL_HERE');

//------------------------------//
//    ADMIN/GENERATION INFO     //
//------------------------------//
// Update this with the verification value you want to use for generating new tokens
define('GENERATOR_VERIFY', 'VALUE_HERE');

//------------------------------//
//   ACCOUNT CONFIRMATION INFO  //
//------------------------------//
define('ALLOW_EMAIL_ACCOUNT_CONFIRMATION', true);
define('CONFIRM_EMAIL', 'CONFIRM@WEBSITE.COM');
define('CONFIRM_EMAIL_PASSWORD', 'CONFIRM_EMAIL_PASSWORD_HERE');
define('CONFIRM_EMAIL_HOST', 'EMAIL_HOST_HERE');
define('CONFIRM_EMAIL_PORT', 'EMAIL_PASSWORD_HERE');

define('CONFIRM_EMAIL_DISPLAYNAME', SOFTWARE_NAME.' Confirmation');
define('CONFIRM_EMAIL_SUBJECT', 'Confirm your '.SOFTWARE_NAME.' Account');

// Update this with the relative URLs of files
define('CONFIRM_PHP_URL', 'confirm_registration.php URL_HERE');

// Attempt to connect to the server
$connection = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD);
if (!$connection)
{
    // This will have an issue if you have not provided the right credentials
    die("Database Connection Failed" . mysqli_error($connection));
}

// Attempt to select the database
$select_db = mysqli_select_db($connection, DB_DATABASE);
if (!$select_db)
{
    // This will have an issue if you either don't have access to that specific database with your login
    // or you have not created the database yet
    die("Database Selection Failed" . mysqli_error($connection));
}

function encodeobject($content_to_encode) {
    if (isset($content_to_encode))
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $text = json_encode($content_to_encode);
        $crypt = base64_encode(openssl_encrypt($text, 'AES-256-CBC', ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv));
        echo base64_encode($iv)."::".$crypt;
    }
}

function logMessage($message, $ip, $username, mysqli $connection)
{
    if (isset($message) AND isset($ip) AND isset($username))
    {
        $stmt = $connection->prepare("INSERT INTO `log` (`message`, `ip`, `username`, `time`) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->bind_param("sss", $message, $ip, $username);
        $stmt->execute();
    }
}
?>