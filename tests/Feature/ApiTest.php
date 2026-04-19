<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function getToken(string $email = 'alice@example.com'): array
    {
        $user = $this->createUser(['email' => $email]);

        $response = $this->postJson('/api/v1/login', [
            'email'       => $email,
            'password'    => 'password',
            'device_name' => 'test',
        ]);

        return ['token' => $response->json('token'), 'user' => $user];
    }

    #[Test]
    public function api_login_returns_token(): void
    {
        $this->createUser(['email' => 'test@example.com']);

        $this->postJson('/api/v1/login', [
            'email'       => 'test@example.com',
            'password'    => 'password',
            'device_name' => 'phpunit',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    #[Test]
    public function api_login_fails_with_wrong_password(): void
    {
        $this->createUser(['email' => 'test@example.com']);

        $this->postJson('/api/v1/login', [
            'email'       => 'test@example.com',
            'password'    => 'wrong-password',
            'device_name' => 'phpunit',
        ])->assertUnprocessable(); // 422
    }

    #[Test]
    public function api_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/projects')->assertUnauthorized(); // 401
    }

    #[Test]
    public function api_can_list_projects(): void
    {
        ['token' => $token, 'user' => $user] = $this->getToken();
        $project = $this->createProject($user);

        $this->withToken($token)
            ->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'status', 'created_at']],
            ]);
    }

    #[Test]
    public function api_can_create_project(): void
    {
        ['token' => $token] = $this->getToken();

        $this->withToken($token)
            ->postJson('/api/v1/projects', [
                'name'   => 'API Created Project',
                'status' => 'active',
            ])
            ->assertCreated() // 201
            ->assertJsonPath('name', 'API Created Project');

        $this->assertDatabaseHas('projects', ['name' => 'API Created Project']);
    }

    #[Test]
    public function api_returns_403_for_non_member_project_access(): void
    {
        $owner  = $this->createUser();
        $project = $this->createProject($owner);

        ['token' => $token] = $this->getToken('other@example.com');

        $this->withToken($token)
            ->getJson("/api/v1/projects/{$project->id}")
            ->assertForbidden(); // 403
    }

    #[Test]
    public function api_can_update_task_status(): void
    {
        ['token' => $token, 'user' => $user] = $this->getToken();
        $project = $this->createProject($user);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status'     => 'todo',
        ]);

        $this->withToken($token)
            ->patchJson("/api/v1/projects/{$project->id}/tasks/{$task->id}/status", [
                'status' => 'done',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'done');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    #[Test]
    public function api_logout_revokes_token(): void
    {
        ['token' => $token] = $this->getToken();

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertOk();


        // Force Laravel to forget the authenticated user in memory
        $this->app->forgetInstance('auth');

        // Token should no longer work
        $this->withToken($token)
            ->getJson('/api/v1/projects')
            ->assertUnauthorized();
    }
}
