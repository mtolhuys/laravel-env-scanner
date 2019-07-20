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
        'empty' => 0,
        'has_value' => 0,
        'depending_on_default' => 0,
        'data' => []
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
            $values = array_filter(
                preg_split("#[\n]+#", shell_exec("tr -d '\n' < $file | grep -oP 'env\(\K[^)]+'"))
            );

            foreach ($values as $value) {
                $result = $this->getResult(
                    explode(',', str_replace(["'", '"', ' '], '', $value))
                );

                $this->storeResult($file, $result);
            }
        }

        return $this;
    }

    /**
     * Get result based on comma separated parsed env() parameters
     *
     * @param array $values
     * @return object
     */
    private function getResult(array $values)
    {
        return (object)[
            'envVar' => $values[0],
            'hasValue' => (bool)env($values[0]),
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
        if ($result->hasValue) {
            $this->results['has_value']++;
        } else if ($result->hasDefault) {
            $this->results['depending_on_default']++;
        } else {
            $this->results['empty']++;
        }

        $this->results['data'][] = [
            'filename' => $this->getFilename($file),
            'has_value' => $result->hasValue ? $result->envVar : '-',
            'depending_on_default' => !$result->hasValue && $result->hasDefault ? $result->envVar : '-',
            'empty' => !$result->hasValue && !$result->hasDefault ? $result->envVar : '-',
        ];
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
