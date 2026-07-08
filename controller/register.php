<?php
require_once __DIR__ . "/../autoload.php";

class Register 
{
    private $userDAO;

    public function __construct($userDAO) 
    {
        $this->userDAO = $userDAO;
    }

    public function handleRequest()
    {
        if ($_SERVER["REQUEST_METHOD"]=="POST")
            $this->processData();
    }

    public function processData()
    {
        $username=$_POST['username'];
        $email=$_POST['email'];
        $password=$_POST['password'];
        $passwordconfirm=$_POST['passwordconfirm'];

        if ($password!=$passwordconfirm)
        {
            header("Location: ../registerpage.html?error=password_mismatch");
            exit();
        }

        $hashedpassword=password_hash($password, PASSWORD_DEFAULT);
        $newUser= new User ($username, $email, $hashedpassword, "default_pfp.png");

        try 
        {
            $this->userDAO->add($newUser);

            $newUserRow = $this->userDAO->getUserByLogin($username);

            if ($newUserRow) {
                $payload = [
                    'user_id' => $newUserRow['id'],
                    'username' => $newUserRow['username'],
                    'user_pfp' => $newUserRow['poza_profil'],
                    'email' => $newUserRow['email'],
                    'exp' => time() + (86400 * 30) 
                ];

                $token = JwtHelper::generateToken($payload);

                setcookie("auth_token", $token, time() + (86400 * 30), "/", "", false, true);

                header("Location: ../firstpage.html");
                exit();
            } else {
                header("Location: ../loginpage.html?error=auto_login_failed");
                exit();
            }
        }
        catch (PDOException $e)
        {
            if ($e->getCode()=='23505')
                {
                    header("Location: ../registerpage.html?error=duplicate_user");
                    exit();
                }
            else
                {
                    header("Location: ../registerpage.html?error=db_error");
                    exit();
                }
        }
    }

}

$dao = new UserDAO($pdo);
$registerProcessor = new Register($dao);
$registerProcessor->handleRequest();

?>