<?php

namespace Emarsys\Homework\Contracts;

use DateTimeInterface;

interface DueDateCalculator
{
    public function calculateDueDate(string $submitDate, string $turnaroundTime): DateTimeInterface;
}
