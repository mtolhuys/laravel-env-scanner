<?php

namespace Mtolhuys\LaravelEnvScanner\Tests;

use Mtolhuys\LaravelEnvScanner\LaravelEnvScanner;
use Orchestra\Testbench\TestCase;

class EnvScanTest extends TestCase
{
    private $scanner;

    /**
     * EnvScanTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     * @throws \Exception
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        env('FILLED');
        env('DEPENDS', 'on_default');
        env('EMPTY');

        $this->scanner = new LaravelEnvScanner(__DIR__);

        $this->scanner->scan();
    }

    /** @test
     * @throws \Exception
     */
    public function it_checks_if_example_env_scan_results_are_correct()
    {
        $this->assertTrue($this->scanner->results['files'] === 1);
        $this->assertTrue($this->scanner->results['has_value'] === 1);
        $this->assertTrue($this->scanner->results['depending_on_default'] === 1);
        $this->assertTrue($this->scanner->results['empty'] === 1);
        $this->assertTrue($this->scanner->results['data'][0]['filename'] === basename(__FILE__));

        foreach ($this->scanner->results['data'] as $result) {
            if ($result['has_value'] !== '-') {
                $this->assertTrue($result['has_value'] === 'FILLED');
                $this->assertTrue($result['depending_on_default'] === '-');
                $this->assertTrue($result['empty'] === '-');
            } else if ($result['depending_on_default'] !== '-') {
                $this->assertTrue($result['depending_on_default'] === 'DEPENDS');
                $this->assertTrue($result['has_value'] === '-');
                $this->assertTrue($result['empty'] === '-');
            } else if ($result['empty'] !== '-') {
                $this->assertTrue($result['empty'] === 'EMPTY');
                $this->assertTrue($result['depending_on_default'] === '-');
                $this->assertTrue($result['has_value'] === '-');

            }
        }
    }
}
