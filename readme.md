# Documentation de l'API

#### Auteur: Nilson SIMAO
#### Github: https://www.github.com/Nilson360

## Introduction
Cette API permet de gérer une liste de magasins. Avec elle, vous pouvez :

- Récupérer la liste des magasins.
- Filtrer et trier les résultats.
- Ajouter de nouveaux magasins.
- Mettre à jour les magasins existants.
- Supprimer un magasin.

Pour accéder à certaines fonctionnalités, l'utilisateur doit s'authentifier et obtenir un token d'accès.

## Authentification et Instructions pour Utiliser l'API

### Inscription d'un nouvel utilisateur et génération de token
**Inscrit un nouvel utilisateur et génère un token.**

### Vous pouvez aussi créer un utiliser via une interface graphique, accédez la page index.php à la racine du projet.

- **Request Body:**
    - `username` (string) : Le nom d'utilisateur.
    - `password` (string) : Le mot de passe.

- **Exemples:**
    - Requête pour inscrire un nouvel utilisateur et obtenir un token :
      ```bash
      curl -X POST "http://localhost/api-magasin/user_auth.php" -H "Content-Type: application/x-www-form-urlencoded" -d 'action=register&username=myUsername&password=myPassword'
      ```

- **Response:**
  ```json
  {
    "message": "User registered successfully.",
    "token": "2dbba7ec628019360542a51df5a32eb0",
    "expires_at": "2023-12-31 23:59:59"
  }
  ```
  
## Endpoints de l'API

### GET /api/stores
**Récupère la liste des magasins.**

- **Headers:**
    - `Authorization: Bearer <token>` : Token d'authentification.

- **Query Parameters:**
    - `filter` (optionnel) : Filtrer par nom ou localisation du magasin (ex : `filter=Store`).
    - `sort` (optionnel) : Trier par un champ (ex : `sort=name`).

- **Exemples:**
    - Requête pour récupérer tous les magasins :
      ```bash
      curl -X GET "http://localhost/api-magasin/api/stores" -H "Authorization: Bearer 2dbba7ec628019360542a51df5a32eb0"
      ```

    - Requête avec filtre et tri :
      ```bash
      curl -X GET "http://localhost/api-magasin/api/stores?filter=Location&sort=name" -H "Authorization: Bearer 2dbba7ec628019360542a51df5a32eb0"
      ```

- **Response:**
  ```json
  {
    "magasins": [
      {
        "id": "1",
        "name": "Store 1",
        "location": "Location 1",
        "description": "Description 1"
      },
      {
        "id": "2",
        "name": "Store 2",
        "location": "Location 2",
        "description": "Description 2"
      }
    ]
  }
  ```

### POST /api/stores
**Ajoute un nouveau magasin.**

- **Headers:**
    - `Authorization: Bearer <token>` : Token d'authentification.

- **Request Body:**
    - `name` (string) : Le nom du magasin.
    - `location` (string) : L'adresse du magasin.
    - `description` (string) : La description du magasin.

- **Exemples:**
    - Requête pour ajouter un nouveau magasin :
      ```bash
      curl -X POST "http://localhost/api-magasin/api/stores" -H "Authorization: Bearer 2dbba7ec628019360542a51df5a32eb0" -H "Content-Type: application/json" -d '{
        "name": "Test magasin",
        "location": "3 rue de la lune, nouvelle galaxie",
        "description": "Mon premier magasin"
      }'
      ```

- **Response:**
  ```json
  {
    "message": "Magasin cree."
  }
  ```

### PUT /api/stores
**Met à jour un magasin existant.**

- **Headers:**
    - `Authorization: Bearer <token>` : Token d'authentification.

- **Request Body:**
    - `id` (int) : L'identifiant du magasin.
    - `name` (string) : Le nom du magasin.
    - `location` (string) : L'adresse du magasin.
    - `description` (string) : La description du magasin.

- **Exemples:**
    - Requête pour mettre à jour un magasin :
      ```bash
      curl -X PUT "http://localhost/api-magasin/api/stores" -H "Authorization: Bearer 2dbba7ec628019360542a51df5a32eb0" -H "Content-Type: application/json" -d '{
        "id": 1,
        "name": "Updated Store Name",
        "location": "Updated Location",
        "description": "Updated Description"
      }'
      ```

- **Response:**
  ```json
  {
    "message": "Magasin mis à jour."
  }
  ```

### DELETE /api/stores
**Supprime un magasin.**

- **Headers:**
    - `Authorization: Bearer <token>` : Token d'authentification.

- **Request Body:**
    - `id` (int) : L'identifiant du magasin.

- **Exemples:**
    - Requête pour supprimer un magasin :
      ```bash
      curl -X DELETE "http://localhost/api-magasin/api/stores" -H "Authorization: Bearer 2dbba7ec628019360542a51df5a32eb0" -H "Content-Type: application/json" -d '{
        "id": 1
      }'
      ```

- **Response:**
  ```json
  {
    "message": "Magasin supprimé."
  }
  ```