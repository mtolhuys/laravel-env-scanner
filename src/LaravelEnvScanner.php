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
        'processed' => [],
        'data' => [],
    ];

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
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function scan()
    {
        $files = $this->recursiveDirSearch($this->dir,  '/.*?.php/');

        foreach ($files as $file) {
            preg_match_all(
                '# env\((.*?)\)#',
                str_replace(["\n", "\r"], '', file_get_contents($file)),
                $values
            );

            if (is_array($values)) {
                foreach ($values[1] as $value) {
                    $result = $this->getResult(
                        explode(',', str_replace(["'", '"', ' '], '', $value))
                    );

                    if (! $result) {
                        continue;
                    }

                    $this->storeResult($file, $result);
                }
            }
        }

        return $this;
    }

    /**
     * Get result based on comma separated parsed env() parameters
     *
     * @param array $values
     * @return object|bool
     */
    private function getResult(array $values)
    {
        $envVar = $values[0];

        if (in_array($envVar, $this->results['processed'])) {
            return false;
        }

        $this->results['processed'][] = $envVar;

        return (object)[
            'envVar' => $envVar,
            'hasValue' => env($values[0]) !== null,
            'hasDefault' => isset($values[1]),
        ];
    }

    /**
     * Store result and optional runtime output
     *
     * @param string $file
     * @param $result
     */
    private function storeResult(string $file, $result)
    {
        $resultData = [
            'filename' => $this->getFilename($file),
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
            $resultData['undefined'] = $result->envVar;
            $this->results['undefined']++;
        }

        $this->results['data'][] = $resultData;
    }

    private function getFilename(string $file)
    {
        $basename = basename($file);

        if ($this->currentFile === $basename) {
            return '-';
        }

        $this->results['files']++;

        return $this->currentFile = $basename;
    }

    private function recursiveDirSearch(string $folder, string $pattern): array
    {
        if (! file_exists($folder)) {
            return [];
        }

        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folder)
            ),
            $pattern, RegexIterator::GET_MATCH
        );

        $list = [];

        foreach($files as $file) {
            $list = array_merge($list, $file);
        }

        return $list;
    }
}
