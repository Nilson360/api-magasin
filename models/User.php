<?php

namespace models;

use PDO;
use PDOException;

class User
{
    private $conn;
    private $table = 'users';
    public $id;
    public $username;
    public $password;

    /**
     * Constructeur de la classe User
     *
     * @param PDO $db Connexion à la base de données
     */
    public function __construct($db)
    {
        // Initialiser la connexion à la base de données
        $this->conn = $db;
    }

    /**
     * Méthode pour vérifier les identifiants de l'utilisateur
     *
     * @return bool Retourne true si les identifiants sont corrects, sinon false
     */
    public function login()
    {
        // Préparer la requête SQL pour sélectionner l'utilisateur avec le nom d'utilisateur donné
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);

        // Lier le paramètre de nom d'utilisateur à la requête préparée
        $stmt->bindParam(':username', $this->username);

        // Exécuter la requête
        $stmt->execute();

        // Vérifier si un utilisateur correspondant a été trouvé
        if ($stmt->rowCount() > 0) {
            // Récupérer les données de l'utilisateur
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];

            // Vérifier que le mot de passe fourni correspond au mot de passe haché stocké
            return password_verify($this->password, $row['password']);
        }

        // Retourner false si aucun utilisateur correspondant n'a été trouvé ou si le mot de passe est incorrect
        return false;
    }
}
