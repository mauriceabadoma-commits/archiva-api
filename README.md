# Archiva — API REST (PHP + MySQL)

Backend du projet Archiva : authentification (sessions), gestion des CERs (CRUD), préférence de langue (cookie). Conçu pour tourner en local avec **XAMPP**.

## Installation avec XAMPP

1. [Téléchargez XAMPP](https://www.apachefriends.org/fr/index.html) et installez-le.
2. Copiez le dossier `archiva-api` entier dans le dossier `htdocs` de XAMPP :
   - Windows : `C:\xampp\htdocs\archiva-api`
   - macOS : `/Applications/XAMPP/htdocs/archiva-api`
3. Ouvrez le **Panneau de contrôle XAMPP**, démarrez **Apache** et **MySQL**.
4. Ouvrez `http://localhost/phpmyadmin` dans votre navigateur.
5. Cliquez sur l'onglet **SQL**, collez le contenu du fichier `database/schema.sql`, puis cliquez sur **Exécuter**.
   - Cela crée la base `archiva`, ses deux tables (`users`, `cers`), et insère les 7 CERs de démonstration.
6. Vérifiez que l'API répond : ouvrez `http://localhost/archiva-api/api/cers.php` dans votre navigateur. Vous devez voir une liste de CERs au format JSON.

## Configuration

Si votre MySQL a un mot de passe différent de celui par défaut de XAMPP (vide), modifiez `config/database.php` :

```php
define('DB_USER', 'root');
define('DB_PASS', 'votre_mot_de_passe');
```

## Structure du projet

```
archiva-api/
├── config/database.php         connexion PDO à MySQL
├── includes/
│   ├── cors.php                 autorise le frontend React
│   ├── session.php              démarre la session PHP
│   ├── response.php             helpers JSON (DRY)
│   └── auth_guard.php           vérifie qu'on est connecté
├── api/
│   ├── cers.php                 GET / POST / PUT / DELETE
│   ├── auth/
│   │   ├── register.php         inscription (+ connexion automatique)
│   │   ├── login.php             connexion
│   │   ├── logout.php            déconnexion
│   │   └── me.php                qui est connecté ?
│   └── preferences/
│       └── language.php          lit/écrit un cookie de langue
├── database/schema.sql           à importer dans phpMyAdmin
└── postman/Archiva-API.postman_collection.json
```

## Les routes de l'API

| Méthode | URL | Authentification | Description |
|---|---|---|---|
| GET | `/api/cers.php` | non | Liste tous les CERs (`?search=`, `?level=`) |
| GET | `/api/cers.php?mine=1` | **oui** | Liste seulement les CERs de l'utilisateur connecté |
| GET | `/api/cers.php?id=5` | non | Un seul CER |
| POST | `/api/cers.php` | **oui** | Crée un CER |
| PUT | `/api/cers.php?id=5` | **oui**, auteur uniquement | Modifie un CER |
| DELETE | `/api/cers.php?id=5` | **oui**, auteur uniquement | Supprime un CER |
| POST | `/api/auth/register.php` | non | Inscription (`name`, `email`, `password`, `level`) |
| POST | `/api/auth/login.php` | non | Connexion (`email`, `password`) |
| GET | `/api/auth/logout.php` | non | Déconnexion |
| GET | `/api/auth/me.php` | non | Qui est connecté actuellement ? |
| GET | `/api/preferences/language.php` | non | Langue actuelle (cookie) |
| POST | `/api/preferences/language.php` | non | Change la langue (`language`: `"fr"` ou `"en"`) |

## Tester avec Postman

1. Importez `postman/Archiva-API.postman_collection.json` dans Postman (**Import**, glissez le fichier).
2. Dans Postman, activez la gestion des cookies (normalement automatique : Postman garde la session entre les requêtes d'une même collection).
3. Lancez les requêtes dans l'ordre : **Register** → **Me** → **Create CER** → **List mine** → **Delete CER**.

## Pourquoi sessions ET cookies, pas l'un ou l'autre ?

- **Sessions** (authentification) : la vraie donnée reste **côté serveur**. Le navigateur ne reçoit qu'un identifiant, qui expire à la fermeture du navigateur.
- **Cookies** (préférence de langue) : une donnée simple, publique, stockée **directement dans le navigateur**, qui doit survivre même après la fermeture du navigateur.

## Brancher le frontend React

Voir `vite.config.js` dans le projet `archiva-react` : un proxy redirige `/api/*` vers `http://localhost/archiva-api`, pour que React et l'API semblent être sur la même origine (évite les soucis de cookies entre deux ports différents).

Ordre de démarrage, à chaque session de travail :
1. Démarrer **Apache** et **MySQL** dans XAMPP.
2. Dans le dossier `archiva-react`, lancer `npm run dev`.
3. Ouvrir `http://localhost:5173`.

## Limite assumée (projet local)

Cette API n'est pas déployée publiquement : XAMPP est un outil de développement local, pas un hébergeur. Pour un vrai déploiement, il faudrait un hébergement PHP + MySQL (hors du périmètre de cette étape, qui reste volontairement locale).

---

## Qualité et sécurité du code (tests automatisés, CI/CD)

### Architecture testable

La logique métier vit dans `src/Services/` (`AuthService`, `CerService`), séparée des fichiers `api/*.php` qui ne font plus qu'un fin aiguillage HTTP. Ça permet de tester les règles importantes (mot de passe haché, un CER ne peut être modifié que par son auteur, email en double refusé...) sans avoir besoin d'un vrai serveur web.

```
src/
├── Services/        AuthService.php, CerService.php — toute la logique
├── Exceptions/       une classe par type d'erreur (404, 403, 409...)
└── Support/          Validator.php — fonctions de validation pures

tests/
├── Unit/              tests sur des fonctions pures (Validator)
└── Feature/            tests sur les services, avec SQLite en mémoire
```

### Installer et lancer les tests en local

```bash
composer install
vendor/bin/phpunit
```

Pour générer un rapport de couverture de code, il faut que l'extension **Xdebug** soit activée dans votre `php.ini` (XAMPP l'installe par défaut, mais souvent désactivée) :

```bash
vendor/bin/phpunit --coverage-html coverage-html
```

Ouvrez ensuite `coverage-html/index.html` dans un navigateur pour voir, ligne par ligne, ce qui est couvert par les tests.

### Pratiquer le TDD sur une nouvelle fonctionnalité

1. Écrivez d'abord un test qui décrit le comportement attendu (il doit échouer, puisque le code n'existe pas encore).
2. Écrivez le minimum de code pour faire passer ce test.
3. Nettoyez le code si besoin, en gardant le test au vert.

### Intégration continue (GitHub Actions)

Le fichier `.github/workflows/ci.yml` exécute automatiquement, à chaque `push` et chaque Pull Request :
1. L'installation des dépendances (`composer install`)
2. Tous les tests PHPUnit, avec mesure de la couverture de code
3. Une analyse SonarCloud (qualité + sécurité)

### Connecter SonarCloud (une seule fois)

1. Créez un compte gratuit sur [sonarcloud.io](https://sonarcloud.io), en vous connectant avec GitHub.
2. Cliquez sur **+ → Analyze new project**, choisissez votre dépôt `archiva-api`.
3. SonarCloud vous donne une **Organization Key** et un **Project Key** : reportez-les dans `sonar-project.properties`, à la place de `VOTRE-PSEUDO_archiva-api` et `votre-organisation-sonarcloud`.
4. Dans SonarCloud, générez un **token** (*My Account → Security*).
5. Sur GitHub, dans votre dépôt : *Settings → Secrets and variables → Actions → New repository secret*, nommez-le `SONAR_TOKEN`, collez le token.
6. Poussez du code : l'onglet **Actions** de GitHub affiche l'exécution, et SonarCloud affiche le résultat de l'analyse.

### Pourquoi cette architecture, concrètement

| Avant (tout dans `api/cers.php`) | Après (service séparé) |
|---|---|
| Impossible de tester sans lancer un vrai serveur | Testable en quelques millisecondes, sans réseau |
| `exit()` au milieu du code bloquerait un test | Les services ne font jamais `exit()` ni `header()` |
| Toute erreur mélangée avec la réponse HTTP | Chaque erreur est une exception typée, claire à tester |
