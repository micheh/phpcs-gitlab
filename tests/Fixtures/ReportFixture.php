<?php

/**
 * @copyright Copyright (c) 2025, Michel Hunziker <info@michelhunziker.com>
 * @license http://www.opensource.org/licenses/BSD-3-Clause The BSD-3-Clause License
 */

declare(strict_types=1);

namespace MichehTest\PhpCodeSniffer\Fixtures;

interface ReportFixture
{
    /**
     * @return array{filename: string, errors: int, warnings: int, fixable: int, messages: array<mixed>}
     */
    public function getReportData(): array;

    public function getExpectedOutput(): string;
}
