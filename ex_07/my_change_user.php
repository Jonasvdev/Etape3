<?php

function my_change_user(PDO $bdd, string...$name) {

    // Implémentation de la fonction de modification d'utilisateur

 try {
        // Vérification de l'existence de l'utilisateur

    $query = $bdd->prepare("SELECT * FROM users WHERE name = ?"); 
    $query->execute([$name]);
    $user = $query->fetch(PDO::FETCH_ASSOC); 

        foreach ($name as $name) {

    // Transformation de nom , exemple "toto" => "Toto"

        $name = ucfirst(strtolower($name)) . "42";
        }

     // Mise à jour des données de l'utilisateur  

        $updateQuery = $bdd->prepare("UPDATE users SET name = ? WHERE name = ?");
        $updateQuery->execute([$name]);
        $updateQuery->fetch(PDO::FETCH_ASSOC);

        echo "Utilisateur modifié avec succès : " . $name . "\n";

    if(isset($name[($user)])){
        echo "Utilisateur trouvé : " . $user['name'] . " \n";
    } 
     else if
            (!isset($name[($user)])) {
                throw new Exception("Utilisateur non trouvé : " );
            } 
            
   
}
    finally {
        echo "Bonne chance avec la base de données utilisateur ! " . "\n";
    }
}   


?>