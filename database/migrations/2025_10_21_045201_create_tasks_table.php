<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: This migration is designed for MongoDB.
     * MongoDB is schemaless, so we only create the collection.
     * Indexes are created separately via db/indexes.js script.
     */
    public function up(): void
    {
        // For MongoDB, we just need to create the collection
        // The schema is flexible and doesn't need to be defined upfront

        // MongoDB collections are created automatically on first insert
        // But if you want to explicitly create it:
        if (config('database.default') === 'mongodb') {
            // Collection will be created automatically
            // Indexes are managed via db/indexes.js

            // Alternative: Create collection explicitly
            // DB::connection('mongodb')->getCollection('tasks');
        } else {
            // If using MySQL/SQL, create traditional table
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('pending');
                $table->string('priority')->default('medium');
                $table->date('due_date')->nullable();
                $table->timestamps();

                // Indexes for SQL databases
                $table->index('status');
                $table->index('priority');
                $table->index('due_date');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mongodb') {
            // Drop MongoDB collection
            Schema::connection('mongodb')->dropIfExists('tasks');
        } else {
            // Drop SQL table
            Schema::dropIfExists('tasks');
        }
    }
};
