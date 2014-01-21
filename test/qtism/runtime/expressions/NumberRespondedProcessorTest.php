<?php

use qtism\common\datatypes\Point;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Pair;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\common\collections\IdentifierCollection;
use qtism\data\expressions\NumberResponded;
use qtism\runtime\expressions\NumberRespondedProcessor;

require_once (dirname(__FILE__) . '/../../../QtiSmItemSubsetTestCase.php');

class NumberRespondedProcessorTest extends QtiSmItemSubsetTestCase {
	
	public function testNumberResponded() {
		$session = $this->getTestSession();
		
		$overallResponded = self::getNumberResponded();
		$includeMathResponded = self::getNumberResponded('', new IdentifierCollection(array('mathematics')));
		$processor = new NumberRespondedProcessor($overallResponded);
		$processor->setState($session);
		
		// Nothing responded yet.
		$this->assertEquals(0, $processor->process());
		$processor->setExpression($includeMathResponded);
		$this->assertEquals(0, $processor->process());
		
		// Q01
		$session->beginAttempt();
		$responses = new State();
		// Correct!
		$responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, 'ChoiceA'));
		$session->endAttempt($responses);
		$processor->setExpression($overallResponded);
	    $this->assertEquals(1, $processor->process());
	    $processor->setExpression($includeMathResponded);
	    $this->assertEquals(1, $processor->process());
	    $session->moveNext();
	    
	    // Q02
	    $responses->reset();
	    $session->beginAttempt();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'P'), new Pair('D', 'L')))));
	    $session->endAttempt($responses);
	    $this->assertEquals(3, $session['Q02.SCORE']);  // just for fun...
	    $processor->setExpression($overallResponded);
	    $this->assertEquals(2, $processor->process());
	    $processor->setExpression($includeMathResponded);
	    $this->assertEquals(1, $processor->process());
	    $session->moveNext();
	    
	    // Q03
	    $session->beginAttempt();
	    $session->skip();
	    $processor->setExpression($overallResponded);
	    $this->assertEquals(2, $processor->process());
	    $session->moveNext();
	    
	    // Q04
	    $responses->reset();
	    $session->beginAttempt();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('W', 'G1'), new DirectedPair('Su', 'G2')))));
	    $session->endAttempt($responses);
	    $this->assertEquals(3, $session['Q04.SCORE']);
	    $this->assertEquals(3, $processor->process());
	    $session->moveNext();
	    
	    // Q05
	    $session->beginAttempt();
	    $session->skip();
	    $this->assertEquals(3, $processor->process());
	    $session->moveNext();
	    
	    // Q06
	    $responses->reset();
	    $session->beginAttempt();
	    $responses->setVariable(new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, 'A'));
	    $session->endAttempt($responses);
	    $this->assertEquals(4, $processor->process());
	    $session->moveNext();
	    
	    // Q07.1
	    $responses->reset();
	    $session->beginAttempt();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(100, 100)));
	    $session->endAttempt($responses);
	    $this->assertEquals(1, $session['Q07.1.SCORE']);
	    $this->assertEquals(5, $processor->process());
	    $session->moveNext();
	    
	    // Q07.2
	    $responses->reset();
	    $session->beginAttempt();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(10, 10)));
	    $session->endAttempt($responses);
	    $this->assertEquals(0, $session['Q07.2.SCORE']);
	    $this->assertEquals(6, $processor->process());
	    $session->moveNext();
	    
	    // Q07.3
	    $responses->reset();
	    $session->beginAttempt();
	    $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)));
	    $session->endAttempt($responses);
	    $this->assertEquals(1, $session['Q07.3.SCORE']);
	    $this->assertEquals(7, $processor->process());
	    $session->moveNext();
	}
	
    protected static function getNumberResponded($sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null) {
	    $numberResponded = new NumberResponded();
	    $numberResponded->setSectionIdentifier($sectionIdentifier);
	    
	    if (empty($includeCategories) === false) {
	        $numberResponded->setIncludeCategories($includeCategories);
	    }
	    
	    if (empty($excludeCategories) === false) {
	        $numberResponded->setExcludeCategories($excludeCategories);
	    }

	    return $numberResponded;
	}
}