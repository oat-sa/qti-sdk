<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentItemTestCase;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;

class AssessmentItemSessionShufflingTest extends QtiSmAssessmentItemTestCase {
    
    public function testShufflingOccurs() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'ims/items/2_1/choice_fixed.xml');
        
        $session = new AssessmentItemSession($doc->getDocumentComponent());
        $session->beginItemSession();
        
        $shufflingStates = $session->getShufflingStates();
        $this->assertCount(1, $shufflingStates);
        
        $shufflingGroups = $shufflingStates[0]->getShufflingGroups();
        $this->assertCount(1, $shufflingGroups);
        $this->assertCount(4, $shufflingGroups[0]->getIdentifiers());
        $this->assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceA'));
        $this->assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceB'));
        $this->assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceC'));
        $this->assertTrue($shufflingGroups[0]->getIdentifiers()->contains('ChoiceD'));
    }
}
