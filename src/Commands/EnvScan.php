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
            { --a|app : Include app folder }
            { --t|table : Show results data in a table }
    ';

    private $scanner;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all environmental variables used in config folder';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->scanner = new LaravelEnvScanner(
            $this->option('app')
        );

        $this->scanner->scan();

        $this->showResults();
    }

    private function showResults()
    {
        if ($this->option('table')) {
            $this->table([
                'File',
                "Has value ({$this->scanner->results['has_value']})",
                "Depending on default ({$this->scanner->results['depending_on_default']})",
                "No value ({$this->scanner->results['empty']})",
            ], $this->scanner->results['data']);
        } else {
            $this->info("{$this->scanner->results['has_value']} have a value");
            $this->line("{$this->scanner->results['depending_on_default']} are depending on default");
            $this->warn("{$this->scanner->results['empty']} have no value");
        }
    }
}
