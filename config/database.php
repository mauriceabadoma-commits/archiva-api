<?php
/**
 * Connexion à la base de données.
 *
 * Valeurs par défaut adaptées à XAMPP (utilisateur "root", pas de mot de
 * passe). Si vous avez protégé votre MySQL local, changez DB_PASS ci-dessous.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'archiva');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retourne une connexion PDO unique (créée une seule fois par requête).
 *
 * Astuce de test : si la variable d'environnement ARCHIVA_SQLITE_PATH est
 * définie, on utilise SQLite à la place de MySQL. Cela ne sert qu'aux tests
 * automatisés sans serveur MySQL ; en local avec XAMPP, cette variable
 * n'existe pas, donc MySQL est toujours utilisé normalement.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $sqlitePath = getenv('ARCHIVA_SQLITE_PATH');

        if ($sqlitePath) {
            $pdo = new PDO('sqlite:' . $sqlitePath);
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    return $pdo;
}
