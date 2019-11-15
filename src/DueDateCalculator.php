<?php

namespace Emarsys\Homework;

use DateTimeImmutable;
use DateTimeInterface;
use Emarsys\Homework\Builder\WorkingDayDateBuilder;
use Emarsys\Homework\Contracts\DueDateCalculator as DueDateCalculatorInterface;
use Emarsys\Homework\Exceptions\NonWorkingHourReportedIssueException;
use Emarsys\Homework\ValueObjects\TurnaroundTime;

class DueDateCalculator implements DueDateCalculatorInterface
{
    /** @var WorkingDayDateBuilder */
    private $dateBuilder;

    public function __construct(WorkingDayDateBuilder $dateBuilder)
    {
        $this->dateBuilder = $dateBuilder;
    }

    public function calculateDueDate(string $submitDate, string $turnaroundTime): DateTimeInterface
    {
        $submitDateInstance = new DateTimeImmutable($submitDate);
        $turnaroundTimeInstance = new TurnaroundTime((int) $turnaroundTime);

        return $this->calculate(
            $submitDateInstance,
            $turnaroundTimeInstance
        );
    }

    /**
     * @throws NonWorkingHourReportedIssueException If submit date is not a working hour
     */
    public function calculate(DateTimeImmutable $issueDate, TurnaroundTime $turnaroundTime): DateTimeImmutable
    {
        return $this
            ->dateBuilder
            ->setTargetDate($issueDate)
            ->addHours($turnaroundTime->getTurnaroundHours())
            ->build()
        ;
    }
}
