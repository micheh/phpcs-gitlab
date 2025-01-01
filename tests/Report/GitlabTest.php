<?php

/**
 * @copyright Copyright (c) 2025, Michel Hunziker <info@michelhunziker.com>
 * @license http://www.opensource.org/licenses/BSD-3-Clause The BSD-3-Clause License
 */

declare(strict_types=1);

namespace MichehTest\PhpCodeSniffer\Report;

use Micheh\PhpCodeSniffer\Report\Gitlab;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Reporter;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Runner;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

class GitlabTest extends TestCase
{
    /**
     * @var Gitlab
     */
    private $report;

    /**
     * @var Config
     */
    private static $config;


    public static function setUpBeforeClass(): void
    {
        self::$config = new Config();
        self::$config->basepath = __DIR__ . '/../';
        self::$config->standards = ['PSR12'];

        $runner = new Runner();
        $runner->config = self::$config;
        $runner->init();
    }

    protected function setUp(): void
    {
        $this->report = new Gitlab();
    }

    public function testGenerate(): void
    {
        $this->expectOutputString("[{\"phpunit\":\"test\"}]\n");
        $this->report->generate('{"phpunit":"test"},', 5, 1, 2, 1);
    }

    public function testGenerateWithEmpty(): void
    {
        $this->expectOutputString("[]\n");
        $this->report->generate('', 5, 0, 0, 0);
    }

    /**
     * @return array<string, array<string>>
     */
    public static function violations(): array
    {
        return [
            'single' => ['Single'],
            'mixed' => ['Mixed'],
            'multiple' => ['Multiple'],
            'same-line' => ['SameLine'],
        ];
    }

    /**
     * @dataProvider violations
     */
    public function testGenerateFileReport(string $fileName): void
    {
        $phpPath = __DIR__ . '/../_files/' . $fileName . '.php';
        self::assertFileExists($phpPath);

        $outputPath = __DIR__ . '/../_files/' . $fileName . '.json';
        self::assertFileExists($outputPath);

        $file = $this->createFile($phpPath);

        $this->expectOutputString((string) file_get_contents($outputPath));
        $this->report->generateFileReport($this->getReportData($file), $file);
    }

    private function createFile(string $path): File
    {
        $file = new LocalFile($path, new Ruleset(self::$config), self::$config);
        $file->process();

        return $file;
    }

    /**
     * @return array{filename: string, errors: int, warnings: int, fixable: int, messages: array<mixed>}
     */
    private function getReportData(File $file): array
    {
        $reporter = new Reporter(self::$config);
        return $reporter->prepareFileReport($file); // @phpstan-ignore return.type
    }
}
