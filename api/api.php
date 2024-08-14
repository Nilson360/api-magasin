<?php

use config\Database;
use models\Store;

include_once '../config/Database.php';
include_once '../models/Store.php';

$database = new Database();
$db = $database->getConnection();
$store = new Store($db);

$request_method = $_SERVER["REQUEST_METHOD"];

// Vérification du token pour les requêtes protégées
$protected_routes = ['GET', 'POST', 'PUT', 'DELETE'];
if (in_array($request_method, $protected_routes)) {
    //var_dump($_SERVER['HTTP_AUTHORIZATION']);die();
    //echo "token: ".$_SERVER['HTTP_AUTHORIZATION']."\n";
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        //error_log('Authorization header not found');
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized, token not found']);
        exit;
    }

    // Suppression de 'Bearer ' de l'en-tête Authorization
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    //$token = $_SERVER['HTTP_AUTHORIZATION'];
    //error_log('Received token: ' . $token); // Log pour déboguer

    if (!validateToken($db, $token)) {
       // error_log('Token validation failed');
        http_response_code(401);
        echo json_encode(['message' => 'Invalid token or expired token']);
        exit;
    }
}

// Gestions des différentes requêtes.
switch ($request_method) {
    case 'GET':
        handleGetRequest($store);
        break;
    case 'POST':
        handlePostRequest($store);
        break;
    case 'PUT':
        handlePutRequest($store);
        break;
    case 'DELETE':
        handleDeleteRequest($store);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Unauthorized method"));
}

/**
 * Gestion de la validation du token
 */
function validateToken($db, $token) {
    //error_log("Validating token: " . $token); // Log pour déboguer
    $query = "SELECT * FROM tokens WHERE token = :token AND expires_at >= NOW() LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $is_valid = $stmt->rowCount() > 0;
   // error_log("Token validation result: " . ($is_valid ? "Valid" : "Invalid")); // Log pour déboguer
    return $is_valid;
}

/**
 * Gère les requêtes GET pour récupérer les magasins.
 */
function handleGetRequest($store) {
    // Obtient les filtres et les critères de tri s'ils sont fournis.
    $filter = isset($_GET['filter']) ? $_GET['filter'] : "";
    $sort = isset($_GET['sort']) ? $_GET['sort'] : "";

    // Appelle la méthode read du modèle Store pour obtenir les magasins.
    $stmt = $store->read($filter, $sort);
    $num = $stmt->rowCount();

    if ($num > 0) {
        // Initialise le tableau de réponse.
        $stores_arr = array();
        $stores_arr["magasins"] = array();

        // Parcourt les résultats et les ajoute au tableau.
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $store_item = array(
                "id" => $id,
                "name" => $name,
                "location" => $location,
                "description" => $description,
            );
            array_push($stores_arr["magasins"], $store_item);
        }
        // Réponse avec le code 200 et les magasins trouvés.
        http_response_code(200);
        echo json_encode($stores_arr);
    } else {
        // Réponse avec le code 404 si aucun magasin n'est trouvé.
        http_response_code(404);
        echo json_encode(array("message" => "Aucun magasin trouvé."));
    }
}

/**
 * Gère les requêtes POST pour créer un nouveau magasin.
 */
function handlePostRequest($store) {
    // Obtient les données de la requête.
    $data = json_decode(file_get_contents("php://input"));

    // Vérifie que toutes les données nécessaires sont présentes.
    if (!empty($data->name) && !empty($data->location) && !empty($data->description)) {
        // Définit les propriétés du magasin à partir des données.
        $store->name = $data->name;
        $store->location = $data->location;
        $store->description = $data->description;

        // Tente de créer le magasin et renvoie une réponse appropriée.
        if ($store->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Magasin créé."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Impossible de créer le magasin."));
        }
    } else {
        // Réponse avec le code 400 si les données sont incomplètes.
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes."));
    }
}

/**
 * Gère les requêtes PUT pour mettre à jour un magasin existant.
 */
function handlePutRequest($store) {
    // Obtient les données de la requête.
    $data = json_decode(file_get_contents("php://input"));

    // Vérifie que toutes les données nécessaires sont présentes.
    if (!empty($data->id) && !empty($data->name) && !empty($data->location) && !empty($data->description)) {
        // Définit les propriétés du magasin à partir des données.
        $store->id = $data->id;
        $store->name = $data->name;
        $store->location = $data->location;
        $store->description = $data->description;

        // Tente de mettre à jour le magasin et renvoie la réponse appropriée.
        if ($store->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Magasin mis à jour."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Impossible de mettre à jour le magasin."));
        }
    } else {
        // Réponse avec le code 400 si les données sont incomplètes.
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes."));
    }
}

/**
 * Gère les requêtes DELETE pour supprimer un magasin existant.
 */
function handleDeleteRequest($store) {
    // Obtient les données de la requête.
    $data = json_decode(file_get_contents("php://input"));

    // Vérifie que l'ID du magasin est présent.
    if (!empty($data->id)) {
        // Définit l'ID du magasin à partir des données.
        $store->id = $data->id;

        // Tente de supprimer le magasin et renvoie la réponse appropriée.
        if ($store->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Magasin supprimé."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Impossible de supprimer le magasin."));
        }
    } else {
        // Réponse avec le code 400 si les données sont incomplètes.
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes."));
    }
}
