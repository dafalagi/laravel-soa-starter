<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;

class MigrateFresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:migrate-fresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrate:fresh and set up Passport';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running migrate:fresh command...');
        $this->call('migrate:fresh', ['--seed' => true]);
        $this->info('Migrate fresh completed.');

        $this->info('Setting up Passport...');
        $this->call('passport:keys', ['--force' => true]);
        $this->call('passport:client',['--personal' => true]);
        $this->info('Passport setup completed.');
    }
}
