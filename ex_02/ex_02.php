<?php

function my_password_hash(string $password): array {
    
    // Générer 16 octets aléatoires

    $salt = bin2hex(random_bytes(16));

    // Hacher le mot de passe avec le sel

    $hash = crypt($password, '$6$' . $salt . '$');

    return [
        'hash' => $hash,
        'salt' => $salt
    ];
}

function my_password_verify(string $password, string $salt, string $hash): bool{
    
    // True:  Le mot de passe en clair correspond au hash → mot de passe correct

    // False: Le mot de passe en clair ne correspond pas au hash → mot de passe incorrect
    
    $newHash = crypt($password, '$6$' . $salt . '$');
    return $newHash === $hash;
}