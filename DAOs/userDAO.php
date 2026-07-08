<?php 
require_once __DIR__ . '/../model/user.php';

class UserDAO
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo=$pdo;
    }

    public function add (User $user)
    {
        $sql="INSERT INTO USERS (USERNAME, EMAIL, PASSWORD)
                VALUES (:USERNAME, :EMAIL, :PASSWORD)";
            
        $stmt=$this->pdo->prepare($sql);

        $stmt->execute([
            ':USERNAME'=>$user->username,
            ':EMAIL'=>$user->email,
            ':PASSWORD'=>$user->password
        ]);
    }

    public function getUserByLogin($input)
    {
        $sql="SELECT * FROM USERS WHERE USERNAME = :input OR EMAIL = :input";
        $stmt=$this->pdo->prepare($sql);
        $stmt->execute([':input' => $input]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUsername($id,$usernamenou)
    {
        $sql="UPDATE USERS SET username=:usernamenou WHERE id=:userid";
        $stmt=$this->pdo->prepare($sql);
        $stmt->execute([
            ':usernamenou'=>$usernamenou,
            ':userid'=>$id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfilePicture($id, $pozanou)
    {
        $sql="UPDATE USERS SET poza_profil=:pozanou WHERE id=:id";
        $stmt=$this->pdo->prepare($sql);
        return $stmt->execute([
            ':pozanou'=>$pozanou,
            ':id'=>$id
        ]);
    }

    public function searchUsersByUsername($searchQuery) {
        $sql = "SELECT username FROM users WHERE username ILIKE :query LIMIT 5";
        
 
        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute(['query' => $searchQuery . '%']);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

?>