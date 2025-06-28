<?php
// function_hash.php
// Script CLI pour générer un hash de mot de passe sécurisé en utilisant password_hash().

// --- Début du script ---

// Demande le mot de passe de manière interactive et sécurisée.
// L'utilisation de readline() évite que le mot de passe ne soit stocké
// dans l'historique du terminal.
$password = readline("Veuillez saisir le mot de passe à hasher : ");

// Vérifie si un mot de passe a bien été entré.
// trim() retire les espaces superflus au début et à la fin.
if (empty(trim($password))) {
    echo "Erreur : Aucun mot de passe fourni. Le script est arrêté." . PHP_EOL;
    exit(1); // Quitte le script avec un code d'erreur.
}

// Affiche un message de confirmation avant de hasher.
echo "Génération du hash pour votre mot de passe..." . PHP_EOL;

// Utilise la fonction native et sécurisée de PHP pour le hachage.
// PASSWORD_DEFAULT est une constante qui garantit l'utilisation de l'algorithme
// de hachage le plus fort et le plus récent disponible dans votre version de PHP (actuellement BCRYPT).
// Cette fonction gère automatiquement la génération d'un "sel" (salt) cryptographique.
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Affiche le résultat.
echo "------------------------------------------------------------" . PHP_EOL;
echo "Mot de passe hashé : " . $hashedPassword . PHP_EOL;
echo "------------------------------------------------------------" . PHP_EOL;
echo "Note : Ce hash peut être stocké en toute sécurité dans votre base de données." . PHP_EOL;

exit(0); // Quitte le script avec succès.
