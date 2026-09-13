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
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();

            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('task_id')->unsigned()->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        $tasks = DB::table('tasks')->whereNotNull('comments')->get();
        foreach ($tasks as $task) {
            DB::table('task_comments')->insert([
                'comment' => $task->comments,
                'user_id' => $task->responsible_id,
                'task_id' => $task->id,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ]);
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');

        Schema::table('tasks', function (Blueprint $table) {
            $table->text('comments')->nullable();
        });
    }
};
