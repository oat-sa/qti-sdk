<?php

namespace qtismtest\common\datatypes;

use DateInterval;
use InvalidArgumentException;
use qtism\common\datatypes\QtiDuration;
use qtismtest\QtiSmTestCase;

/**
 * Class DurationTest
 */
class DurationTest extends QtiSmTestCase
{
    /**
     * @dataProvider validDurationProvider
     * @param string $intervalSpec
     */
    public function testValidDurationCreation($intervalSpec)
    {
        $duration = new QtiDuration($intervalSpec);
        $this::assertInstanceOf(QtiDuration::class, $duration);
    }

    /**
     * @dataProvider invalidDurationProvider
     * @param string $intervalSpec
     */
    public function testInvalidDurationCreation($intervalSpec)
    {
        $this->expectException(InvalidArgumentException::class);
        $duration = new QtiDuration($intervalSpec);
    }

    public function testPositiveDuration()
    {
        $duration = new QtiDuration('P3YT6H8M'); // 2 years, 0 days, 6 hours, 8 minutes.
        $this::assertEquals(3, $duration->getYears());
        $this::assertEquals(0, $duration->getMonths());
        $this::assertEquals(0, $duration->getDays());
        $this::assertEquals(6, $duration->getHours());
        $this::assertEquals(8, $duration->getMinutes());
        $this::assertEquals(0, $duration->getSeconds());
    }

    public function testEquality()
    {
        $d1 = new QtiDuration('P1DT12H'); // 1 day + 12 hours.
        $d2 = new QtiDuration('P1DT12H');
        $d3 = new QtiDuration('PT3600S'); // 3600 seconds.

        $this::assertTrue($d1->equals($d2));
        $this::assertTrue($d2->equals($d1));
        $this::assertFalse($d1->equals($d3));
        $this::assertFalse($d3->equals($d1));
        $this::assertTrue($d3->equals($d3));
    }

    public function testClone()
    {
        $d = new QtiDuration('P1DT12H'); // 1 day + 12 hours.
        $c = clone $d;
        $this::assertNotSame($c, $d);
        $this::assertTrue($c->equals($d));
        $this::assertEquals($d->getDays(), $c->getDays());
        $this::assertEquals($d->getHours(), $c->getHours());
        $this::assertEquals($d->getMinutes(), $c->getMinutes());
        $this::assertEquals($d->getSeconds(), $c->getSeconds());
        $this::assertEquals($d->getMonths(), $c->getMonths());
        $this::assertEquals($d->getYears(), $c->getYears());
    }

    /**
     * @dataProvider toStringProvider
     *
     * @param QtiDuration $duration
     * @param string $expected
     */
    public function testToString(QtiDuration $duration, $expected)
    {
        $this::assertEquals($duration->__toString(), $expected);
    }

    public function testAdd()
    {
        $d1 = new QtiDuration('PT1S');
        $d2 = new QtiDuration('PT1S');
        $d1->add($d2);
        $this::assertEquals('PT2S', $d1->__toString());

        $d1 = new QtiDuration('PT23H59M59S');
        $d2 = new QtiDuration('PT10S');
        $d1->add($d2);
        $this::assertEquals('P1DT9S', $d1->__toString());

        $d1 = new QtiDuration('PT1S');
        $d2 = new DateInterval('PT1S');
        $d1->add($d2);
        $this::assertEquals('PT2S', $d1->__toString());
    }

    public function testSub()
    {
        $d1 = new QtiDuration('PT2S');
        $d2 = new QtiDuration('PT1S');
        $d1->sub($d2);
        $this::assertEquals('PT1S', $d1->__toString());

        $d1 = new QtiDuration('PT2S');
        $d2 = new QtiDuration('PT4S');
        $d1->sub($d2);
        $this::assertEquals('PT0S', $d1->__toString());

        $d1 = new QtiDuration('P1DT2H25M30S');
        $d2 = new QtiDuration('P1DT2H');
        $d1->sub($d2);
        $this::assertEquals('PT25M30S', $d1->__toString());

        $d1 = new QtiDuration('PT20S');
        $d2 = new QtiDuration('PT20S');
        $d1->sub($d2);
        $this::assertEquals('PT0S', $d1->__toString());

        $d1 = new QtiDuration('PT20S');
        $d2 = new QtiDuration('PT21S');
        $d1->sub($d2);
        $this::assertTrue($d1->isNegative());
    }

    public function createFromDateInterval()
    {
        $interval = new DateInterval('PT5S');
        $duration = QtiDuration::createFromDateInterval($interval);
        $this::assertEquals(5, $duration->getSeconds(true));
    }

    /**
     * @dataProvider shorterThanProvider
     *
     * @param QtiDuration $duration1
     * @param QtiDuration $duration2
     * @param bool $expected
     */
    public function testShorterThan(QtiDuration $duration1, QtiDuration $duration2, $expected)
    {
        $this::assertSame($expected, $duration1->shorterThan($duration2));
    }

    /**
     * @dataProvider longerThanOrEqualsProvider
     *
     * @param QtiDuration $duration1
     * @param QtiDuration $duration2
     * @param bool $expected
     */
    public function testLongerThanOrEquals(QtiDuration $duration1, QtiDuration $duration2, $expected)
    {
        $this::assertSame($expected, $duration1->longerThanOrEquals($duration2));
    }

    /**
     * @return array
     */
    public function shorterThanProvider()
    {
        $returnValue = [];
        $returnValue[] = [new QtiDuration('P1Y'), new QtiDuration('P2Y'), true];
        $returnValue[] = [new QtiDuration('P1Y'), new QtiDuration('P1Y'), false];
        $returnValue[] = [new QtiDuration('P1Y'), new QtiDuration('P1YT2S'), true];
        $returnValue[] = [new QtiDuration('P2Y'), new QtiDuration('P1Y'), false];
        $returnValue[] = [new QtiDuration('PT0S'), new QtiDuration('PT1S'), true];
        $returnValue[] = [new QtiDuration('PT1H25M0S'), new QtiDuration('PT1H26M12S'), true];
        $returnValue[] = [new QtiDuration('PT1H26M12S'), new QtiDuration('PT1H25M0S'), false];

        return $returnValue;
    }

    /**
     * @return array
     */
    public function longerThanOrEqualsProvider()
    {
        $returnValue = [];
        $returnValue[] = [new QtiDuration('P1Y'), new QtiDuration('P2Y'), false];
        $returnValue[] = [new QtiDuration('P1Y'), new QtiDuration('P1Y'), true];
        $returnValue[] = [new QtiDuration('P1Y'), new QtiDuration('P1YT2S'), false];
        $returnValue[] = [new QtiDuration('P2Y'), new QtiDuration('P1Y'), true];
        $returnValue[] = [new QtiDuration('PT0S'), new QtiDuration('PT1S'), false];
        $returnValue[] = [new QtiDuration('PT1H25M0S'), new QtiDuration('PT1H26M12S'), false];
        $returnValue[] = [new QtiDuration('PT1H26M12S'), new QtiDuration('PT1H25M0S'), true];
        $returnValue[] = [new QtiDuration('PT1H26M'), new QtiDuration('PT1H26M'), true];
        $returnValue[] = [new QtiDuration('PT1M5S'), new QtiDuration('PT1M'), true];
        $returnValue[] = [new QtiDuration('PT1M15S'), new QtiDuration('PT45S'), true];

        return $returnValue;
    }

    /**
     * @return array
     */
    public function validDurationProvider()
    {
        return [
            ['P2D'], // 2 days
            ['PT2S'], // 2 seconds
            ['P6YT5M'] // 6 years, 5 months
        ];
    }

    /**
     * @return array
     */
    public function invalidDurationProvider()
    {
        return [
            ['D2P'],
            ['PSSST'],
            ['Invalid'],
            [''],
        ];
    }

    /**
     * @return array
     */
    public function toStringProvider()
    {
        return [
            [new QtiDuration('P2D'), 'P2D'], // 2 days
            [new QtiDuration('PT2S'), 'PT2S'], // 2 seconds
            [new QtiDuration('P6YT5M'), 'P6YT5M'], // 6 years, 5 months
            [new QtiDuration('PT0S'), 'PT0S'], // 0 seconds
        ];
    }
}
