# PHP Simple Router API

Une API RESTful légère construite avec un router PHP personnalisé, inspiré de Laravel mais sans dépendances externes.

## 📋 Table des matières

- [Installation](#installation)
- [Configuration](#configuration)
- [Structure](#structure)
- [Utilisation](#utilisation)
- [Points d'accès (Endpoints)](#points-daccès)
- [Middleware](#middleware)
- [Exemples](#exemples)
- [Sécurité](#sécurité)
- [Contribution](#contribution)

## 🚀 Installation

1. Clonez le répertoire :
```bash
git clone https://votre-repo/php-simple-router.git
cd php-simple-router
```

2. Configurez votre serveur web pour pointer vers le dossier public.

3. Assurez-vous que mod_rewrite est activé si vous utilisez Apache.

## ⚙️ Configuration

### Configuration du serveur Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 📁 Structure

```
project/
├── public/
│   └── index.php
├── src/
│   ├── Controllers/
│   ├── Middleware/
│   └── Routes/
└── Route.php
```

## 💻 Utilisation

### Définition des routes

```php
<?php
require_once 'Route.php';

// Routes simples
Route::get('users', [UserController::class, 'index']);
Route::get('users/{id}', [UserController::class, 'show']);

// Routes groupées avec préfixe et middleware
Route::group(['prefix' => 'api', 'middleware' => [AuthMiddleware::class]], function() {
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});

Route::dispatch();
```

## 🛣️ Points d'accès

| Méthode | URI | Action | Description |
|---------|-----|--------|-------------|
| GET | `/users` | UserController@index | Liste tous les utilisateurs |
| GET | `/users/{id}` | UserController@show | Affiche un utilisateur spécifique |
| POST | `/api/users` | UserController@store | Crée un nouvel utilisateur |
| PUT | `/api/users/{id}` | UserController@update | Met à jour un utilisateur |
| DELETE | `/api/users/{id}` | UserController@destroy | Supprime un utilisateur |

## 🔒 Middleware

### Création d'un middleware

```php
class AuthMiddleware extends Middleware {
    public function handle(): bool {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader)) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Authentication required']);
            return false;
        }
        return true;
    }
}
```

### Application du middleware

```php
Route::group(['middleware' => [AuthMiddleware::class]], function() {
    // Routes protégées
});
```

## 📝 Exemples

### Exemple de contrôleur

```php
class UserController {
    public function index() {
        return json_encode(['message' => 'Liste des utilisateurs']);
    }

    public function show($id) {
        return json_encode(['message' => "Affichage utilisateur $id"]);
    }
}
```

### Exemple de requête

```bash
# Obtenir la liste des utilisateurs
curl -X GET http://votre-api/users

# Créer un nouvel utilisateur
curl -X POST http://votre-api/api/users \
  -H "Authorization: Bearer votre-token" \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com"}'
```

## 🔐 Sécurité

- Tous les endpoints sous `/api` sont protégés par authentification
- Les jetons JWT doivent être envoyés dans l'en-tête Authorization
- La validation des entrées est effectuée dans les contrôleurs
- Les réponses CORS sont gérées automatiquement

## 🤝 Contribution

1. Forkez le projet
2. Créez votre branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Poussez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## ✨ Fonctionnalités

- Routage RESTful
- Middleware personnalisable
- Gestion des paramètres d'URL
- Groupement de routes
- Préfixes de routes
- Réponses JSON
- Gestion des erreurs HTTP
- Support CORS

## 🔧 Configuration requise

- PHP 8.0 ou supérieur
- Module Apache mod_rewrite (si utilisation d'Apache)
- Serveur web compatible (Apache, Nginx, etc.)
