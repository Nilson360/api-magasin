<?php

namespace config;
use PDO;
use PDOException;

class Database
{
    // Déclaration des propriétés privées pour les paramètres de connexion à la base de données
    private $host = "127.0.0.1";
    private $user = "root";
    private $pass = "";
    private $dbname = "magasins";
    // Déclaration d'une propriété publique pour la connexion PDO
    public $conn;

    // Méthode pour obtenir la connexion à la base de données
    public function getConnection(): ?PDO
    {
        // Initialisation de la connexion à null
        $this->conn = null;

        try {
            // Création d'une nouvelle connexion PDO avec les paramètres de connexion
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
            // Configuration des attributs PDO pour lancer des exceptions en cas d'erreur
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Configuration du jeu de caractères pour utiliser UTF-8
            $this->conn->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            echo 'Un problème est survenu: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
