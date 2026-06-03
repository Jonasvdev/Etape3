<?php

function my_very_secure_hash(string $password): string {
    return md5($password); // Hache le mot de passe en une chaîne de 32 caractères hexadécimaux
}