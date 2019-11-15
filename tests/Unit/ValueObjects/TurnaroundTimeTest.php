<?php

use Emarsys\Homework\ValueObjects\TurnaroundTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class TurnaroundTimeTest extends TestCase
{
    /**
     * @dataProvider validTurnaroundTimeProvider
     *
     * @param int $time
     * @param int $expectedValue
     */
    public function testWithValidData(int $time, int $expectedValue): void
    {
        $time = new TurnaroundTime($time);

        $this->assertSame($expectedValue, $time->getTurnaroundHours());
    }

    /**
     * @dataProvider invalidTurnaroundTimeProvider
     *
     * @param mixed $time
     * @param mixed $expectedException
     */
    public function testWithInvalidData($time, $expectedException): void
    {
        $this->expectException($expectedException);

        new TurnaroundTime($time);
    }

    public function validTurnaroundTimeProvider(): array
    {
        return [
            'time is 1' => [1, 1],
            'time is 16' => [16, 16],
        ];
    }

    public function invalidTurnaroundTimeProvider(): array
    {
        return [
            'time is 0' => [0, UnexpectedValueException::class],
            'time is -10' => [-10, UnexpectedValueException::class],
            'time is string' => ['something wrong', TypeError::class],
        ];
    }
}
