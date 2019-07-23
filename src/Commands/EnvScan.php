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
            { --u|undefined-only : Only show undefined variables as output }
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

        $this->showOutput();
    }

    private function showOutput() {
        foreach ($this->scanner->warnings as $warning) {
            $this->warn("Warning: <fg=red>{$warning->invocation}</fg=red> found in {$warning->filename}");
        }

        if ($this->option('undefined-only')) {
            if ($this->scanner->results['undefined'] === 0) {
                $this->info("Looking good!");
            } else {
                $this->line(
                    "<fg=red>{$this->scanner->results['undefined']} used environmental variables are undefined:</fg=red>"
                );
                $this->line('<fg=red>'.implode(PHP_EOL, $this->scanner->undefined)."</fg=red>");
            }
        }

        else {
            $this->table([
                "Files ({$this->scanner->results['files']})",
                "Defined ({$this->scanner->results['defined']})",
                "Depending on default ({$this->scanner->results['depending_on_default']})",
                "Undefined ({$this->scanner->results['undefined']})",
            ], $this->scanner->results['columns']);
        }
    }
}
