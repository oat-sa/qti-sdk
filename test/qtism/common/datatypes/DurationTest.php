<?php
use qtism\common\datatypes\Duration;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class DurationTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider validDurationProvider
	 */
	public function testValidDurationCreation($intervalSpec) {
		$duration = new Duration($intervalSpec);
		$this->assertInstanceOf('qtism\\common\\datatypes\\Duration', $duration);
	}
	
	/**
	 * @dataProvider invalidDurationProvider
	 */
	public function testInvalidDurationCreation($intervalSpec) {
		$this->setExpectedException('\\InvalidArgumentException');
		$duration = new Duration($intervalSpec);
	}
	
	public function testPositiveDuration() {
		$duration = new Duration('P2Y4DT6H8M'); // 2 years, 4 days, 6 hours, 8 minutes.
		$this->assertEquals(2, $duration->getYears());
		$this->assertEquals(4, $duration->getDays());
		$this->assertEquals(6, $duration->getHours());
		$this->assertEquals(8, $duration->getMinutes());
		$this->assertEquals(0, $duration->getMonths());
		$this->assertEquals(0, $duration->getSeconds());
		$this->assertEquals(734, $duration->getDays(true));
	}
	
	public function testEquality() {
		$d1 = new Duration('P1DT12H'); // 1 day + 12 hours.
		$d2 = new Duration('P1DT12H');
		$d3 = new Duration('PT3600S'); // 3600 seconds.
		
		$this->assertTrue($d1->equals($d2));
		$this->assertTrue($d2->equals($d1));
		$this->assertFalse($d1->equals($d3));
		$this->assertFalse($d3->equals($d1));
		$this->assertTrue($d3->equals($d3));
	}
	
	public function testNegativeDuration() {
		$duration = new Duration('P2Y4DT6H8M'); // - 2 years, 4 days, 6 hours, 8 minutes.
	}
	
	public function testClone() {
		$d = new Duration('P1DT12H'); // 1 day + 12 hours.
		$c = clone $d;
		$this->assertFalse($c === $d);
		$this->assertTrue($c->equals($d));
		$this->assertEquals($d->getDays(), $c->getDays());
		$this->assertEquals($d->getHours(), $c->getHours());
		$this->assertEquals($d->getMinutes(), $c->getMinutes());
		$this->assertEquals($d->getSeconds(), $c->getSeconds());
		$this->assertEquals($d->getMonths(), $c->getMonths());
		$this->assertEquals($d->getYears(), $c->getYears());
	}
	
	public function validDurationProvider() {
		return array(
			array('P2D'), // 2 days
			array('PT2S'), // 2 seconds
			array('P6YT5M') // 5 years, 2 seconds	
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
}