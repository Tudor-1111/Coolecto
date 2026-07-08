<?php 
require_once __DIR__ . "/../autoload.php";

class Login
{
    private $userDAO;

    public function __construct($userDAO)
    {
        $this->userDAO=$userDAO;
    }

    public function handleRequest()
    {
        if ($_SERVER["REQUEST_METHOD"]=="POST")
            $this->processData();
    }

    public function processData()
    {
        $loginInput=trim($_POST['username']);
        $password=$_POST['password'];
        $userRow=$this->userDAO->getUserByLogin($loginInput);

        if ($userRow)
        {
            if (password_verify($password, $userRow['password']))
            {   
                $payload = [
                    'user_id' => $userRow['id'],
                    'username' => $userRow['username'],
                    'user_pfp' => $userRow['poza_profil'],
                    'email' => $userRow['email'],
                    'exp' => time() + (86400 * 30)
                ];

                $token = JwtHelper::generateToken($payload);

                setcookie("auth_token", $token, time() + (86400 * 30), "/", "", false, true);

                $redirect_url = !empty($_POST['redirect_to']) ? trim($_POST['redirect_to']) : '../firstpage.html';
                
                header("Location: " . $redirect_url);
                exit();
            }
            else 
            {
                header("Location: ../loginpage.html?error=wrong_pass");
                exit();
            }
        }
        else 
        {
            header("Location: ../loginpage.html?error=not_found");
            exit();
        }
    }
}

$dao=new UserDAO($pdo);
$loginProcessor=new Login($dao);
$loginProcessor->handleRequest();

?>