<?php

use qtism\common\datatypes\QtiDuration;

require_once (dirname(__FILE__) . '/../../QtiSmTestCase.php');

use qtism\data\storage\xml\XmlDocument;

class AssessmentTestTest extends QtiSmTestCase {
    
	public function testTimeLimits() {
	    $doc = new XmlDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/timelimits.xml');
	    
	    $testPart = $doc->getDocumentComponent()->getComponentByIdentifier('testPartId');
	    $this->assertTrue($testPart->hasTimeLimits());
	    $timeLimits = $testPart->getTimeLimits();
	    
	    $this->assertTrue($timeLimits->getMinTime()->equals(new QtiDuration('PT60S')));
	    $this->assertTrue($timeLimits->getMaxTime()->equals(new QtiDuration('PT120S')));
	    $this->assertTrue($timeLimits->doesAllowLateSubmission());
	}
}
