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
     * If true
     * combine all config files with files in app folder
     *
     * @var bool
     */
    private $includeAppFolder;

    public function __construct($includeAppFolder = false)
    {
        $this->includeAppFolder = $includeAppFolder;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function scan()
    {
        $files = $this->recursiveGlob(config_path(),  '/.*?.php/');

        if ($this->includeAppFolder) {
            $files = array_merge(
                $this->recursiveGlob(app_path(), '/.*?.php/'), $files
            );
        }

        foreach ($files as $file) {
            $values = array_filter(
                preg_split(
                    "#[\n]+#", shell_exec("tr -d '\n' < $file | grep -oP 'env\(\K[^)]+'")
                )
            );

            foreach ($values as $value) {
                $result = $this->getResult(
                    explode(',', str_replace(["'", '"', ' '], '', $value))
                );

                $this->storeResult($file, $result);
            }
        }
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
            'File' => $this->getFilename($file),
            'Has value' => $result->hasValue ? $result->envVar : '-',
            'Depending on default' => !$result->hasValue && $result->hasDefault ? $result->envVar : '-',
            'No value' => !$result->hasValue && !$result->hasDefault ? $result->envVar : '-',
        ];
    }

    private function getFilename(string $file)
    {
        $basename = basename($file);

        if ($this->currentFile === $basename) {
            return '';
        }

        return $this->currentFile = $basename;
    }

    private function recursiveGlob(string $folder, string $pattern): array
    {
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
