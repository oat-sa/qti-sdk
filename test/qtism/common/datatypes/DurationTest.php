<?php
use qtism\common\datatypes\QtiDuration;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class DurationTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider validDurationProvider
	 */
	public function testValidDurationCreation($intervalSpec) {
		$duration = new QtiDuration($intervalSpec);
		$this->assertInstanceOf(QtiDuration::class, $duration);
	}
	
	/**
	 * @dataProvider invalidDurationProvider
	 */
	public function testInvalidDurationCreation($intervalSpec) {
		$this->setExpectedException('\\InvalidArgumentException');
		$duration = new QtiDuration($intervalSpec);
	}
	
	public function testPositiveDuration() {
		$duration = new QtiDuration('P3Y0DT6H8M'); // 2 years, 0 days, 6 hours, 8 minutes, 0 seconds, 0 microseconds.
		$this->assertEquals(3, $duration->getYears());
		$this->assertEquals(0, $duration->getMonths());
		$this->assertEquals(0, $duration->getDays());
		$this->assertEquals(6, $duration->getHours());
		$this->assertEquals(8, $duration->getMinutes());
		$this->assertEquals(0, $duration->getSeconds());
		$this->assertEquals(0, $duration->getMicroseconds());
	}
	
	public function testEquality() {
		$d1 = new QtiDuration('P1DT12H'); // 1 day + 12 hours.
		$d2 = new QtiDuration('P1DT12H');
		$d3 = new QtiDuration('PT3600S'); // 3600 seconds.
		
		$this->assertTrue($d1->equals($d2));
		$this->assertTrue($d2->equals($d1));
		$this->assertFalse($d1->equals($d3));
		$this->assertFalse($d3->equals($d1));
		$this->assertTrue($d3->equals($d3));
	}

	public function testConstruct()
	{
		$duration = new QtiDuration('PT10.10S');
		$this->assertEquals('PT10.100000S', $duration->__toString());

		$duration = new QtiDuration('PT10.010S');
		$this->assertEquals('PT10.010000S', $duration->__toString());

		$duration = new QtiDuration('PT10.000001S');
		$this->assertEquals('PT10.000001S', $duration->__toString());
	}
	
	public function testClone() {
		$d = new QtiDuration('P1DT12H12M12.0012S'); // 1 day + 12 hours + 12 minutes + 12 seconds and 1200 microseconds
		$c = clone $d;
		$this->assertFalse($c === $d);
		$this->assertTrue($c->equals($d));
		$this->assertEquals($d->getDays(), $c->getDays());
		$this->assertEquals($d->getHours(), $c->getHours());
		$this->assertEquals($d->getMinutes(), $c->getMinutes());
		$this->assertEquals($d->getSeconds(), $c->getSeconds());
		$this->assertEquals($d->getMicroseconds(), $c->getMicroseconds());
		$this->assertEquals($d->getMonths(), $c->getMonths());
		$this->assertEquals($d->getYears(), $c->getYears());
	}
	
	/**
	 * @dataProvider toStringProvider
	 * 
	 * @param Duration $duration
	 * @param string $expected
	 */
	public function testToString(QtiDuration $duration, $expected) {
		$this->assertEquals($duration->__toString(), $expected);
	}
	
	public function testAdd() {
		$d1 = new QtiDuration('PT1S');
		$d2 = new QtiDuration('PT1S');
		$d1->add($d2);
		$this->assertEquals('PT2S', $d1->__toString());

		$d1 = new QtiDuration('PT23H59M59S');
		$d2 = new QtiDuration('PT10S');
		$d1->add($d2);
		$this->assertEquals('P1DT9S', $d1->__toString());

		$d1 = new QtiDuration('PT1.000500S');
		$d2 = clone $d1;
		$d1->add($d2);
		$this->assertEquals('PT2.001000S', $d1->__toString());
	}
	
	public function testSub() {
	    $d1 = new QtiDuration('PT2S');
	    $d2 = new QtiDuration('PT1S');
	    $d1->sub($d2);
	    $this->assertEquals('PT1S', $d1->__toString());

	    $d1 = new QtiDuration('PT2S');
	    $d2 = new QtiDuration('PT4S');
	    $d1->sub($d2);
	    $this->assertEquals('PT0S', $d1->__toString());

	    $d1 = new QtiDuration('P1DT2H25M30S');
	    $d2 = new QtiDuration('P1DT2H');
	    $d1->sub($d2);
	    $this->assertEquals('PT25M30S', $d1->__toString());

	    $d1 = new QtiDuration('PT20S');
	    $d2 = new QtiDuration('PT20S');
	    $d1->sub($d2);
	    $this->assertEquals('PT0S', $d1->__toString());

		$d1 = new QtiDuration('PT20.005S');
		$d2 = new QtiDuration('PT20.003S');
		$d1->sub($d2);
		$this->assertEquals('PT0.002000S', $d1->__toString());

		$d1 = new QtiDuration('PT0.003S');
		$d2 = new QtiDuration('PT0.005S');
		$d1->sub($d2);
		$this->assertEquals('PT0S', $d1->__toString());

	    $d1 = new QtiDuration('PT20S');
	    $d2 = new QtiDuration('PT21S');
	    $d1->sub($d2);
	    $this->assertTrue($d1->isNegative());
	}
		
	/**
	 * @dataProvider shorterThanProvider
	 * 
	 * @param Duration $duration1
	 * @param Duration $duration2
	 * @param boolean $expected
	 */
	public function testShorterThan(QtiDuration $duration1, QtiDuration $duration2, $expected) {
		$this->assertSame($expected, $duration1->shorterThan($duration2));
	}
	
	/**
	 * @dataProvider longerThanOrEqualsProvider
	 *
	 * @param Duration $duration1
	 * @param Duration $duration2
	 * @param boolean $expected
	 */
	public function testLongerThanOrEquals(QtiDuration $duration1, QtiDuration $duration2, $expected) {
		$this->assertSame($expected, $duration1->longerThanOrEquals($duration2));
	}
	
	public function shorterThanProvider() {
		$returnValue = array();
		$returnValue[] = array(new QtiDuration('P1Y'), new QtiDuration('P2Y'), true);
		$returnValue[] = array(new QtiDuration('P1Y'), new QtiDuration('P1Y'), false);
		$returnValue[] = array(new QtiDuration('P1Y'), new QtiDuration('P1YT2S'), true);
		$returnValue[] = array(new QtiDuration('P2Y'), new QtiDuration('P1Y'), false);
		$returnValue[] = array(new QtiDuration('PT0S'), new QtiDuration('PT1S'), true);
		$returnValue[] = array(new QtiDuration('PT1H25M0S'), new QtiDuration('PT1H26M12S'), true);
		$returnValue[] = array(new QtiDuration('PT1H26M12S'), new QtiDuration('PT1H25M0S'), false);
		
		return $returnValue;
	}
	
	public function longerThanOrEqualsProvider() {
		$returnValue = array();
		$returnValue[] = array(new QtiDuration('P1Y'), new QtiDuration('P2Y'), false);
		$returnValue[] = array(new QtiDuration('P1Y'), new QtiDuration('P1Y'), true);
		$returnValue[] = array(new QtiDuration('P1Y'), new QtiDuration('P1YT2S'), false);
		$returnValue[] = array(new QtiDuration('P2Y'), new QtiDuration('P1Y'), true);
		$returnValue[] = array(new QtiDuration('PT0S'), new QtiDuration('PT1S'), false);
		$returnValue[] = array(new QtiDuration('PT1H25M0S'), new QtiDuration('PT1H26M12S'), false);
		$returnValue[] = array(new QtiDuration('PT1H26M12S'), new QtiDuration('PT1H25M0S'), true);
		$returnValue[] = array(new QtiDuration('PT1H26M'), new QtiDuration('PT1H26M'), true);
	
		return $returnValue;
	}
	
	public function validDurationProvider() {
		return array(
			array('P2D'), // 2 days
			array('PT2S'), // 2 seconds
			array('P6YT5M') // 6 years, 5 months
		);
	}
	
	public function invalidDurationProvider() {
		return array(
			array('D2P'),
			array('PSSST'),
			array('Invalid'),
			array('')
		);
	}
	
	public function toStringProvider() {
		return array(
			array(new QtiDuration('P2D'), 'P2D'), // 2 days
			array(new QtiDuration('PT2S'), 'PT2S'), // 2 seconds
			array(new QtiDuration('P6YT5M'), 'P6YT5M'), // 6 years, 5 months
			array(new QtiDuration('PT0S'), 'PT0S'), // 0 seconds
			array(new QtiDuration('PT0.005S'), 'PT0.005000S'), // 5 milliseconds
		);
	}
}
