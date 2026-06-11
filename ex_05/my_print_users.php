<?php

// fonction qui prend en parametre une instance PDO et ET UN NOMBRE VARIABLE D'RNTIRRS

function my_print_users(PDO $pdo, int ...$ids) : bool{

    $querry = $pdo->prepare("SELECT * FROM users WHERE id = ?");

    foreach($ids as $id) {

        $querry->execute([$id]);
        $users = $querry->fetch(PDO::FETCH_ASSOC);
        echo $users['name'] . "\n";
    }

    if (isset($users['name'])) {
        return true;
    } else {
        return false;
    } 

    // verifier si les ids sont vides

    if (empty($ids)) {
        echo "Aucun Id fourni.\n";
        return false;

    // verifier si les ids sont nuls
   
    } elseif (count($ids) === 0) {
        echo "Aucun ID fourni.\n";
        return false;


   
    // verifier si les ids sont des entiers

    if(!is_int($ids)) {
        // echo "Identifiant invalide.\n";
        throw new Exception("Identifiant invalide : " ); 

    }
}
}
    








?>