<?php

use Emarsys\Homework\Builder\WorkingDayDateBuilder;
use Emarsys\Homework\Exceptions\NonWorkingHourReportedIssueException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class WorkingDayDateBuilderTest extends TestCase
{
    /** @var WorkingDayDateBuilder */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new WorkingDayDateBuilder();
    }

    public function addIntervalDataProvider(): array
    {
        return [
            ['Tuesday 2:12PM', 'PT16H', 'Thursday 2:12PM'],
            ['Monday 10AM', 'PT1H', 'Monday 11AM'],
            ['Monday 4PM', 'PT1H', 'Monday 5PM'],
            ['Monday 4PM', 'PT2H', 'Tuesday 10AM'],
            ['Monday 4:30PM', 'PT2H10M', 'Tuesday 10:40AM'],
            ['Friday 4PM', 'PT2H', 'Monday 10AM'],
        ];
    }

    /**
     * @dataProvider addIntervalDataProvider
     */
    public function testAddHours(string $targetDate, string $interval, string $expectedResult)
    {
        $result = $this
            ->builder
            ->setTargetDate(new DateTimeImmutable($targetDate))
            ->addInterval(new DateInterval($interval))
            ->build()
        ;

        $this->assertEquals(new DateTimeImmutable($expectedResult), $result);
    }

    /**
     * @dataProvider invalidWorkingDateDateProvider
     *
     * @param string $dateTime
     */
    public function testWillThrowExceptionIfOutOfWorkingHours(string $dateTime): void
    {
        $this->expectException(NonWorkingHourReportedIssueException::class);

        $this->builder->setTargetDate(new DateTimeImmutable($dateTime))->build();
    }

    public function testBuilderThrowsExceptionIfNoTargetDateSet()
    {
        $this->expectException(RuntimeException::class);

        $this->builder->build();
    }

    public function invalidWorkingDateDateProvider(): array
    {
        return [
            'Saturday 10am' => ['Saturday 10am'],
            'Sunday 10am' => ['Sunday 10am'],
            '8:59am' => ['8:59am'],
            '5:01pm' => ['5:01pm'],
            '5:01am' => ['5:01am'],
        ];
    }

    /**
     * @dataProvider validWorkingDateDateProvider
     *
     * @param string $dateTime
     */
    public function testCanAcceptValidDateTime(string $dateTime): void
    {
        $providedDateTime = new DateTimeImmutable($dateTime);
        $issueDate = $this->builder->setTargetDate($providedDateTime)->build();

        $this->assertEquals($providedDateTime, $issueDate);
    }

    public function validWorkingDateDateProvider(): array
    {
        return [
            'Monday 9am' => ['Monday 9AM'],
            'Tuesday 9:30 am' => ['Tuesday 9:30 AM'],
            'Wednesday 5PM' => ['Wednesday 5PM'],
            'Thursday 5PM' => ['Thursday 5PM'],
            'Friday 5PM' => ['Thursday 5PM'],
        ];
    }
}
