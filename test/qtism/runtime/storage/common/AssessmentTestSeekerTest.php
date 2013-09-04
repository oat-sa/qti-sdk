<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;
use qtism\runtime\storage\common\AssessmentTestSeeker;
use \OutOfBoundsException;

class AssessmentTestSeekerTest extends QtiSmTestCase {
	
    public function testAssessmentTestSeekerBasic() {
        
        $doc = new XmlCompactAssessmentTestDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        
        $seeker = new AssessmentTestSeeker($doc, array('assessmentItemRef', 'assessmentSection'));
        
        $ref = $seeker->seek('assessmentItemRef', 0);
        $this->assertEquals('Q01', $ref->getIdentifier());
        
        $ref = $seeker->seek('assessmentItemRef', 3);
        $this->assertEquals('Q04', $ref->getIdentifier());
        
        $sec = $seeker->seek('assessmentSection', 0);
        $this->assertEquals('S01', $sec->getIdentifier());
        
        $ref = $seeker->seek('assessmentItemRef', 6);
        $this->assertEquals('Q07', $ref->getIdentifier());
        
        $sec = $seeker->seek('assessmentSection', 2);
        $this->assertEquals('S03', $sec->getIdentifier());
        
        // Should not be found.
        try {
            $ref = $seeker->seek('responseProcessing', 25);
            $this->assertFalse(true, "The 'responseProcessing' QTI class is not registered with the AssessmentTestSeeker object.");
        }
        catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
        
        try {
            $ref = $seeker->seek('assessmentItemRef', 100);
            $this->assertFalse(true, "Nothing should be found for 'assessmentItemRef' at position '100'. This is out of bounds.");
        }
        catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
    }
}