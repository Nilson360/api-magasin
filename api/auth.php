<?php

use config\Database;

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

// Obtenir la méthode de requête HTTP utilisée
$request_method = $_SERVER["REQUEST_METHOD"];

// Vérifier si la méthode de requête est POST
if ($request_method == 'POST') {
    // Appeler la fonction pour gérer la requête de connexion
    handleLoginRequest($db);
} else {
    // Répondre avec un code 405 si la méthode de requête n'est pas autorisée
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}

/**
 * Fonction pour gérer la requête de connexion
 */
function handleLoginRequest($db) {
    // Récupérer les données JSON envoyées dans le corps de la requête
    $data = json_decode(file_get_contents("php://input"));

    // Vérifier que le nom d'utilisateur et le mot de passe ne sont pas vides
    if (!empty($data->username) && !empty($data->password)) {
        // Nettoyer les données pour éviter les attaques XSS et les injections SQL
        $username = htmlspecialchars(strip_tags($data->username));
        $password = htmlspecialchars(strip_tags($data->password));

        // Préparer une requête SQL pour vérifier les identifiants de l'utilisateur
        $query = "SELECT id, password FROM users WHERE username = :username LIMIT 0,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Vérifier si un utilisateur correspondant a été trouvé
        if ($stmt->rowCount() > 0) {
            // Récupérer les données de l'utilisateur
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $row['id'];
            $hashed_password = $row['password'];

            // Vérifier que le mot de passe fourni correspond au mot de passe haché stocké
            if (password_verify($password, $hashed_password)) {
                // Générer un token d'authentification simple
                $token = bin2hex(random_bytes(16));
                $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

                // Stocker le token dans la base de données avec une date d'expiration
                $insert_query = "INSERT INTO tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->bindParam(':user_id', $user_id);
                $insert_stmt->bindParam(':token', $token);
                $insert_stmt->bindParam(':expires_at', $expires_at);
                if ($insert_stmt->execute()) {
                    //error_log("Token generated: " . $token);
                    http_response_code(200);
                    echo json_encode(array("token" => $token, "expires_at" => $expires_at));
                } else {
                   // error_log("Failed to store token");
                    http_response_code(500);
                    echo json_encode(array("message" => "Unable to store the token."));
                }
            } else {
                // Répondre avec une erreur si le mot de passe est invalide
                http_response_code(401);
                echo json_encode(array("message" => "Invalid username or password."));
            }
        } else {
            // Répondre avec une erreur si aucun utilisateur correspondant n'a été trouvé
            http_response_code(401);
            echo json_encode(array("message" => "Invalid username or password."));
        }
    } else {
        // Répondre avec une erreur si les données sont incomplètes
        http_response_code(400);
        echo json_encode(array("message" => "Incomplete data."));
    }
}
