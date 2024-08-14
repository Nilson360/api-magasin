<?php
namespace models;
use PDO;
use PDOException;

class Store
{
    // Propriétés privées pour la connexion à la base de données et le nom de la table
    private $conn;
    private $table = 'stores';

    // Propriétés publiques pour les champs de la table 'stores'
    public $id;
    public $name;
    public $location;
    public $description;

    // Constructeur pour initialiser la connexion à la base de données
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     *  Méthode pour lire les enregistrements de la table stores avec des options de filtrage et de tri
     */
    public function read($filter = "", $sort = "")
    {
        // Construction de la requête SQL de base
        $query = "SELECT * FROM $this->table";

        // Ajout de conditions de filtrage si un filtre est fourni
        if ($filter) {
            $query .= " WHERE name LIKE :filter OR location LIKE :filter";
        }

        // Ajout de critères de tri si un critère est fourni
        if ($sort) {
            $query .= " ORDER BY $sort";
        }

        // Préparation de la requête SQL
        $stmt = $this->conn->prepare($query);

        // Liaison des paramètres de filtrage s'ils sont fournis
        if ($filter) {
            $filter = "%$filter%";
            $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Méthode pour créer un nouvel enregistrement dans la table stores
     */
    public function create()
    {
        // Construction de la requête SQL d'insertion
        $query = "INSERT INTO " . $this->table . " SET name=:name, location=:location, description=:description";

        // Préparation de la requête SQL
        $stmt = $this->conn->prepare($query);

        // Nettoyage des données pour éviter les injections SQL
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Liaison des paramètres
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':description', $this->description);

        return $stmt->execute();
    }

    /**
     * Méthode pour mettre à jour un enregistrement existant dans la table 'stores'
     */
    public function update()
    {
        // Construction de la requête SQL de mise à jour
        $query = "UPDATE " . $this->table . " SET name=:name, location=:location, description=:description WHERE id=:id";

        // Préparation de la requête SQL
        $stmt = $this->conn->prepare($query);

        // Nettoyage des données pour éviter les injections SQL
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Liaison des paramètres
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);

        // Exécution de la requête et retour du résultat
        return $stmt->execute();
    }

    /**
     * Méthode pour supprimer un enregistrement de la table 'stores'
     */
    public function delete()
    {
        // Construction de la requête SQL de suppression
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";

        // Préparation de la requête SQL
        $stmt = $this->conn->prepare($query);

        // Nettoyage de l'ID pour éviter les injections SQL
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Liaison du paramètre ID
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }
}
