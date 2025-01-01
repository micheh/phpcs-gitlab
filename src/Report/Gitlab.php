<?php

/**
 * @copyright Copyright (c) 2025, Michel Hunziker <info@michelhunziker.com>
 * @license http://www.opensource.org/licenses/BSD-3-Clause The BSD-3-Clause License
 */

declare(strict_types=1);

namespace Micheh\PhpCodeSniffer\Report;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Reports\Report;
use SplFileObject;

use function count;
use function is_string;
use function md5;
use function preg_replace;
use function rtrim;
use function str_replace;

use const PHP_EOL;

class Gitlab implements Report
{
    /**
     * @param array{filename: string, errors: int, warnings: int, fixable: int, messages: array<mixed>} $report
     */
    public function generateFileReport($report, File $phpcsFile, $showSources = false, $width = 80)
    {
        $hasOutput = false;
        $violations = [];

        foreach ($report['messages'] as $line => $lineErrors) {
            $lineContent = $this->getContentOfLine($phpcsFile->getFilename(), $line);

            foreach ($lineErrors as $col => $colErrors) {
                foreach ($colErrors as $error) {
                    $fingerprint = md5($report['filename'] . $lineContent . $error['source']);

                    $violations[$fingerprint][$line . $col] = [
                        'type' => 'issue',
                        'categories' => ['Style'],
                        'check_name' => $error['source'],
                        'fingerprint' => $fingerprint,
                        'severity' => $error['type'] === 'ERROR' ? 'major' : 'minor',
                        'description' => str_replace(["\n", "\r", "\t"], ['\n', '\r', '\t'], $error['message']),
                        'location' => [
                            'path' => $report['filename'],
                            'lines' => [
                                'begin' => $line,
                                'end' => $line,
                            ]
                        ],
                    ];
                }
            }
        }

        foreach ($violations as $fingerprints) {
            $hasMultiple = count($fingerprints) > 1;
            foreach ($fingerprints as $lineColumn => $issue) {
                if ($hasMultiple) {
                    $issue['fingerprint'] = md5($issue['fingerprint'] . $lineColumn);
                }

                echo json_encode($issue, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ',';
                $hasOutput = true;
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
     * @param string $filename
     * @param int $line
     * @return string
     */
    private function getContentOfLine($filename, $line)
    {
        $file = new SplFileObject($filename);

        if (!$file->eof()) {
            $file->seek($line - 1);
            $contents = $file->current();

            if (is_string($contents)) {
                return (string) preg_replace('/\s+/', '', $contents);
            }
        }

        return '';
    }
}
