<?php

define('ERROR_LOG_FILE', 'errors.log');

// Copie de la fonction de l'exercice précédent

function connect_db(string $host, string $username, string $passwd, string $port, string $db){
    
try {
    $connection = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $username, $passwd);
    return $connection;

} catch (PDOException $e) {
        $error = 'ERREUR PDO : ' . $e->getMessage() . ' Erreur de connexion à la base de données' . ERROR_LOG_FILE . "\n";
        echo $error;
        file_put_contents(ERROR_LOG_FILE, $error, FILE_APPEND);
        return null;
    }
}

// Vérification des paramètres

if ($argc < 6) {

    $message = "Paramètres incorrects ! Utilisation : php connect_db.php host username password port db\n";

    echo $message;
    // Enregister l'erreur dans le fichier de ERRO_LOG_FILE
    
    file_put_contents(ERROR_LOG_FILE, $message, FILE_APPEND);
    exit(1);
}

// Récupération des arguments

        $host     = $argv[1];
        $username = $argv[2];
        $passwd   = $argv[3];
        $port     = $argv[4];
        $db       = $argv[5];

// Tentative de connexion

$connection = connect_db($host, $username, $passwd, $port, $db);

    // Affichage du résultat
    if ($connection) {
        echo "Connexion à la base de données succès\n";
    } else {
        $message = "Erreur de connexion à la base de données\n";
        echo $message;
        file_put_contents(ERROR_LOG_FILE, $message, FILE_APPEND);
}






























