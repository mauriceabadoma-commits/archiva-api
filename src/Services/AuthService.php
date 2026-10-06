<?php

namespace App\Services;

use PDO;
use App\Support\Validator;
use App\Exceptions\ValidationException;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;

/**
 * Toute la logique d'inscription et de connexion, séparée du routage HTTP.
 * Peut être testée directement avec une base SQLite en mémoire, sans
 * jamais démarrer de vrai serveur (voir tests/Feature/AuthServiceTest.php).
 */
class AuthService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function register(string $name, string $email, string $password, string $level = 'X1'): array
    {
        $name = trim($name);
        $email = trim($email);

        if ($name === '' || $email === '' || $password === '') {
            throw new ValidationException('Nom, email et mot de passe sont obligatoires.');
        }
        if (!Validator::isValidEmail($email)) {
            throw new ValidationException('Adresse email invalide.');
        }
        if (!Validator::isValidPassword($password)) {
            throw new ValidationException('Le mot de passe doit contenir au moins 6 caractères.');
        }

        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            throw new ConflictException('Un compte existe déjà avec cet email.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, level) VALUES (:name, :email, :hash, :level)'
        );
        $stmt->execute(['name' => $name, 'email' => $email, 'hash' => $hash, 'level' => $level]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'name' => $name,
            'email' => $email,
        ];
    }

    public function login(string $email, string $password): array
    {
        $email = trim($email);

        if ($email === '' || $password === '') {
            throw new ValidationException('Email et mot de passe sont obligatoires.');
        }

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new UnauthorizedException('Email ou mot de passe incorrect.');
        }

        return [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
    }
}
