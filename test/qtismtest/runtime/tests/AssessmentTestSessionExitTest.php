<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\common\datatypes\Identifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;

class AssessmentTestSessionExitTest extends QtiSmAssessmentTestSessionTestCase {
    
    public function testLinearAssessmentTestDuring() {
        $url = self::samplesDir() . 'custom/runtime/exits/exitsection.xml';
        $testSession = self::instantiate($url);
        
        $testSession->beginTestSession();
        
        // If we get correct to the first question, we should EXIT SECTION.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        
        // We should arrive at section 2.
        $testSession->moveNext();
        $this->assertEquals('S02', $testSession->getCurrentAssessmentSection()->getIdentifier());
    }
}
