<?php
class User 
{
    public $pfp;
    public $username;
    public $email;
    public $password;

    public function __construct($username, $email, $password,$pfp)
    {
        $this->pfp=$pfp;
        $this->username=$username;
        $this->email=$email;
        $this->password=$password;
    }

}
?>