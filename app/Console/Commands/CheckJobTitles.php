<?php

namespace App\Console\Commands;

use App\Models\JobTitle;
use Illuminate\Console\Command;

class CheckJobTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:job-titles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check job titles data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jobTitles = JobTitle::all();
        $this->info('Total job titles: ' . $jobTitles->count());
        $this->newLine();

        $headers = ['ID', 'Name', 'Status'];
        $data = [];

        foreach ($jobTitles as $jobTitle) {
            $data[] = [
                'ID' => $jobTitle->id,
                'Name' => $jobTitle->name,
                'Status' => $jobTitle->status
            ];
        }

        $this->table($headers, $data);
        
        return Command::SUCCESS;
    }
}