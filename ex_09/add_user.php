<?php

define('ERROR_LOG_FILE', 'errors.log');

// Fonction de connexion de l'exercice 03
function connect_db(string $host, string $username, string $passwd, string $port, string $db) {
    
try {
        $connection = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $username, $passwd);
        return $connection;

    } catch (PDOException $e) {
        $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans ' . ERROR_LOG_FILE . "\n";
        echo $error;
        file_put_contents(ERROR_LOG_FILE, $error, FILE_APPEND);
        return null;
    }
}

// Vérification des paramètres

if ($argc < 5) {
    $message = "Paramètres incorrects ! Utilisation : php add_user.php name password password_conf is_admin\n";
    echo $message;
    file_put_contents(ERROR_LOG_FILE, $message, FILE_APPEND);
    exit(1);
}

// Récupération des arguments

$name         = $argv[1];
$password     = $argv[2];
$password_conf = $argv[3];
$is_admin     = $argv[4];

// Vérification des mots de passe

$errors = false;

if ($password !== $password_conf) {
    $message = "Mots de passe incorrects\n";
    echo $message;
    file_put_contents(ERROR_LOG_FILE, $message, FILE_APPEND);
    $errors = true;
}

if ($errors) {
    exit(1);
}

// Connexion à la base de données 

$pdo = connect_db('localhost', 'root', '', '3306', 'dbname');

if (!$pdo) {
    $message = "Erreur de connexion à la base de données\n";
    echo $message;
    file_put_contents(ERROR_LOG_FILE, $message, FILE_APPEND);
    exit(1);
}
    else {    

        echo "Connexion à la base de données réussie\n";}

// Chiffrement du mot de passe en SHA-256

$hashed_password = hash('sha256', $password);

// Ajout de l'utilisateur

try {
    $query = $pdo->prepare('INSERT INTO users (name, password, is_admin, email, created_at) VALUES (?, ?, ?, ?, ?)');
    $query->execute([$name, $hashed_password, $is_admin, '', date('d/m/Y H:i:s')]);
    echo "Utilisateur ajouté à la base de données\n";

} catch (PDOException $e) {
    $message = "Erreur MySQL, utilisateur non ajouté, plus d'informations dans " . ERROR_LOG_FILE . "\n";
    echo $message;
    $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans ' . ERROR_LOG_FILE . "\n";
    file_put_contents(ERROR_LOG_FILE, $error, FILE_APPEND);


}

?>    