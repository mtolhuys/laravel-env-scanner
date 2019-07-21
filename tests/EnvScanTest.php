<?php

namespace Mtolhuys\LaravelEnvScanner\Tests;

use Mtolhuys\LaravelEnvScanner\LaravelEnvScanner;
use Orchestra\Testbench\TestCase;

class EnvScanTest extends TestCase
{
    private function not_env($test) {
        // This should not turn up in scanner results
    }

    /** @test
     * @throws \Exception
     */
    public function it_checks_if_example_env_scan_results_are_correct()
    {
        // Defined
        env('FILLED');
        // Test if doubles are ignored
        env('FILLED');
        env('NOT_FILLED');
        env('FILLED_WITH_FALSE');

        // Depending on their default value
        env('DEPENDING_ON_DEFAULT', 'default');
        env('DEFAULT_IS_FALSE', false);

        // Undefined
        env('UNDEFINED');

        $scanner = (new LaravelEnvScanner(__DIR__))->scan();

        $this->assertTrue($scanner->results['files'] === 1);
        $this->assertTrue($scanner->results['defined'] === 3);
        $this->assertTrue($scanner->results['depending_on_default'] === 2);
        $this->assertTrue($scanner->results['undefined'] === 1);
        $this->assertTrue($scanner->results['data'][0]['filename'] === basename(__FILE__));

        foreach ($scanner->results['data'] as $result) {
            if ($result['defined'] !== '-') {
                $this->assertTrue($result['depending_on_default'] === '-');
                $this->assertTrue($result['undefined'] === '-');
                $this->assertTrue(
                    $result['defined'] === 'FILLED'
                    || $result['defined'] === 'NOT_FILLED'
                    || $result['defined'] === 'FILLED_WITH_FALSE'
                );
            } else if ($result['depending_on_default'] !== '-') {
                $this->assertTrue($result['defined'] === '-');
                $this->assertTrue($result['undefined'] === '-');
                $this->assertTrue(
                    $result['depending_on_default'] === 'DEPENDING_ON_DEFAULT'
                    || $result['depending_on_default'] === 'DEFAULT_IS_FALSE'
                );
            } else if ($result['undefined'] !== '-') {
                $this->assertTrue($result['depending_on_default'] === '-');
                $this->assertTrue($result['defined'] === '-');
                $this->assertTrue($result['undefined'] === 'UNDEFINED');
            }
        }
    }
}
