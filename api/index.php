<?php

// Récupère et décompose la requête en segments
$request = explode('/', trim($_GET['request'], '/'));

 //var_dump($request[0]);die();

// Switch pour gérer différents endpoints basés sur le premier segment de la requête
switch ($request[0]) {
    case 'stores':
        include 'api.php';
        break;
    case 'auth':
        include 'auth.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found."));
}
