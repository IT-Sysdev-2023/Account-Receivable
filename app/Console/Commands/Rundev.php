<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunDev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = getHostByName(getHostName());

        $this->info("Using host: $host");

        $this->info('Starting Laravel server...');
        $laravelProcess = Process::fromShellCommandline("php artisan serve --host=$host --port=8080");
        $laravelProcess->start();

        $this->info('Starting npm dev server...');
        $npmDevProcess = Process::fromShellCommandline("npm run dev -- --host $host");
        $npmDevProcess->start();

        $this->info('Starting Reverb server...');
        $reverbProcess = Process::fromShellCommandline("php artisan reverb:start --host=$host --port=8081");
        $reverbProcess->start();

        $this->info('Starting queue listener...');
        $queueProcess = Process::fromShellCommandline("php artisan queue:listen");
        // $queueProcess = Process::fromShellCommandline("php artisan queue:work --queue=default --tries=3 --timeout=0");
        $queueProcess->start();

        $processes = [$laravelProcess, $npmDevProcess, $reverbProcess, $queueProcess];

        while (array_filter($processes, fn($p) => $p->isRunning())) {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    echo $process->getIncrementalOutput();
                    echo $process->getIncrementalErrorOutput();
                }
            }
            usleep(100_000); // 0.1s
        }

        return 0;
    }
}
