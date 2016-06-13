<?php

use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDirectedPair;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class DirectedPairTest extends QtiSmTestCase {

	public function testEquality() {
		$p1 = new QtiDirectedPair('A', 'B');
		$p2 = new QtiDirectedPair('A', 'B');
		$p3 = new QtiDirectedPair('C', 'D');
		$p4 = new QtiPair('A', 'B');
		$p5 = new QtiDirectedPair('D', 'C');
		
		$this->assertTrue($p1->equals($p2));
		$this->assertTrue($p2->equals($p1));
		$this->assertFalse($p1->equals($p3));
		$this->assertFalse($p3->equals($p1));
		$this->assertFalse($p3->equals(1337));
		$this->assertTrue($p3->equals($p3));
		$this->assertFalse($p1->equals($p4));
		$this->assertFalse($p3->equals($p5));
		
		$p7 = new QtiDirectedPair('abc', 'def');
		$p8 = new QtiDirectedPair('def', 'abc');
		$this->assertFalse($p7->equals($p8));
		$this->assertFalse($p8->equals($p7));
	}
}
