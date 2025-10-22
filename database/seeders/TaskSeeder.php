<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing tasks
        // Note: truncate() doesn't work with MongoDB, use delete() instead
        if (config('database.default') === 'mongodb') {
            Task::query()->delete();
        } else {
            Task::truncate();
        }

        // Create 40 dummy tasks using factory
        Task::factory()->count(40)->create();

        $this->command->info('✅ Successfully created 40 dummy tasks!');
    }
}
