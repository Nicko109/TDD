<?php

namespace App\Console\Commands;

use App\Imports\ProjectDynamicImport;
use App\Imports\ProjectImport;
use App\Models\Task;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command Test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        Excel::import(new ProjectImport(), 'files/projects (1).xlsx', 'public');
        Excel::import(new ProjectDynamicImport(Task::find(6)), 'files/projects2 (1).xlsx', 'public');
    }
}
