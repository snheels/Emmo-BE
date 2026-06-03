<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignee_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('notes')->nullable();
            $table->enum('priority', ['high', 'medium', 'low']);
            $table->enum('status', ['todo', 'progress', 'completed'])->default('todo');
            $table->enum('review_status', ['pending', 'revision', 'approved'])->default('pending');
            $table->date('due_date');
            $table->dateTime('completed_at')->nullable();
            $table->text('revision_notes')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
