<?php

namespace Mtolhuys\LaravelEnvScanner\Commands;

use Illuminate\Console\Command;
use Mtolhuys\LaravelEnvScanner\LaravelEnvScanner;

class EnvScan extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        env:scan 
            { --d|dir= : Specify directory to scan (defaults to your config folder) }
    ';

    private $scanner;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check environmental variables used in your app';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->scanner = new LaravelEnvScanner(
            $this->option('dir')
        );

        if (! file_exists($this->scanner->dir)) {
            $this->error("{$this->scanner->dir} does not exist");
            exit();
        }

        $this->output->write(
            "<fg=green>Scanning:</fg=green> <fg=white>{$this->scanner->dir}...</fg=white>\n"
        );

        $this->scanner->scan();

        $this->table([
            "Files ({$this->scanner->results['files']})",
            "Has value ({$this->scanner->results['has_value']})",
            "Depending on default ({$this->scanner->results['depending_on_default']})",
            "No value ({$this->scanner->results['empty']})",
        ], $this->scanner->results['data']);
    }
}
