<?php

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;

require_once (dirname(__FILE__) . '/../../../QtiSmAssessmentTestSessionTestCase.php');

class AssessmentTestSessionPreConditionsTest extends QtiSmAssessmentTestSessionTestCase {
	
    public function testInstantiationSample1() {
        
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_single_section_linear.xml');
        $route = $testSession->getRoute();
        
        // Q01 - No precondtions.
        $routeItem = $route->getRouteItemAt(0);
        $this->assertEquals(0, count($routeItem->getPreConditions()));
        
        // Q02 - A precondition based on Q01.SCORE.
        $routeItem = $route->getRouteItemAt(1);
        $preConditions = $routeItem->getPreConditions();
        $this->assertEquals(1, count($preConditions));
        $var = $preConditions[0]->getComponentsByClassName('variable');
        $this->assertEquals('Q01.SCORE', $var[0]->getIdentifier());
        
        // Q03 - A precondition based on Q02.SCORE.
        $routeItem = $route->getRouteItemAt(2);
        $preConditions = $routeItem->getPreConditions();
        $this->assertEquals(1, count($preConditions));
        $var = $preConditions[0]->getComponentsByClassName('variable');
        $this->assertEquals('Q02.SCORE', $var[0]->getIdentifier());
        
        // Q04 - A precondition based on Q03.SCORE.
        $routeItem = $route->getRouteItemAt(3);
        $preConditions = $routeItem->getPreConditions();
        $this->assertEquals(1, count($preConditions));
        $var = $preConditions[0]->getComponentsByClassName('variable');
        $this->assertEquals('Q03.SCORE', $var[0]->getIdentifier());
    }
    
    public function testSingleSectionLinear1() {

        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_single_section_linear.xml');
        $testSession->beginTestSession();
        
        // Q01 - Answer incorrect to be redirected by successive false evaluated preconditions.
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceB'))));
        
        // Because of the autoforward, the test is finished.
        $this->assertFalse($testSession->isRunning());
        $this->assertInternalType('float', $testSession['Q01.SCORE']);
        $this->assertEquals(0.0, $testSession['Q01.SCORE']);
        $this->assertSame(null, $testSession['Q02.SCORE']);
        $this->assertSame(null, $testSession['Q03.SCORE']);
        $this->assertSame(null, $testSession['Q04.SCORE']);
    }
    
    public function testKillerTestEpicFail() {
        
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_killertest.xml');
        $testSession->beginTestSession();
        
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'BadChoice'))));
        
        // Incorrect answer = end of test.
        $this->assertFalse($testSession->isRunning());
        $this->assertEquals(0.0, $testSession['Q01.SCORE']);
        $this->assertInternalType('float', $testSession['Q01.SCORE']);
        
        // Other items could not be instantiated.
        $this->assertSame(null, $testSession['Q02.SCORE']);
        $this->assertSame(null, $testSession['Q03.SCORE']);
        $this->assertSame(null, $testSession['Q04.SCORE']);
        $this->assertSame(null, $testSession['Q05.SCORE']);
    }
    
    public function testKillerTestEpicWin() {
        $testSession = self::instantiate(self::samplesDir() . 'custom/runtime/preconditions/preconditions_killertest.xml');
        $testSession->beginTestSession();
        
        $this->assertEquals('Q01', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'GoodChoice'))));
        $this->assertEquals(1.0, $testSession['Q01.SCORE']);
        
        $this->assertEquals('Q02', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'GoodChoice'))));
        $this->assertEquals(1.0, $testSession['Q02.SCORE']);
        
        $this->assertEquals('Q03', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'GoodChoice'))));
        $this->assertEquals(1.0, $testSession['Q03.SCORE']);
        
        $this->assertEquals('Q04', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'GoodChoice'))));
        $this->assertEquals(1.0, $testSession['Q04.SCORE']);
        
        $this->assertEquals('Q05', $testSession->getCurrentAssessmentItemRef()->getIdentifier());
        $testSession->beginAttempt();
        $testSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'GoodChoice'))));
        $this->assertEquals(1.0, $testSession['Q05.SCORE']);
        
        $this->assertFalse($testSession->isRunning());
    }
}