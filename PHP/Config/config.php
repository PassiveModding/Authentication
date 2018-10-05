<?php
// Your server url it mydatabase.website.com
// You may need to specify a port if the server is at a location different to your files
define('DB_SERVER', 'PUT SERVER URL HERE');
// Your server login username and password
// This account must have update, insert, delete and select permissions
define('DB_USERNAME', 'PUT LOGIN USERNAME HERE');
define('DB_PASSWORD', 'PUT LOGIN PASSWORD HERE');
// Your server database name, ie. passive_database
define('DB_DATABASE', 'PUT DATABASE NAME HERE');

// Update this content with the information that will be used for password recovery emails
define('RESET_EMAIL', 'myemail@mail.com');
define('RESET_EMAIL_DISPLAYNAME', 'NAME_HERE');
define('RESET_EMAIL_SUBJECT', 'SUBJECT_HERE');
define('RESET_EMAIL_PASSWORD', 'PASSWORD_GOES_HERE');
define('RESET_EMAIL_HOST', 'HOST_HERE');
define('RESET_EMAIL_PORT', 'POST_HERE');

// Update this with a secure key that you want to encrypt your data with (this will be the same key you use in the c# app)
define('ENCRYPT_KEY', 'ANY_RANDOM_STRING');

// Update this with the relative URLs of files
define('RESET_PHP_URL', 'http://path/to/reset.php');

// Update this with the verification value you want to use for generating new tokens
define('GENERATOR_VERIFY', 'VALUE_HERE');

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
?>