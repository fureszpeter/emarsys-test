<?php

namespace Emarsys\Homework\Builder;

use DateInterval;
use DateTimeImmutable;
use Emarsys\Homework\Exceptions\NonWorkingHourReportedIssueException;
use RuntimeException;

class WorkingDayDateBuilder
{
    /** @var string */
    private $dayBegins;

    /** @var string */
    private $dayEnds;

    /** @var array */
    private $workDays;

    /** @var null|DateTimeImmutable */
    private $targetDate;

    /**
     * @param string   $dayBegins
     * @param string   $dayEnds
     * @param string[] $workDays
     */
    public function __construct(
        string $dayBegins = '9AM',
        string $dayEnds = '5PM',
        array $workDays = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
        ]
    ) {
        $this->dayBegins = $dayBegins;
        $this->dayEnds = $dayEnds;
        $this->workDays = $workDays;
    }

    public function addInterval(DateInterval $interval): WorkingDayDateBuilder
    {
        $intervalInSeconds = self::intervalToSeconds($interval);
        $remainingWorkingTimeInSeconds = self::intervalToSeconds($this->remainingWorkingTimeToday());

        if (!$this->isWorkingDay()) {
            return $this
                ->setNextDay()
                ->addInterval($interval)
                ;
        }
        if ($remainingWorkingTimeInSeconds >= $intervalInSeconds) {
            return $this->setTargetDate($this->getTargetDate()->add($interval));
        }

        return $this
            ->setNextMorning()
            ->addInterval(
                new DateInterval('PT'.($intervalInSeconds - $remainingWorkingTimeInSeconds).'S')
            )
        ;
    }

    public function addHours(int $hours): WorkingDayDateBuilder
    {
        return $this->addInterval(new DateInterval('PT'.$hours.'H'));
    }

    public function setNextDay(): WorkingDayDateBuilder
    {
        return $this->setTargetDate($this->getTargetDate()->modify('+1 day'));
    }

    public function setNextMorning(): WorkingDayDateBuilder
    {
        return $this->setTargetDate($this->getTargetDate()->modify('+1 day')->modify($this->getDayBegins()));
    }

    /**
     * @throws RuntimeException
     */
    public function assertDateSet(): void
    {
        if (null === $this->targetDate) {
            throw new RuntimeException(
                'You must set target date first.'
            );
        }
    }

    public function getEndOfDay(): DateTimeImmutable
    {
        return $this->getTargetDate()->modify($this->getDayEnds());
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getTargetDate(): ?DateTimeImmutable
    {
        return $this->targetDate;
    }

    /**
     * @param null|DateTimeImmutable $targetDate
     *
     * @return $this
     */
    public function setTargetDate(DateTimeImmutable $targetDate): WorkingDayDateBuilder
    {
        $this->targetDate = $targetDate;

        return $this;
    }

    public function getDayEnds(): string
    {
        return $this->dayEnds;
    }

    public function isWorkingDay(DateTimeImmutable $dateTime = null): bool
    {
        $subject = $dateTime ?? $this->getTargetDate();

        return in_array($subject->format('l'), $this->getWorkDays());
    }

    /**
     * @return string[]
     */
    public function getWorkDays(): array
    {
        return $this->workDays;
    }

    public function getStartOfDay(): DateTimeImmutable
    {
        return $this->getTargetDate()->modify($this->getDayBegins());
    }

    public function getDayBegins(): string
    {
        return $this->dayBegins;
    }

    public function build(): DateTimeImmutable
    {
        $this->assertDateSet();

        $errors = [];
        if (!$this->isWorkingDay()) {
            $errors[] = 'Not a working day';
        }

        if (!$this->isInWorkingTimeRange()) {
            $errors[] = 'Not in time range';
        }

        if (!empty($errors)) {
            throw new NonWorkingHourReportedIssueException(implode("\n", $errors));
        }

        return $this->targetDate;
    }

    public function isInWorkingTimeRange(): bool
    {
        return $this->getTargetDate() >= $this->getTargetDate()->modify($this->getDayBegins())
            && $this->getTargetDate() <= $this->getTargetDate()->modify($this->getDayEnds());
    }

    private static function intervalToSeconds(DateInterval $interval): int
    {
        return $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
    }

    private function remainingWorkingTimeToday(): DateInterval
    {
        return $this->getEndOfDay()->diff($this->getTargetDate());
    }
}
