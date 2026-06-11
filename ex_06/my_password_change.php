<?php

function my_password_change(PDO $bdd, string $email, string $newpassword): void {
    // Implémentation de la fonction de changement de mot de passe

    // Vérification de l'existence de l'utilisateur

    $query = $bdd->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);
    
    // modifie l'utilisateur associé à l'email en changeant son mot de passe avec password_hash
        
    if ($user) {
            $hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);
            $updateQuery = $bdd->prepare("UPDATE users SET password = ? WHERE email = ?");
            $updateQuery->execute([$hashedPassword, $email]);
            echo "Mot de passe mis à jour avec succès.\n";
            
        } 

        // verifier le mot de passe avec password_verify

        if (password_verify($newpassword, $user['password'])){  
            echo "Le nouveau mot de passe ne peut pas être le même que l'ancien.\n";
             

        } else if ($newpassword === $user['password']) {
            echo "Le nouveau mot de passe doit être différent de l'ancien.\n";
            
        }
     
// si l'users n'existe pas

    if (!isset($user)) {
       throw new Exception("Utilisateur non trouvé : " . $newpassword);
    
       // si le nouveau mot de passe est vide

    } else if (empty($newpassword)) {
        echo "Le nouveau mot de passe ne peut pas être vide.\n";

        
    } else {
        echo "Mot de passe mis à jour avec succès.\n";
        
    }
}

?>  