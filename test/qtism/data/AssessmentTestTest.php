<?php

use qtism\common\datatypes\Duration;

require_once (dirname(__FILE__) . '/../../QtiSmTestCase.php');

use qtism\data\storage\xml\XmlAssessmentTestDocument;

class AssessmentTestTest extends QtiSmTestCase {
    
	public function testTimeLimits() {
	    $doc = new XmlAssessmentTestDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/timelimits.xml');
	    
	    $testPart = $doc->getComponentByIdentifier('testPartId');
	    $this->assertTrue($testPart->hasTimeLimits());
	    $timeLimits = $testPart->getTimeLimits();
	    
	    $this->assertTrue($timeLimits->getMinTime()->equals(new Duration('PT60S')));
	    $this->assertTrue($timeLimits->getMaxTime()->equals(new Duration('PT120S')));
	    $this->assertTrue($timeLimits->doesAllowLateSubmission());
	}
}