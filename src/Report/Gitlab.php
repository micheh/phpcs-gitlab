<?php

/**
 * @copyright Copyright (c) 2020, Michel Hunziker <info@michelhunziker.com>
 * @license http://www.opensource.org/licenses/BSD-3-Clause The BSD-3-Clause License
 */

declare(strict_types=1);

namespace Micheh\PhpCodeSniffer\Report;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Reports\Report;

use function md5;
use function rtrim;
use function str_replace;

use const PHP_EOL;

class Gitlab implements Report
{
    /**
     * Gitlab predefined environment name
     * @see https://docs.gitlab.com/13.8/ee/ci/variables/predefined_variables.html
     */
    public const GITLAB_PROJECT_DIR_ENV = 'CI_PROJECT_DIR';

    /**
     * @psalm-suppress ImplementedParamTypeMismatch PHP_CodeSniffer has a wrong docblock
     */
    public function generateFileReport($report, File $phpcsFile, $showSources = false, $width = 80)
    {
        $hasOutput = false;

        foreach ($report['messages'] as $line => $lineErrors) {
            foreach ($lineErrors as $column => $colErrors) {
                foreach ($colErrors as $error) {
                    $filename = $this->removeBaseFromLocationPath($report['filename']);

                    $issue = [
                        'type' => 'issue',
                        'categories' => ['Style'],
                        'check_name' => $error['source'],
                        'fingerprint' => md5($filename . $error['message'] . $line . $column),
                        'severity' => $error['type'] === 'ERROR' ? 'major' : 'minor',
                        'description' => str_replace(["\n", "\r", "\t"], ['\n', '\r', '\t'], $error['message']),
                        'location' => [
                            'path' => $filename,
                            'lines' => [
                                'begin' => $line,
                                'end' => $line,
                            ]
                        ],
                    ];

                    echo json_encode($issue, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ',';
                    $hasOutput = true;
                }
            }
        }

        return $hasOutput;
    }

    public function generate(
        $cachedData,
        $totalFiles,
        $totalErrors,
        $totalWarnings,
        $totalFixable,
        $showSources = false,
        $width = 80,
        $interactive = false,
        $toScreen = true
    ) {
        echo '[' . rtrim($cachedData, ',') . ']' . PHP_EOL;
    }

    /**
     * Removes Gitlab build directory from location
     *
     * @param string $path
     * @return string
     */
    private function removeBaseFromLocationPath(string $path)
    {
        $dir = getenv(self::GITLAB_PROJECT_DIR_ENV);

        return $dir
            ? str_replace($dir, '', $path) // isn't right way, because we need remove first 'a' from '/a/a/'
            : $path;
    }
}
