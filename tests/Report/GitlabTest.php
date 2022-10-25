<?php

/**
 * @copyright Copyright (c) 2025, Michel Hunziker <info@michelhunziker.com>
 * @license http://www.opensource.org/licenses/BSD-3-Clause The BSD-3-Clause License
 */

declare(strict_types=1);

namespace MichehTest\PhpCodeSniffer\Report;

use Micheh\PhpCodeSniffer\Report\Gitlab;
use MichehTest\PhpCodeSniffer\Fixtures\MixedViolations;
use MichehTest\PhpCodeSniffer\Fixtures\ReportFixture;
use MichehTest\PhpCodeSniffer\Fixtures\SingleViolation;
use PHP_CodeSniffer\Files\File;
use PHPUnit\Framework\TestCase;

class GitlabTest extends TestCase
{
    /**
     * @var Gitlab
     */
    private $report;


    protected function setUp(): void
    {
        $this->report = new Gitlab();
    }

    /**
     * @covers \Micheh\PhpCodeSniffer\Report\Gitlab::generate
     */
    public function testGenerate(): void
    {
        $this->expectOutputString("[{\"phpunit\":\"test\"}]\n");
        $this->report->generate('{"phpunit":"test"},', 5, 1, 2, 1);
    }

    /**
     * @covers \Micheh\PhpCodeSniffer\Report\Gitlab::generate
     */
    public function testGenerateWithEmpty(): void
    {
        $this->expectOutputString("[]\n");
        $this->report->generate('', 5, 0, 0, 0);
    }

    /**
     * @return array<string, array<class-string<ReportFixture>>>
     */
    public function violations(): array
    {
        return [
            'single' => [SingleViolation::class],
            'mixed' => [MixedViolations::class],
        ];
    }

    /**
     * @param class-string<ReportFixture> $class
     * @covers \Micheh\PhpCodeSniffer\Report\Gitlab::generateFileReport
     * @dataProvider violations
     */
    public function testGenerateFileReport(string $class): void
    {
        $fixture = new $class();
        self::assertInstanceOf(ReportFixture::class, $fixture);
        $fixtureReflection = new \ReflectionClass($class);
        $phpcsFile = $this->createMock(File::class);
        $phpcsFile->method('getFilename')->willReturn($fixtureReflection->getFileName());

        $this->expectOutputString($fixture->getExpectedOutput());
        $this->report->generateFileReport($fixture->getReportData(), $phpcsFile);
    }
}
