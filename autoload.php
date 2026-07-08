<?php
$cale_config = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'coolecto_db_config.php';

if (file_exists($cale_config)) {
    require_once($cale_config);
} else {
    die("Eroare critica: Fisierul lipseste. PHP cauta fix la adresa asta: " . $cale_config);
}

$cale_auth = __DIR__ . DIRECTORY_SEPARATOR . 'utils' . DIRECTORY_SEPARATOR . 'auth_check.php';
if (file_exists($cale_auth)) {
    require_once($cale_auth);
}

function autoload($class) {
    $classParts = explode("\\", $class);
    $className = $classParts[count($classParts) - 1];
    
    $dir = __DIR__;
    $sep = DIRECTORY_SEPARATOR;
    
    $foldere = [
        $dir . $sep . 'model' . $sep,
        $dir . $sep . 'DAOs' . $sep,
        $dir . $sep . 'utils' . $sep
    ];
    
    foreach ($foldere as $folder) {
        $path = $folder . $className . ".php";
        
        if (file_exists($path)) {
            require_once($path);
            return; 
        }
    }
}

spl_autoload_register('autoload');

?>