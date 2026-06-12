<?php


function my_show_db(PDO $bdd, string $name) {

try {
    $name = htmlspecialchars($name); // Sécurisation de la variable $name pour éviter les injections SQL

    // Implémentation de la fonction d'affichage de la base de données

    
        $query = $bdd->query("SELECT * FROM users WHERE name = ?");
        $query->execute([$name ]);
        $users = $query->fetchAll(PDO::FETCH_ASSOC);

    if ($users['name'] === $_POST['name']){
        echo "Bienvenue " . $users['name'] . " !";
        yield $users['name'];  

    } else if(empty($name)) {
        echo "Aucun utilisateur trouvé avec le nom : " . $name;
        return null;

    } else if ($users['name'] !== $_POST['name']) {
        echo "Utilisateur non trouvé.";
        return null;
    }


} catch (PDOException $e) {
    echo "Erreur de syntaxe : " . $e->getMessage();
    return null;
}

}

?>