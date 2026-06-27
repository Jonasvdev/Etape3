<?php


define('ERROR_LOG_FILE', 'errors.log');
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', '3306');
define('DB_NAME', 'bdd');

// Fonction de connexion
function connect_db(string $host, string $username, string $passwd, int $port, string $bdd) {
    
try {
        $connection = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $bdd, $username, $passwd);
        return $connection;

    } catch (PDOException $e) {
        $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans ' . ERROR_LOG_FILE . "\n"; 
        echo $error;
        file_put_contents(ERROR_LOG_FILE, $error, FILE_APPEND);
        return null;
    }
}

// Connexion à la base de données

$pdo = connect_db(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_PORT, DB_NAME);

if (!$pdo) {
    echo "Erreur de connexion à la base de données\n";
    exit(1);
} else {

    echo "Connexion à la base de données réussie\n";
}

// Vérification des paramètres

if ($argc > 1 && $argc !== 4) {
    echo "Paramètres incorrects ! Utilisation: php dump_users.php [valeur du filtre exacte]\n";
    exit(1);
}

// Construction de la requête

try {

    // Sans filtre : afficher toute la table

    if (!empty($argv[1]) && !empty($argv[2]) && !empty($argv[3])) {

        $query = $pdo->prepare('SELECT * FROM users');
        $query->execute();
        $users = $query->fetchAll(PDO::FETCH_ASSOC);

    } else if ($argc == 1) { 
        
        $filter = $argv[1];
        $value  = $argv[2]; 
        $exact  = $argv[3];


    // Vérification du filtre password

        if ($filter === 'password') {
            fwrite(STDERR, "Ne tentez pas de filtrer le mot de passe, cela n'est pas possible\n");
            exit(1);
        }

        // Recherche exacte ou non

        if ($exact === 'true') {
            $query = $pdo->prepare('SELECT * FROM users WHERE ' . $filter . ' = ?');
            $query->execute([$value]);
            
        } else {
            $query = $pdo->prepare('SELECT * FROM users WHERE ' . $filter . ' LIKE ?');
            $query->execute(['%' . $value . '%']);
        }
    }

    // Récupération et affichage des résultats

    // $users = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        echo "Aucun résultat ne correspond à votre recherche\n";
        exit(0);
    }


} catch (PDOException $e) {
    $message = "Erreur MySQL, plus d'informations dans " . ERROR_LOG_FILE . "\n";
    echo $message;
    $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans ' . ERROR_LOG_FILE . "\n";
    file_put_contents(ERROR_LOG_FILE, $error, FILE_APPEND);
}


























