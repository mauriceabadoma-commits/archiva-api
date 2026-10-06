<?php

namespace Tests\Feature;

use PDO;
use PHPUnit\Framework\TestCase;
use App\Services\CerService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;

class CerServiceTest extends TestCase
{
    private PDO $pdo;
    private CerService $service;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec(
            'CREATE TABLE cers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                level TEXT DEFAULT "X1",
                image TEXT DEFAULT "web-intro.svg",
                author TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $this->service = new CerService($this->pdo);
    }

    public function test_create_then_find_returns_the_same_cer(): void
    {
        $id = $this->service->create(1, 'Aurelle', [
            'title' => 'Prosit Test',
            'description' => 'Une description',
            'level' => 'X2',
        ]);

        $cer = $this->service->find($id);

        $this->assertSame('Prosit Test', $cer['title']);
        $this->assertSame('X2', $cer['level']);
        $this->assertSame('Aurelle', $cer['author']);
    }

    public function test_create_rejects_an_empty_title(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->create(1, 'Aurelle', ['title' => '', 'description' => 'une description']);
    }

    public function test_create_rejects_an_empty_description(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->create(1, 'Aurelle', ['title' => 'Un titre', 'description' => '']);
    }

    public function test_finding_a_missing_cer_throws_not_found(): void
    {
        $this->expectException(NotFoundException::class);
        $this->service->find(999);
    }

    /** Reproduit exactement le test manuel fait avec curl : chacun ne voit que ses CERs. */
    public function test_list_with_mine_filter_only_returns_the_owners_cers(): void
    {
        $this->service->create(1, 'Aurelle', ['title' => 'CER de Aurelle', 'description' => 'd']);
        $this->service->create(2, 'Dauphin', ['title' => 'CER de Dauphin', 'description' => 'd']);

        $mine = $this->service->list(['user_id' => 1]);

        $this->assertCount(1, $mine);
        $this->assertSame('CER de Aurelle', $mine[0]['title']);
    }

    public function test_list_without_filter_returns_everyones_cers(): void
    {
        $this->service->create(1, 'Aurelle', ['title' => 'CER de Aurelle', 'description' => 'd']);
        $this->service->create(2, 'Dauphin', ['title' => 'CER de Dauphin', 'description' => 'd']);

        $this->assertCount(2, $this->service->list());
    }

    public function test_owner_can_update_their_cer(): void
    {
        $id = $this->service->create(1, 'Aurelle', ['title' => 'Original', 'description' => 'd']);

        $this->service->update($id, 1, ['title' => 'Titre modifié']);

        $this->assertSame('Titre modifié', $this->service->find($id)['title']);
    }

    /** Reproduit le test manuel : modifier le CER d'un autre renvoie 403. */
    public function test_update_by_a_different_user_is_forbidden(): void
    {
        $id = $this->service->create(1, 'Aurelle', ['title' => 'Original', 'description' => 'd']);

        $this->expectException(ForbiddenException::class);
        $this->service->update($id, 2, ['title' => 'Piraté']);
    }

    public function test_owner_can_delete_their_cer(): void
    {
        $id = $this->service->create(1, 'Aurelle', ['title' => 'Original', 'description' => 'd']);

        $this->service->delete($id, 1);

        $this->expectException(NotFoundException::class);
        $this->service->find($id);
    }

    public function test_delete_by_a_different_user_is_forbidden(): void
    {
        $id = $this->service->create(1, 'Aurelle', ['title' => 'Original', 'description' => 'd']);

        $this->expectException(ForbiddenException::class);
        $this->service->delete($id, 2);
    }
}
