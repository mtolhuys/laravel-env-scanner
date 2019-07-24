<?php

namespace Mtolhuys\LaravelEnvScanner\Tests;

use Illuminate\Support\Facades\Artisan;
use Mtolhuys\LaravelEnvScanner\LaravelEnvScanner;
use Mtolhuys\LaravelEnvScanner\LaravelEnvScannerServiceProvider;
use Orchestra\Testbench\TestCase;

class EnvScanTest extends TestCase
{
    private $scanner;

    public function getPackageProviders($app)
    {
        return [
            LaravelEnvScannerServiceProvider::class,
        ];
    }

    /**
     * This function is actually a hidden test on its own
     * testing scanner results using pattern '# env\((.*?)\)#'
     * therefore: '$test=null' should not be in the results
     *
     * @param string $dir
     * @throws \Exception
     */
    private function scanning_for_env(string $dir = null)
    {
        $this->scanner = (new LaravelEnvScanner($dir))->scan();
        $risky = 'USAGE';

        // Defined
        env('FILLED');
        getenv('GET_FILLED');

        // Test if doubles are ignored
        env('FILLED');
        env('NOT_FILLED');
        env('FILLED_WITH_FALSE');
        env('POTENTIALLY_'.$risky);
        getenv($risky);

        env('DEPENDING_ON_DEFAULT', 'default');
        getenv('GET_DEPENDING_ON_DEFAULT', 'default');
        env('DEFAULT_IS_FALSE', false);

        env('UNDEFINED');
        getenv('GET_UNDEFINED');
    }

    /** @test
     * @throws \Exception
     */
    public function it_checks_if_example_env_scan_results_are_correct()
    {
        $this->scanning_for_env(__DIR__);

        $this->assertSame($this->scanner->results['files'], 1);
        $this->assertSame($this->scanner->results['defined'], 4);
        $this->assertSame($this->scanner->results['depending_on_default'], 3);
        $this->assertSame($this->scanner->results['undefined'], 2);
        $this->assertSame($this->scanner->results['columns'][0]['filename'], basename(__FILE__));

        foreach ($this->scanner->results['columns'] as $result) {
            if ($result['defined'] !== '-') {
                $this->assertTrue($result['depending_on_default'] === '-');
                $this->assertTrue($result['undefined'] === '-');
                $this->assertContains($result['defined'], [
                    'FILLED',
                    'GET_FILLED',
                    'NOT_FILLED',
                    'FILLED_WITH_FALSE'
                ]);
            } else if ($result['depending_on_default'] !== '-') {
                $this->assertSame($result['defined'], '-');
                $this->assertSame($result['undefined'], '-');
                $this->assertContains($result['depending_on_default'], [
                    'DEPENDING_ON_DEFAULT',
                    'GET_DEPENDING_ON_DEFAULT',
                    'DEFAULT_IS_FALSE',
                ]);
            } else if ($result['undefined'] !== '-') {
                $this->assertSame($result['depending_on_default'], '-');
                $this->assertSame($result['defined'], '-');
                $this->assertContains($result['undefined'], [
                    'UNDEFINED',
                    'GET_UNDEFINED',
                ]);
            }
        }
    }

    /** @test */
    public function it_checks_if_command_output_is_correct_with_undefined_only_option()
    {
        $safeEnv = 'env';
        $safeGetEnv = 'getenv';
        $expectedOutput = 'Scanning: ' . __DIR__ . '...' . PHP_EOL
            . "Warning: $safeEnv('POTENTIALLY_'.\$risky) found in ". __FILE__ . PHP_EOL
            . "Warning: $safeGetEnv(\$risky) found in ". __FILE__ . PHP_EOL
            . '2 used environmental variables are undefined:' . PHP_EOL
            . __FILE__ . ': UNDEFINED' . PHP_EOL
            . __FILE__ . ': GET_UNDEFINED' . PHP_EOL;

        Artisan::call('env:scan', [
            '--dir' => __DIR__,
            '--undefined-only' => 'true',
        ]);

        $this->assertSame($expectedOutput, Artisan::output());
    }
}
