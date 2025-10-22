<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer mock-token-12345'];
    }

    /**
     * Test get all tasks with pagination.
     */
    public function test_get_all_tasks_with_pagination(): void
    {
        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks', $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ])
            ->assertJson(['success' => true]);
    }

    /**
     * Test filter tasks by status.
     */
    public function test_filter_tasks_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/tasks?status=pending', $this->getAuthHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pending', $data[0]['status']);
    }

    /**
     * Test filter tasks by priority.
     */
    public function test_filter_tasks_by_priority(): void
    {
        Task::factory()->create(['priority' => 'high']);
        Task::factory()->create(['priority' => 'low']);

        $response = $this->getJson('/api/tasks?priority=high', $this->getAuthHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('high', $data[0]['priority']);
    }

    /**
     * Test search tasks.
     */
    public function test_search_tasks(): void
    {
        Task::factory()->create(['title' => 'Important task']);
        Task::factory()->create(['title' => 'Regular task']);

        $response = $this->getJson('/api/tasks?search=Important', $this->getAuthHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Important', $data[0]['title']);
    }

    /**
     * Test sort tasks.
     */
    public function test_sort_tasks(): void
    {
        Task::factory()->create(['title' => 'B Task']);
        Task::factory()->create(['title' => 'A Task']);

        $response = $this->getJson('/api/tasks?sort_by=title&sort_order=asc', $this->getAuthHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('A Task', $data[0]['title']);
    }

    /**
     * Test create task with valid data.
     */
    public function test_create_task_with_valid_data(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/tasks', $taskData, $this->getAuthHeaders());

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    /**
     * Test create task fails without title.
     */
    public function test_create_task_fails_without_title(): void
    {
        $response = $this->postJson('/api/tasks', [
            'description' => 'Task description',
        ], $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test create task fails with invalid status.
     */
    public function test_create_task_fails_with_invalid_status(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'New Task',
            'status' => 'invalid_status',
        ], $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test create task fails with duplicate title.
     */
    public function test_create_task_fails_with_duplicate_title(): void
    {
        // Create first task
        Task::factory()->create(['title' => 'Duplicate Task']);

        // Try to create another task with same title
        $response = $this->postJson('/api/tasks', [
            'title' => 'Duplicate Task',
            'description' => 'This should fail',
        ], $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test get single task.
     */
    public function test_get_single_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}", $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                ],
            ]);
    }

    /**
     * Test get non-existent task returns 404.
     */
    public function test_get_nonexistent_task_returns_404(): void
    {
        $response = $this->getJson('/api/tasks/99999', $this->getAuthHeaders());

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found',
            ]);
    }

    /**
     * Test update task.
     */
    public function test_update_task(): void
    {
        $task = Task::factory()->create(['title' => 'Original Title']);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'status' => 'completed',
        ], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => [
                    'title' => 'Updated Title',
                    'status' => 'completed',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * Test update task can keep same title.
     */
    public function test_update_task_can_keep_same_title(): void
    {
        $task = Task::factory()->create(['title' => 'My Task']);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'My Task', // Same title
            'description' => 'Updated description',
        ], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully',
            ]);
    }

    /**
     * Test update task fails with duplicate title from another task.
     */
    public function test_update_task_fails_with_duplicate_title(): void
    {
        $task1 = Task::factory()->create(['title' => 'Task One']);
        $task2 = Task::factory()->create(['title' => 'Task Two']);

        // Try to update task2 with task1's title
        $response = $this->putJson("/api/tasks/{$task2->id}", [
            'title' => 'Task One',
        ], $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test delete task.
     */
    public function test_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}", [], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test tasks endpoint requires authentication.
     */
    public function test_tasks_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }
}
