<?php

class UsersManager
{
    private $pdo;
    private string $host;
    private string $username;
    private string $passwd;
    private $port;
    private $bdd; 
    private $currentUser = null;

    // Commandes disponibles selon le rôle
    private array $adminCommands = ['adduser', 'modifyuser', 'dump', 'deluser', 'makeactive', 'makeinactive', 'help', 'logout', 'quit'];
    private array $userCommands  = ['modifyuser', 'dump', 'help', 'logout', 'quit'];

    public function __construct(string $host, string $username, string $passwd, int $port, string $bdd){
        
        $this->host     = $host;
        $this->username = $username;
        $this->passwd   = $passwd;
        $this->port     = $port;
        $this->bdd      = $bdd;
    }

    // Connexion à la base de données
    public function connect(){

        try {
            $this->pdo = new PDO('mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->bdd,$this->username,$this->passwd);
            return $this; 

        } catch (PDOException $e) {
            $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans errors.log' . "\n";
            echo $error;
            file_put_contents('errors.log', $error, FILE_APPEND);
            return null;
        }
    }

    // Affiche l'invite de commande
    private function prompt(){
        $name = $this->currentUser ? $this->currentUser['name'] : 'guest';
        echo  $name ;
    }

    // Authentification de l'utilisateur
    private function login(){
        $attempts = 0;
        $maxAttempts = 5;

        while (true) {

            echo 'Nom d\'utilisateur : ';
            $name = trim(fgets(STDIN));

            echo 'Mot de passe : ';
            $password = trim(fgets(STDIN));

            // Vérification si le compte existe

            $query = $this->pdo->prepare('SELECT * FROM users WHERE name = ?');
            $query->execute([$name]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            // Compte bloqué

            if ($user && $user['is_active'] == 0) {
                echo "Pour des raisons de sécurité, votre compte a été bloqué.\nVeuillez contacter votre responsable pour le débloquer.\n";
                continue;
            }

            // Vérification du mot de passe

            if ($user && hash('sha256', $password) === $user['password']) {
                $this->currentUser = $user;
                $role = $user['is_admin'] ? 'ADM' : 'NOT ADM';
                echo 'Bienvenue ' . $user['name'] . ', vous êtes maintenant connecté en tant que ' . $role . "\n";
                return;
            }

            // Mauvais identifiants

            $attempts++;
            $remaining = $maxAttempts - $attempts;

            if ($attempts >= $maxAttempts) {

                // Désactivation du compte

                if ($user) {
                    $query = $this->pdo->prepare('UPDATE users SET is_active = 0 WHERE name = ?');
                    $query->execute([$name]);
                }
                echo "Pour des raisons de sécurité, le compte a été bloqué.\n";
                $attempts = 0;
            } else {
                echo 'Identifiants invalides. ' . $remaining . ' tentative(s) restante(s)' . "\n";
            }
        }
    }

    // Boucle principale
    public function start(){
        if (!$this->pdo) {
            return;
        }

        $this->login();

        while (true) {
            $this->prompt();
            $input = trim(fgets(STDIN));
            $parts = explode(' ', $input);
            $command = $parts[0];
            $args = array_slice($parts, 1);

            // Vérification des droits
            $availableCommands = $this->currentUser['is_admin'] ? $this->adminCommands : $this->userCommands;

            if (!in_array($command, $availableCommands)) {
                echo "'" . $command . "' : Commande inconnue\n";
                continue;
            }

            switch ($command) {
                case 'adduser':
                    $this->addUser($args);
                    break;
                case 'modifyuser':
                    $this->modifyUser($args);
                    break;
                case 'dump':
                    $this->dump($args);
                    break;
                case 'deluser':
                    $this->delUser($args);
                    break;
                case 'makeactive':
                    $this->setActive($args, 1);
                    break;
                case 'makeinactive':
                    $this->setActive($args, 0);
                    break;
                case 'help':
                    $this->help($args);
                    break;
                case 'logout':
                    $this->currentUser = null;
                    $this->login();
                    break;
                case 'quit':
                    $this->currentUser = null;
                    echo '$guest@usermanager> ';
                    return;
            }
        }
    }

    // Ajouter un utilisateur
    private function addUser($args){

        if (count($args) < 4) {
            echo "\tParamètres incorrects. adduser <nom> <mot_de_passe> <conf_mot_de_passe> <rôle>\n";
            return;
        }

        [$name, $password, $passwordConf, $role] = $args;

        if ($password !== $passwordConf) {
            echo "Mots de passe incorrects\n";
            return;
        }

        try {
            $query = $this->pdo->prepare('INSERT INTO users (name, password, is_admin, email, created_at) VALUES (?, ?, ?, ?, ?)');
            $query->execute([$name, hash('sha256', $password), $role === 'ADM' ? 1 : 0, '', date('Y-m-d')]);
            echo "Utilisateur ajouté à la base de données\n";

        } catch (PDOException $e) {
            $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans errors.log' . "\n";
            echo "Erreur MySQL, plus d'informations dans errors.log\n";
            file_put_contents('errors.log', $error, FILE_APPEND);
        }
    }

    // Modifier un utilisateur
    private function modifyUser($args)
    {
        if (count($args) < 3) {
            echo "\tParamètres incorrects. modifyuser <id> nom|mot_de_passe|rôle <nouvelle_valeur> [<conf_valeur>]\n";
            return;
        }

        [$id, $key, $value] = $args;

        // Vérification si l'utilisateur existe

        $query = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $query->execute([$id]); 
        $user = $query->fetch(PDO::FETCH_ASSOC); 

        if (!$user) {
            echo 'Utilisateur n° ' . $id . " inconnu !\n";
            return;
        }

        // NOT ADM ne peut modifier que ses propres infos

        if (!$this->currentUser['is_admin'] && $user['id'] != $this->currentUser['id']) {
            echo "'" . 'modifyuser' . "' : Commande inconnue\n";
            return;
        }

        // Modification du mot de passe

        if ($key === 'mot_de_passe') {
            if (count($args) < 4) {
                echo "\tParamètres incorrects. modifyuser <id> mot_de_passe <nouvelle_valeur> <conf_valeur>\n";
                return;
            }
            if ($value !== $args[3]) {
                echo "Mots de passe incorrects\n";
                return;
            }
            $value = hash('sha256', $value);
            $key = 'password';
        }

        if ($key === 'nom')  $key = 'name';
        if ($key === 'rôle') $key = 'is_admin';

        try {
            $query = $this->pdo->prepare('UPDATE users SET ' . $key . ' = ? WHERE id = ?');
            $query->execute([$value, $id]);
            echo "Utilisateur modifié\n";

        } catch (PDOException $e) {
            $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans errors.log' . "\n";
            echo "Erreur MySQL, plus d'informations dans errors.log\n";
            file_put_contents('errors.log', $error, FILE_APPEND);
        }
    }

    // Afficher les utilisateurs
    private function dump($args){

        try {
            if (empty($args)) {
                $query = $this->pdo->prepare('SELECT * FROM users');
                $query->execute();
            } else {
                if (count($args) < 3) {
                    echo "\tParamètres incorrects. dump [<clé> <valeur> <exact>]\n";
                    return;
                }

                [$filter, $value, $exact] = $args;

                if ($filter === 'password') {
                    fwrite(STDERR, "Ne tentez pas de filtrer le mot de passe, cela n'est pas possible\n");
                    return;
                }

                if ($exact === 'true') {
                    $query = $this->pdo->prepare('SELECT * FROM users WHERE ' . $filter . ' = ?');
                    $query->execute([$value]);
                } else {
                    $query = $this->pdo->prepare('SELECT * FROM users WHERE ' . $filter . ' LIKE ?');
                    $query->execute(['%' . $value . '%']);
                }
            }

            $users = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($users)) {
                echo "Aucun résultat ne correspond à votre recherche\n";
                return;
            }
            

            foreach ($users as $user) {
                $status = $user['is_active'] ? 'active' : 'inactive';
                echo '- [' . $user['id'] . '] ' . $user['name'] . ' ' . $user['is_admin'] . ' (' . $status . ')' . "\n";
            }

        } catch (PDOException $e) {
            $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans errors.log' . "\n";
            echo "Erreur MySQL, plus d'informations dans errors.log\n";
            file_put_contents('errors.log', $error, FILE_APPEND);
        }
    }

    // Supprimer un utilisateur
    private function delUser($args) {

        if (empty($args)) {
            echo "\tParamètres incorrects. deluser <id>\n";
            return;
        }

        $id = $args[0];

        $query = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $query->execute([$id]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if (!$user) {

            echo 'Utilisateur n° ' . $id . " inconnu !\n";
            return;
        }

        echo 'Êtes-vous sûr ? [o/N] : ';
        $confirm = trim(fgets(STDIN));

        if ($confirm === 'o' || $confirm === 'O') {
            try {
                $query = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
                $query->execute([$id]);
                echo "Utilisateur supprimé\n";

            } catch (PDOException $e) {

                $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans errors.log' . "\n";
                echo "Erreur MySQL, plus d'informations dans errors.log\n";
                file_put_contents('errors.log', $error, FILE_APPEND);
            }
        }
    }

    // Activer ou désactiver un utilisateur
    private function setActive($args, $status){

        if (empty($args)) {
            echo "\tParamètres incorrects. makeactive/makeinactive <id>\n";
            return;
        }

        $id = $args[0];

        $query = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $query->execute([$id]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo 'Utilisateur n° ' . $id . " inconnu !\n";
            return;
        }

        try {
            $query = $this->pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?');
            $query->execute([$status, $id]);
            echo "Utilisateur mis à jour\n";

        } catch (PDOException $e) {
            $error = 'ERREUR PDO : ' . $e->getMessage() . ' stocké dans errors.log' . "\n";
            echo "Erreur MySQL, plus d'informations dans errors.log\n";
            file_put_contents('errors.log', $error, FILE_APPEND);
        }
    }

    // Aide
    private function help($args){
        
        $commands = $this->currentUser['is_admin'] ? $this->adminCommands : $this->userCommands;

        $usage = [
            'adduser'    => 'adduser <nom> <mot_de_passe> <conf_mot_de_passe> <rôle>',
            'modifyuser' => 'modifyuser <id> nom|mot_de_passe|rôle <nouvelle_valeur> [<conf_valeur>]',
            'dump'       => 'dump [<clé> <valeur> <exact>]',
            'deluser'    => 'deluser <id>',
            'makeactive' => 'makeactive <id>',
            'makeinactive' => 'makeinactive <id>',
            'help'       => 'help [<commande>]',
            'logout'     => 'logout',
            'quit'       => 'quit'
        ];

        if (!empty($args) && isset($usage[$args[0]]) && in_array($args[0], $commands)) {
            echo $usage[$args[0]] . "\n";
        } else {
            foreach ($commands as $command) {
                echo '- ' . $command . "\n";
            }
        }
    }
}

// Exécution

$manager = new UsersManager("localhost", "dbuser", "dbpass", "dbport", "dbname");
$manager->connect()->start();







?>