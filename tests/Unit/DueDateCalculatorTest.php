<?php

namespace Emarsys\Homework\Tests\Unit;

use DateTimeImmutable;
use Emarsys\Homework\Builder\WorkingDayDateBuilder;
use Emarsys\Homework\DueDateCalculator;
use Emarsys\Homework\ValueObjects\TurnaroundTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DueDateCalculatorTest extends TestCase
{
    /** @var DueDateCalculator */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new DueDateCalculator(new WorkingDayDateBuilder());
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param string $issueDate
     * @param int    $turnaroundTime
     * @param string $expectedDueDate
     */
    public function testCalculate(string $issueDate, int $turnaroundTime, string $expectedDueDate): void
    {
        $issueDate = new DateTimeImmutable($issueDate);
        $turnaroundTime = new TurnaroundTime($turnaroundTime);
        $expectedDueDate = new DateTimeImmutable($expectedDueDate);

        $result = $this->calculator->calculate($issueDate, $turnaroundTime);

        $this->assertEquals($expectedDueDate, $result);
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testCalculateDueDate(string $issueDate, int $turnaroundTime, string $expectedDueDate): void
    {
        $expectedDueDate = new DateTimeImmutable($expectedDueDate);

        $result = $this->calculator->calculateDueDate($issueDate, $turnaroundTime);

        $this->assertEquals($expectedDueDate, $result);
    }

    public function validDataProvider(): array
    {
        return [
            ['this Tuesday 2:12PM', 1, 'this Tuesday 3:12PM'],
            ['this Tuesday 4:00PM', 1, 'this Tuesday 5:00PM'],
            ['this Tuesday 4:30PM', 1, 'this Wednesday 9:30AM'],
            ['this Tuesday 2:12PM', 16, 'this Thursday 2:12PM'],
            ['this Tuesday 2:12PM', 17, 'this Thursday 3:12PM'],
            ['this Tuesday 9:01AM',  16, 'this Thursday 9:01AM'],
            ['this Tuesday 9:00AM',  16, 'this Wednesday 5PM'],
            ['this Friday 4PM',  16, 'next Tuesday 4PM'],
        ];
    }
}
