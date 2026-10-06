<?php

namespace Tests\Feature;

use PDO;
use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use App\Exceptions\ValidationException;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;

/**
 * Ces tests reproduisent, de façon automatisée, exactement les scénarios
 * qu'on a vérifiés à la main avec curl pendant le développement : mauvais
 * mot de passe, email déjà utilisé, inscription réussie...
 *
 * SQLite « :memory: » crée une base entièrement neuve, en mémoire, pour
 * chaque test : aucun risque de polluer votre vraie base MySQL, et c'est
 * environ 100 fois plus rapide qu'une vraie connexion réseau à MySQL.
 */
class AuthServiceTest extends TestCase
{
    private PDO $pdo;
    private AuthService $service;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec(
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                level TEXT DEFAULT "X1",
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $this->service = new AuthService($this->pdo);
    }

    public function test_register_creates_a_user_and_returns_its_id(): void
    {
        $user = $this->service->register('Aurelle', 'aurelle@archiva.com', 'secret123');

        $this->assertSame('Aurelle', $user['name']);
        $this->assertSame('aurelle@archiva.com', $user['email']);
        $this->assertIsInt($user['id']);
    }

    public function test_register_never_returns_the_plain_password(): void
    {
        $user = $this->service->register('Aurelle', 'aurelle@archiva.com', 'secret123');

        $this->assertArrayNotHasKey('password', $user);
        $this->assertArrayNotHasKey('password_hash', $user);
    }

    public function test_register_actually_hashes_the_password_in_the_database(): void
    {
        $this->service->register('Aurelle', 'aurelle@archiva.com', 'secret123');

        $stmt = $this->pdo->query('SELECT password_hash FROM users LIMIT 1');
        $hash = $stmt->fetchColumn();

        $this->assertNotSame('secret123', $hash);
        $this->assertTrue(password_verify('secret123', $hash));
    }

    public function test_register_rejects_an_invalid_email(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register('Dauphin', 'pas-un-email', 'secret123');
    }

    public function test_register_rejects_a_short_password(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register('Dauphin', 'dauphin@archiva.com', '123');
    }

    public function test_register_rejects_a_duplicate_email(): void
    {
        $this->service->register('Aurelle', 'aurelle@archiva.com', 'secret123');

        $this->expectException(ConflictException::class);
        $this->service->register('Quelqu\'un d\'autre', 'aurelle@archiva.com', 'secret456');
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $this->service->register('Aurelle', 'aurelle@archiva.com', 'secret123');

        $user = $this->service->login('aurelle@archiva.com', 'secret123');

        $this->assertSame('aurelle@archiva.com', $user['email']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->service->register('Aurelle', 'aurelle@archiva.com', 'secret123');

        $this->expectException(UnauthorizedException::class);
        $this->service->login('aurelle@archiva.com', 'mauvais-mot-de-passe');
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->service->login('inconnu@archiva.com', 'secret123');
    }
}
