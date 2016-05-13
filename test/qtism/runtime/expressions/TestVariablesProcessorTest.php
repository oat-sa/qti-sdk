<?php

use qtism\common\datatypes\QtiIdentifier;

use qtism\common\datatypes\QtiFloat;

use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPair;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\common\collections\IdentifierCollection;
use qtism\data\expressions\TestVariables;
use qtism\runtime\expressions\TestVariablesProcessor;

require_once (dirname(__FILE__) . '/../../../QtiSmItemSubsetTestCase.php');

class TestVariablesProcessorTest extends QtiSmItemSubsetTestCase {
	
    /**
     * @dataProvider testVariablesProvider
     * 
     * @param TestVariables $expression
     * @param int $expectedResult
     */
	public function testTestVariables(TestVariables $expression, $expectedResult) {
		$session = $this->getTestSession();
		
		// S01.Q01 - set a correct response 'ChoiceA', scoring = 1.0
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q01.scoring']);
		$this->assertEquals(1.0, $session['Q01.scoring']->getValue());
		$session->moveNext();
		
		// S01.Q02 - set an incorrect response but close to the correct one ['A P', 'D L'], SCORE = 3
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new QtiPair('A', 'P'), new QtiPair('D', 'L'))))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q02.SCORE']);
		$this->assertEquals(3.0, $session['Q02.SCORE']->getValue());
		$session->moveNext();
		
		// S01.Q03 - set a correct response ['H', '0'], SCORE = 2
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('H'), new QtiIdentifier('O'))))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q03.SCORE']);
		$this->assertEquals(2.0, $session['Q03.SCORE']->getValue());
		$session->moveNext();
		
		// S02.Q04 - set an incorrect response ['W Sp', 'G2 Su'], SCORE = 0
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new QtiDirectedPair('W', 'Sp'), new QtiDirectedPair('G2', 'Su'))))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q04.SCORE']);
		$this->assertEquals(0.0, $session['Q04.SCORE']->getValue());
		$session->moveNext();
		
		// S02.Q05 - set an incorrect response ['C B', 'C D', 'B D'], SCORE = 1 (max = 2)
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new QtiPair('C', 'B'), new QtiPair('C', 'D'), new QtiPair('B', 'D'))))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q05.SCORE']);
		$this->assertEquals(1.0, $session['Q05.SCORE']->getValue());
		$session->moveNext();
		
		// S02.Q06 - set an correct response 'A', mySc0r3 = 1
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('A'))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q06.mySc0r3']);
		$this->assertEquals(1.0, $session['Q06.mySc0r3']->getValue());
		$session->moveNext();
		
		// S03.Q07.1 - set an incorrect but in shape response '105 105', SCORE = 1
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(105, 105))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q07.1.SCORE']);
		$this->assertEquals(1.0, $session['Q07.1.SCORE']->getValue());
		$session->moveNext();
		
		// S03.Q07.2 - set a perfectly correct response '102 113', SCORE = 1
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q07.2.SCORE']);
		$this->assertEquals(1.0, $session['Q07.2.SCORE']->getValue());
		$session->moveNext();
		
		// S03.Q07.3 - set an absolutely incorrect response '13 37', SCORE = 0
		$session->beginAttempt();
		$responses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(13, 37))));
		$session->endAttempt($responses);
		$this->assertInstanceOf(QtiFloat::class, $session['Q07.3.SCORE']);
		$this->assertEquals(0.0, $session['Q07.3.SCORE']->getValue());
		$session->moveNext();
		
		$processor = new TestVariablesProcessor($expression);
	    $processor->setState($session);
		$result = $processor->process();
		
		$this->assertInstanceOf('qtism\\runtime\\common\\MultipleContainer', $result);
		$this->assertTrue($result->equals($expectedResult));
		
	}
	
	public function testVariablesProvider() {
	    return array(
	        array(self::getTestVariables('SCORE'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(3.0), new QtiFloat(2.0), new QtiFloat(0.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(0.0)))),
	        array(self::getTestVariables('scoring'), new MultipleContainer(BaseType::FLOAT)),
	        array(self::getTestVariables('SCORE', -1, '', 'S01'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(3.0), new QtiFloat(2.0)))),
	        array(self::getTestVariables('SCORE', -1, '', 'S02'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(1.0), new QtiFloat(1.0)))),
	        array(self::getTestVariables('SCORE', -1, '', 'S03'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(0.0)))),
	        array(self::getTestVariables('SCORE', -1, 'W01'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(2.0), new QtiFloat(6.0), new QtiFloat(4.0), new QtiFloat(0.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(0.0)))),
	        array(self::getTestVariables('SCORE', -1, 'W01', 'S01'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(2.0), new QtiFloat(6.0), new QtiFloat(4.0)))),
	        array(self::getTestVariables('SCORE', -1, 'W01', 'S02'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(2.0), new QtiFloat(2.0)))),
	        array(self::getTestVariables('SCORE', -1, 'W01', 'S03'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(2.0), new QtiFloat(2.0), new QtiFloat(0.0)))),
	        array(self::getTestVariables('SCORE', -1, 'W0X'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(3.0), new QtiFloat(2.0), new QtiFloat(0.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(0.0)))), // Weight not found, no weight applied
	        array(self::getTestVariables('SCORE', -1, 'W0X', 'S01'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(3.0), new QtiFloat(2.0)))), // same as previous
	        array(self::getTestVariables('SCORE', -1, 'W0X', 'S02'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(0.0), new QtiFloat(1.0), new QtiFloat(1.0)))), // same as previous
	        array(self::getTestVariables('SCORE', -1, 'W0X', 'S03'), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(0.0)))), // same as previous
	        array(self::getTestVariables('SCORE', BaseType::FLOAT), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(3.0), new QtiFloat(2.0), new QtiFloat(0.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(1.0), new QtiFloat(0.0)))),
	        array(self::getTestVariables('SCORE', BaseType::INTEGER), new MultipleContainer(BaseType::FLOAT, array())),
	        array(self::getTestVariables('SCORE', BaseType::FLOAT, '', '', new IdentifierCollection(array('mathematics'))), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(1.0), new QtiFloat(2.0), new QtiFloat(1.0)))),
	        array(self::getTestVariables('SCORE', BaseType::FLOAT, '', '', null, new IdentifierCollection(array('mathematics', 'minimum'))), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(3.0), new QtiFloat(0.0)))),
	        array(self::getTestVariables('SCORE', -1, 'W01', 'S01', null, new IdentifierCollection(array('mathematics', 'minimum'))), new MultipleContainer(BaseType::FLOAT, array(new QtiFloat(6.0)))),
	        array(self::getTestVariables('RESPONSE'), new MultipleContainer(BaseType::FLOAT)),
	        array(self::getTestVariables('RESPONSE', BaseType::IDENTIFIER), new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceA'), new QtiIdentifier('A')))), // Do not forget it only matches if Cardinality = SINGLE
	        array(self::getTestVariables('RESPONSE', BaseType::IDENTIFIER, 'W01'), new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceA'), new QtiIdentifier('A')))), // Weight only applies if baseType is ommited or FLOAT.
	        array(self::getTestVariables('RESPONSE', BaseType::IDENTIFIER, 'W01', 'S01'), new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceA')))), // Weight only applies if baseType is ommited or FLOAT.
	    );
	}
	
    protected static function getTestVariables($variableIdentifier, $baseType = -1, $weightIdentifier = '', $sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null) {
	    $testVariables = new TestVariables($variableIdentifier, $baseType, $weightIdentifier);
	    $testVariables->setSectionIdentifier($sectionIdentifier);
	    
	    if (empty($includeCategories) === false) {
	        $testVariables->setIncludeCategories($includeCategories);
	    }
	    
	    if (empty($excludeCategories) === false) {
	        $testVariables->setExcludeCategories($excludeCategories);
	    }

	    return $testVariables;
	}
}
