<?php 
session_start();

function __autoload($className)
{
	include_once($className.".php");
}

$class = new functionHandler();

//Code for login section
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["login"]))
{
    extract($_REQUEST);

    if(!empty($email) && !empty($password))
    {
        $class->logIn($email, $password);
    }
}

//Code for registration section
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["registration"]))
{
    extract($_REQUEST);
    if(!empty($name) && !empty($email) && !empty($password))
    {
        $class->registration($name, $email, $password);
    }
}

//Code for verification section
if(isset($_GET['email']) && isset($_GET['accessToken']))
{
    extract($_GET);
    $class->verify($email, $accessToken);
}
 ?>