<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\Duration;
use qtism\data\storage\xml\XmlDocument;

class AssessmentTestTest extends QtiSmTestCase {
    
	public function testTimeLimits() {
	    $doc = new XmlDocument();
	    $doc->load(self::samplesDir() . 'custom/runtime/timelimits.xml');
	    
	    $testPart = $doc->getDocumentComponent()->getComponentByIdentifier('testPartId');
	    $this->assertTrue($testPart->hasTimeLimits());
	    $timeLimits = $testPart->getTimeLimits();
	    
	    $this->assertTrue($timeLimits->getMinTime()->equals(new Duration('PT60S')));
	    $this->assertTrue($timeLimits->getMaxTime()->equals(new Duration('PT120S')));
	    $this->assertTrue($timeLimits->doesAllowLateSubmission());
	}
}