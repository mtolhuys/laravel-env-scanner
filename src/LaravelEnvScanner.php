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
        'locations' => 0,
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
        'variables' => [],
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
    private $file;

    /**
     * Current location a found invocation
     *
     * @var string
     */
    private $location;

    /**
     * Current invocation being processed
     *
     * @var string
     */
    private $invocation;

    /**
     * Current parameters being processed
     *
     * @var object
     */
    private $parameters;

    /**
     * Root directory to start recursive search for env()'s from
     * Defaults to config_path()
     *
     * @var string $dir
     */
    public $dir;

    public function __construct(string $dir = null)
    {
        $this->dir = basename($dir ?? config_path());
    }

    /**
     * Run the scan
     *
     * @return mixed
     * @throws \Exception
     */
    public function scan()
    {
        foreach ($this->getFiles() as $file) {
            $lines = explode(PHP_EOL, file_get_contents($file));

            $this->file = $file;

            foreach ($lines as $index => $line) {

                if (preg_match('# env\(| getenv\(#', $line)) {
                    if (! $this->setInvocationDetails($lines, $line, $index)) {
                        continue;
                    }

                    $this->storeResult();
                }
            }
        }

        return $this;
    }

    /**
     * Search for possible matches and make something usable out of it
     *
     * @param array $lines
     * @param string $line
     * @param int $index
     * @return bool
     */
    private function setInvocationDetails(array $lines, string $line, int $index): bool
    {
        $matches = $this->search($lines, $line, $index);

        if (empty(array_filter($matches))) {
            return false;
        }

        $this->setInvocation($matches);
        $this->setParameters($matches);
        $this->setLocation($index + 1);

        if ($this->needsWarning()) {
            return false;
        }

        if ($this->alreadyProcessed()) {
            return false;
        }

        $this->processed['variables'][] = $this->parameters->variable;

        return true;
    }

    /**
     * Search for single and multi-lined env and getenv invocations
     *
     * @param array $lines
     * @param string $line
     * @param int $number
     * @return mixed
     */
    private function search(array $lines, string $line, int $number)
    {
        preg_match_all(
            '# env\((.*?)\)| getenv\((.*?)\)#',
            $line,
            $matches
        );

        $line = str_replace(' ', '', $line);

        if ($line === 'env(' || $line === 'getenv(') {
            $matches = $this->searchMultiLine($lines, $number);
        }

        return $matches;
    }

    /**
     * For multi-line invocation f.e.
     * env(
     *   'MULTI',
     *   'lined'
     * );
     *
     * @param array $lines
     * @param int $number
     * @return mixed
     */
    private function searchMultiLine(array $lines, int $number)
    {
        $search = $lines[$number];
        $search .= $lines[$number + 1];
        $search .= $lines[$number + 2];
        $search .= $lines[$number + 3];

        preg_match_all(
            '# env\((.*?)\)| getenv\((.*?)\)#',
            $search,
            $matches
        );

        return $matches;
    }

    /**
     * Set invocation based on first index in preg_match_all result
     *
     * @param array $matches
     */
    private function setInvocation(array $matches)
    {
        $this->invocation = str_replace(' ', '', str_replace(
            ' ', '', $matches[0]
        )[0]);
    }

    /**
     * Sets parameters based on comma exploding
     * 1 of last indexes in preg_match_all result
     *
     * @param array $matches
     */
    private function setParameters(array $matches): void
    {
        $parameters = empty($matches[1][0]) ? $matches[2][0] : $matches[1][0];
        $parameters = explode(',', str_replace(["'", '"', ' ',], '', $parameters));

        $this->parameters = (object)[
            'variable' => $parameters[0],
            'default' => $parameters[1] ?? null,
        ];
    }

    /**
     * Sets location as filename + linenumber
     *
     * @param int $linenumber
     */
    private function setLocation(int $linenumber): void
    {
        $this->location = "{$this->file}:$linenumber";
    }

    /**
     * Only warn about risky and unreadable invocations
     *
     * @return bool
     */
    private function needsWarning(): bool
    {

        if (!preg_match('/^\w+$/', $this->parameters->variable)) {
            $this->warnings[] =  (object)[
                'invocation' => $this->invocation,
                'location' => $this->location,
            ];

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function alreadyProcessed(): bool
    {
        return in_array($this->parameters->variable, $this->processed['variables'], true);
    }

    private function storeResult(): void
    {
        $resultData = [
            'location' => $this->location,
            'defined' => '-',
            'depending_on_default' => '-',
            'undefined' => '-',
        ];

        $this->results['locations']++;

        if (env($this->parameters->variable) !== null) {
            $resultData['defined'] = $this->parameters->variable;
            $this->results['defined']++;
        } else if ($this->parameters->default) {
            $resultData['depending_on_default'] = $this->parameters->variable;
            $this->results['depending_on_default']++;
        } else {
            $resultData['undefined'] = $this->parameters->variable;
            $this->results['undefined']++;
            $this->undefined[] = [
                'filename' => $this->location,
                'variable' => $this->parameters->variable,
            ];
        }

        $this->results['columns'][] = $resultData;
    }

    private function getFiles(): array
    {
        if (!file_exists($this->dir)) {
            return [];
        }

        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->dir)
            ),
            '/.*?.php/', RegexIterator::GET_MATCH
        );

        $list = [[]];

        foreach ($files as $file) {
            $list[] = $file;
        }

        $list = array_merge(...$list);

        return $list;
    }
}
