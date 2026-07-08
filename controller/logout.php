<?php 

setcookie("auth_token", "", time() - 3600, "/", "", false, true);

header("Location: ../firstpage.html");
exit();
?>