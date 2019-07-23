<?php

namespace Mtolhuys\LaravelEnvScanner;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class LaravelEnvScanner
{
    /**
     * The results of performed scan
     *
     * @var array
     */
    public $results = [
        'files' => 0,
        'defined' => 0,
        'undefined' => 0,
        'depending_on_default' => 0,
        'columns' => [],
    ];

    /**
     * Stores processed file and var names
     *
     * @var array
     */
    private $processed = [
        'files' => [],
        'vars' => [],
    ];

    /**
     * Stores undefined var names
     *
     * @var array
     */
    public $undefined = [];

    /**
     * Stores warnings for vars not passing validation
     *
     * @var array
     */
    public $warnings = [];

    /**
     * Current file being processed
     *
     * @var string
     */
    private $currentFile;

    /**
     * Root directory to start recursive search for env()'s from
     * Defaults to config_path()
     *
     * @var string $dir
     */
    public $dir;

    public function __construct(string $dir = null)
    {
        $this->dir = $dir ?? config_path();
    }

    /**
     * Run the scan
     *
     * @return mixed
     * @throws \Exception
     */
    public function scan()
    {
        $files = $this->recursiveDirSearch($this->dir, '/.*?.php/');

        foreach ($files as $file) {
            preg_match_all(
                '# env\((.*?)\)| getenv\((.*?)\)#',
                str_replace(["\n", "\r"], '', file_get_contents($file)),
                $matches
            );

            if (empty(array_filter($matches))) {
                continue;
            }

            $this->currentFile = $file;
            $invocations = $matches[0];

            foreach ($invocations as $index => $invocation) {
                $result = $this->getResult($invocation, [
                    $matches[1][$index],
                    $matches[2][$index]
                ]);

                if (!$result) {
                    continue;
                }

                $this->storeResult($result);
            }
        }

        return $this;
    }

    /**
     * Get result based on comma separated parsed env() or getenv() parameters
     * Validates by alphanumeric and underscore and skips already processed
     *
     * @param string $invocation
     * @param array $matches
     * @return object|bool
     */
    private function getResult(string $invocation, array $matches)
    {
        $params = explode(',', str_replace(
            ["'", '"', ' '], '', empty($matches[0]) ? $matches[1] : $matches[0]
        ));

        $envVar = $params[0];

        if (in_array($envVar, $this->processed['vars'])) {
            return false;
        }

        $this->processed['vars'][] = $envVar;

        if (!preg_match('/^[A-Za-z0-9_]+$/', $envVar)) {
            $invocation = str_replace(' ', '', $invocation);

            $this->warnings[] = (object)[
                'filename' => $this->currentFile,
                'invocation' => $invocation,
            ];

            return false;
        }

        return (object)[
            'envVar' => $envVar,
            'hasValue' => env($envVar) !== null,
            'hasDefault' => isset($params[1]),
        ];
    }

    /**
     * Store result and optional runtime output
     *
     * @param $result
     */
    private function storeResult($result)
    {
        $resultData = [
            'filename' => $this->getColumnFilename(),
            'defined' => '-',
            'depending_on_default' => '-',
            'undefined' => '-',
        ];

        if ($result->hasValue) {
            $resultData['defined'] = $result->envVar;
            $this->results['defined']++;
        } else if ($result->hasDefault) {
            $resultData['depending_on_default'] = $result->envVar;
            $this->results['depending_on_default']++;
        } else {
            $resultData['undefined'] = $this->undefined[] = $result->envVar;
            $this->results['undefined']++;
        }

        $this->results['columns'][] = $resultData;

        if (!in_array($this->currentFile, $this->processed['files'])) {
            $this->results['files']++;
            $this->processed['files'][] = $this->currentFile;
        }
    }

    /**
     * Return filename or '-' for table
     *
     * @return string
     */
    private function getColumnFilename(): string
    {
        if (in_array($this->currentFile, $this->processed['files'])) {
            return '-';
        }

        return basename($this->currentFile);
    }

    private function recursiveDirSearch(string $folder, string $pattern): array
    {
        if (!file_exists($folder)) {
            return [];
        }

        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folder)
            ),
            $pattern, RegexIterator::GET_MATCH
        );

        $list = [];

        foreach ($files as $file) {
            $list = array_merge($list, $file);
        }

        return $list;
    }
}
