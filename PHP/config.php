<?php
// Your server url it mydatabase.website.com
// You may need to specify a port if the server is at a location different to your files
define('DB_SERVER', 'PUT_SERVER_URL_HERE');
// Your server login username and password
// This account must have update, insert, delete and select permissions
define('DB_USERNAME', 'PUT_SERVER_LOGIN_USERNAME_HERE');
define('DB_PASSWORD', 'PUT_SERVER_LOGIN_PASSWORD_HERE');
// Your server database name, ie. passive_database
define('DB_DATABASE', 'PUT_SERVER_DATABASE_NAME_HERE');

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