<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use config\Database;

include_once 'config/Database.php';

// Créer une instance de la base de données et obtenir une connexion
$database = new Database();
$db = $database->getConnection();

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        registerUser($db);
    } elseif ($action === 'login') {
        loginUser($db);
    } else {
        http_response_code(405);
        echo json_encode(array("message" => "Invalid action."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}

/**
 * Fonction pour enregistrer un nouvel utilisateur et générer un token
 */
function registerUser($db) {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        // Nettoyer les entrées utilisateur
        $username = htmlspecialchars(strip_tags($_POST['username']));
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Préparer la requête SQL pour insérer un nouvel utilisateur
        $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            // Récupérer l'ID du nouvel utilisateur
            $user_id = $db->lastInsertId();

            // Générer un token pour le nouvel utilisateur
            $token = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

            // Stocker le token dans la base de données
            $insert_query = "INSERT INTO tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $user_id);
            $insert_stmt->bindParam(':token', $token);
            $insert_stmt->bindParam(':expires_at', $expires_at);

            if ($insert_stmt->execute()) {
                echo json_encode(array("message" => "User registered successfully.", "token" => $token, "expires_at" => $expires_at));
            } else {
                echo json_encode(array("message" => "User registered but failed to generate token."));
            }
        } else {
            echo json_encode(array("message" => "Failed to register user."));
        }
    } else {
        echo json_encode(array("message" => "Username and password are required."));
    }
}

/**
 * Fonction pour authentifier un utilisateur et générer un token
 */
function loginUser($db) {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        // Nettoyer les entrées utilisateur
        $username = htmlspecialchars(strip_tags($_POST['username']));
        $password = htmlspecialchars(strip_tags($_POST['password']));

        // Préparer la requête SQL pour vérifier les identifiants de l'utilisateur
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
                // Générer un token simple
                $token = bin2hex(random_bytes(16));
                $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

                // Stocker le token
                $insert_query = "INSERT INTO tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->bindParam(':user_id', $user_id);
                $insert_stmt->bindParam(':token', $token);
                $insert_stmt->bindParam(':expires_at', $expires_at);

                if ($insert_stmt->execute()) {
                    error_log("Token generated: " . $token); // Log pour déboguer
                    http_response_code(200);
                    echo json_encode(array("token" => $token, "expires_at" => $expires_at));
                } else {
                    error_log("Failed to store token");
                    http_response_code(500);
                    echo json_encode(array("message" => "Unable to store the token."));
                }
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Invalid username or password."));
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid username or password."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Incomplete data."));
    }
}
