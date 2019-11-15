<?php

namespace Emarsys\Homework\ValueObjects;

use UnexpectedValueException;

class TurnaroundTime
{
    /** @var int */
    private $time;

    /**
     * @param int $turnaroundTime
     *
     * @throws UnexpectedValueException If time is invalid
     */
    public function __construct(int $turnaroundTime)
    {
        $this->assertTimeValid($turnaroundTime);
        $this->time = $turnaroundTime;
    }

    public function getTurnaroundHours(): int
    {
        return $this->time;
    }

    /**
     * @param int $turnaroundTime
     *
     * @throws UnexpectedValueException If time is invalid
     */
    private function assertTimeValid(int $turnaroundTime)
    {
        if ($turnaroundTime <= 0) {
            throw new UnexpectedValueException(
                sprintf(
                    'Turnaround time should be greater than zero. [received: %s]',
                    $turnaroundTime
                )
            );
        }
    }
}
