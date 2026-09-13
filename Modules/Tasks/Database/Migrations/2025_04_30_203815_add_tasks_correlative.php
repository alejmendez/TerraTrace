<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_correlative', function (Blueprint $table) {
            $table->id();
            $table->integer('correlative')->unsigned();
            $table->integer('year')->unsigned();
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('correlative', 10)->nullable();
        });

        $tasks = DB::table('tasks')->orderBy('id')->get();
        $correlatives = [];
        foreach ($tasks as $task) {
            $year = Carbon::parse($task->created_at)->year;
            if (! isset($correlatives[$year])) {
                $correlatives[$year] = 0;
            }
            $correlative = str_pad($correlatives[$year], 3, '0', STR_PAD_LEFT).'_'.substr($year, -2);
            DB::table('tasks')->where('id', $task->id)->update(['correlative' => $correlative]);
            $correlatives[$year]++;
        }

        foreach ($correlatives as $year => $correlative) {
            DB::table('task_correlative')->insert([
                'correlative' => $correlative - 1,
                'year' => $year,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('correlative');
        });

        Schema::dropIfExists('task_correlative');
    }
};
