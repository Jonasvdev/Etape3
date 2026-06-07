<?php

// Une fonction qui etabli une connexion à la base de données et retourne l'objet PDO

define('ERROR_LOG_FILE', '/tmp/error.log');

function connect_db(string $host, string $username, string $passwd, int $port , string $dbname ): mixed {

// Renvoie la ressource de connexion à la base de données ou une erreur en cas d'échec de la connexion

  try {
        $connection = new PDO("mysql:host=" . $host . ";port=" . $port . ";dbname=" . $dbname, $username, $passwd);
        
        return $connection;

    } catch (PDOException $e) {

        $error = 'ERREUR PDO: ' . $e->getMessage() . 'stocke dans' . ERROR_LOG_FILE ."\n";

        file_put_contents(ERROR_LOG_FILE, $error, FILE_APPEND);

        echo "Connection échouée: " . $e->getMessage();

        return null;
        
}

}




?>

















