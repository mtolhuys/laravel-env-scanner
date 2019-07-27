<?php

namespace Mtolhuys\LaravelEnvScanner\Tests;

use Illuminate\Support\Facades\Artisan;
use Mtolhuys\LaravelEnvScanner\LaravelEnvScanner;
use Mtolhuys\LaravelEnvScanner\LaravelEnvScannerServiceProvider;
use Orchestra\Testbench\TestCase;

class EnvScanTest extends TestCase
{
    private $results;

    public function getPackageProviders($app): array
    {
        return [
            LaravelEnvScannerServiceProvider::class,
        ];
    }

    /** @test
     * @throws \Exception
     */
    public function it_checks_if_example_env_scan_results_are_correct()
    {
        $this->scanning_for_env(__DIR__);

        $this->assertSame($this->results['locations'], 9);
        $this->assertSame($this->results['defined'], 4);
        $this->assertSame($this->results['depending_on_default'], 3);
        $this->assertSame($this->results['undefined'], 2);
        $this->assertContains( __FILE__, $this->results['columns'][0]['location']);

        foreach ($this->results['columns'] as $result) {
            if ($result['defined'] !== '-') {
                $this->assertSame($result['depending_on_default'], '-');
                $this->assertSame($result['undefined'], '-');
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
        $this->results = (new LaravelEnvScanner($dir))->scan()->results;
        $risky = 'USAGE';

        // Defined
        env('FILLED');
        getenv('GET_FILLED');
        env('FILLED_WITH_FALSE');
        env('NOT_FILLED');

        // Test if doubles are ignored
        env('FILLED');

        // Risky behavior is not tested
        env(
            'POTENTIALLY_'.$risky,
            'behavior'
        );
        getenv($risky);

        env('DEPENDING_ON_DEFAULT', 'default');
        getenv('GET_DEPENDING_ON_DEFAULT', 'default');
        env('DEFAULT_IS_FALSE', false);

        env('UNDEFINED');
        getenv('GET_UNDEFINED');
    }
}
