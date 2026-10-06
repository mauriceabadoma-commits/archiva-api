<?php

namespace App\Services;

use PDO;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;

/**
 * Toute la logique des CERs (lister, créer, modifier, supprimer), y compris
 * la règle « seul l'auteur peut modifier/supprimer son CER ». Testable
 * directement avec une base SQLite en mémoire.
 */
class CerService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param array{search?: string, level?: string, user_id?: int} $filters */
    public function list(array $filters = []): array
    {
        $sql = 'SELECT id, title, description, level, image, author, user_id, created_at FROM cers WHERE 1=1';
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= ' AND user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND title LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['level'])) {
            $sql .= ' AND level = :level';
            $params['level'] = $filters['level'];
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM cers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $cer = $stmt->fetch();

        if (!$cer) {
            throw new NotFoundException('CER introuvable.');
        }

        return $cer;
    }

    public function create(int $userId, string $authorName, array $data): int
    {
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $level = trim($data['level'] ?? 'X1');

        if ($title === '' || $description === '') {
            throw new ValidationException('Le titre et la description sont obligatoires.');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO cers (user_id, title, description, level, image, author)
             VALUES (:user_id, :title, :description, :level, :image, :author)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'level' => $level,
            'image' => 'web-intro.svg',
            'author' => $authorName,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): void
    {
        $cer = $this->findOwned($id, $userId);

        $title = trim($data['title'] ?? $cer['title']);
        $description = trim($data['description'] ?? $cer['description']);
        $level = trim($data['level'] ?? $cer['level']);

        $stmt = $this->pdo->prepare(
            'UPDATE cers SET title = :title, description = :description, level = :level WHERE id = :id'
        );
        $stmt->execute(['title' => $title, 'description' => $description, 'level' => $level, 'id' => $id]);
    }

    public function delete(int $id, int $userId): void
    {
        $this->findOwned($id, $userId);

        $stmt = $this->pdo->prepare('DELETE FROM cers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Vérifie que le CER existe ET appartient à l'utilisateur, sinon lève une exception. */
    private function findOwned(int $id, int $userId): array
    {
        $cer = $this->find($id);

        if ((int) $cer['user_id'] !== $userId) {
            throw new ForbiddenException("Vous n'êtes pas l'auteur de ce CER.");
        }

        return $cer;
    }
}
