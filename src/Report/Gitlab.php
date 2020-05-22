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
use function str_replace;

use const PHP_EOL;

class Gitlab implements Report
{
    /**
     * @param File $phpcsFile
     * @inheritDoc
     */
    public function generateFileReport($report, File $phpcsFile, $showSources = false, $width = 80): bool
    {
        $hasOutput = false;

        foreach ($report['messages'] as $line => $lineErrors) {
            foreach ($lineErrors as $column => $colErrors) {
                foreach ($colErrors as $error) {
                    $issue = [
                        'type' => 'issue',
                        'categories' => ['Style'],
                        'check_name' => $error['source'],
                        'fingerprint' => md5($report['filename'] . $error['message'] . $line . $column),
                        'description' => str_replace(["\n", "\r", "\t"], ['\n', '\r', '\t'], $error['message']),
                        'location' => [
                            'path' => $report['filename'],
                            'lines' => [
                                'begin' => $line,
                                'end' => $line,
                            ]
                        ],
                    ];

                    if ($hasOutput) {
                        echo ',';
                    }

                    echo json_encode($issue, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $hasOutput = true;
                }
            }
        }

        return $hasOutput;
    }

    /**
     * @inheritDoc
     */
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
    ): void {
        echo '[' . $cachedData . ']' . PHP_EOL;
    }
}
