<?php 
session_start();

function __autoload($className)
{
	include_once($className.".php");
}

$class = new functionHandler();
if($class->login_status() == false)
{
	$class->redirect("index.php");
}

if(isset($_POST['action'])) $class->logout();
 ?>
 <!DOCTYPE html>
 <html>
 <head>
 	<title>Dashboard</title>
 </head>
 <body>
 <h1>Dashboard</h1>
 <h2>Logged in successfully</h2>
 <form method="POST" action="#">

<input type="submit" name="action" value="LOGOUT">
	
 </form>
 </body>
 </html>