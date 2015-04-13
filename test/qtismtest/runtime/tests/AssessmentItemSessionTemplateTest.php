<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentItemTestCase;
use qtism\common\datatypes\Identifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\ItemSessionControl;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\tests\AssessmentItemSession;

class AssessmentItemSessionTemplateTest extends QtiSmAssessmentItemTestCase {
    
    public function testAssigningScoresAndCorrectResponses() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/items/template_processing.xml');
        
        $session = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);
        
        $session->setItemSessionControl($itemSessionControl);
        $session->beginItemSession();
        
        // Check that the templateProcessing was correctly processed.
        $this->assertEquals('ChoiceA', $session->getVariable('RESPONSE')->getCorrectResponse()->getValue());
        $this->assertEquals(1.0, $session['GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['WRONGSCORE']->getValue());
        
        // Check that it really works...
        // With a correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))
        );
        $session->endAttempt($responses);
        $this->assertEquals(1.0, $session['SCORE']->getValue());
        
        // With an incorrect response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))
        );
        $session->endAttempt($responses);
        $this->assertEquals(0.0, $session['SCORE']->getValue());
    }
}
