<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Tasks\Models\Task;

class TasksOverdued extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:overdued';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set tasks overdued';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::where('end_date', '<=', now())
            ->whereIn('status', ['to_begin', 'started'])
            ->get();

        foreach ($tasks as $task) {
            $task->status = 'overdued';
            $task->save();
        }
    }
}
