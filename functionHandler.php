<?php 
class functionHandler
{
	private $conn;

	//Database initialization
	public function __construct()
	{
		$dbObj = new Database();
		$this->conn = $dbObj->dbConn();
	}

	//Login verification
	public function logIn($email, $password)
	{
		$sql = "SELECT id, pass FROM users_table WHERE email = :email AND active = 1";
		$preparedQuery = $this->conn->prepare($sql);
		$preparedQuery->execute(array(":email" => $email));
		$dataFromDb = $preparedQuery->fetch(PDO::FETCH_ASSOC);
		extract($dataFromDb);
		
		if($preparedQuery->rowCount() == 1)
		{
			$checkPassword = password_verify($password, $pass);

			if($checkPassword == true)
			{
				$_SESSION['logged_in_user_id'] = $id;
				$this->redirect("dashboard.php");
			}
			else
			{
				setcookie('msg', 'Wrong Password', time()+5);
				$this->redirect("index.php");
			}
		}
		else
		{
			setcookie('msg', "Email, Password didn't match", time()+5);
			$this->redirect("index.php");
		}
	}

	//User registration
	public function registration($name, $email, $password)
	{
		if($this->check_email_availability($email) == true)
		{
			$hashed_pass = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
			$now = time();
			$access_token = md5(rand(0,1000)); // Generates random 32 character has
			$sql = "INSERT INTO users_table (user_name, email, pass, access_token, created_at) VALUES (:name, :email, :hashed_pass, :access_token, :now)";
			$preparedQuery = $this->conn->prepare($sql);
			$preparedQuery->bindparam(":name", $name);
			$preparedQuery->bindparam(":email", $email);
			$preparedQuery->bindparam(":hashed_pass", $hashed_pass);
			$preparedQuery->bindparam(":access_token", $access_token);
			$preparedQuery->bindparam(":now", $now);
			$preparedQuery->execute();
			$this->send_email($email, $name, $access_token);
			setcookie('msg', "Your account has been created. <br/> Account activation link has been sent to your email.", time()+5);
			$this->redirect("index.php#toregister");
		}
		else
		{
			setcookie('msg', "This email is used once", time()+5);
			$this->redirect("index.php#toregister");
		}
	}

	//Verification of email using access token
	public function verify($email, $accessToken)
	{
		$sql = "UPDATE users_table SET active = 1 WHERE email = :email AND access_token = :accessToken";
		$preparedQuery = $this->conn->prepare($sql);
		$preparedQuery->bindparam(":email", $email);
		$preparedQuery->bindparam(":accessToken", $accessToken);
		$preparedQuery->execute();
		if($preparedQuery->rowCount() > 0) { $this->redirect("dashboard.php");}
		else {
			setcookie('msg', "This verification token is not valid", time()+5);
			$this->redirect("index.php");
		}
	}

	//Checking a user is logged in or not
	public function login_status()
	{
		if(isset($_SESSION['logged_in_user_id']))
		{
			return true;
		}
		else return false;
	}

	//Ending of a users session
	public function logout()
	{
		session_destroy();
		$this->redirect("index.php");
	}

	//Redirect to a given url
	public function redirect($URL)
	{
		header("Location: $URL");
	}

	//Send mail to the users email for verification purpose
	public function send_email($email, $user_name, $hash)
	{
		//This email funcionality will not work at localhost environment.

        $to      = $email; // recipient
        $subject = 'Signup | Verification'; // subject of email
        $message = '
         
        Thanks for signing up!
        Your account has been created, you can login with the following credentials after you have activated 
        your account by pressing the url below.
         
        ------------------------
        Username: '.$user_name.'
        ------------------------
         
        Please click this link to activate your account:
        http://www.examle.com/verify.php?email='.$email.'&accessToken='.$hash.'
         
        '; // Our message above including the link
                             
        $headers = 'From:noreply@example.com' . "\r\n"; // Set from headers
        mail($to, $subject, $message, $headers); // Send our email
        
    	$msg = 'Your account has been made. <br /> Please verify it by clicking the activation link that has been send to your email.';
	}

	//Checking a email address is used or not
	public function check_email_availability($email)
	{
        $sql = "SELECT COUNT('email') as emailPresent FROM users_table WHERE email =  :email";
        $prepared = $this->conn->prepare($sql);
        $prepared->bindparam(':email', $email);
        $prepared->execute();
        $count = $prepared->fetch(PDO::FETCH_ASSOC);
        if($count['emailPresent'] == 0) return true;
        else return false;
    }

    //Returns the last inserted id of database table
    public function lastInsertedID()
    {
    	$id = $this->conn->lastInsertId();
    	return $id;
    }
}
 ?>