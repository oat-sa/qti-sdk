<?php
namespace qtismtest\runtime\common;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\data\state\ResponseDeclaration;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\VariableCollection;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

class StateTest extends QtiSmTestCase {

	public function testInstantiation() {
		$state = new State();
		$this->assertInstanceOf('qtism\\runtime\\common\\State', $state);
		$this->assertEquals(0, count($state));
		
		$varsArray = array();
		$varsArray[] = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER);
		$varsArray[] = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
		
		$state = new State($varsArray);
		$this->assertEquals(2, count($state));
		$this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $state->getVariable('RESPONSE'));
		$this->assertEquals($state->getVariable('RESPONSE')->getBaseType(), BaseType::INTEGER);
		
		// replace a variable.
		$var = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FLOAT);
		$state->setVariable($var);
		$this->assertEquals($state->getVariable('RESPONSE')->getBaseType(), BaseType::FLOAT);
		
		// unset a variable.
		unset($state['RESPONSE']);
		$isset = isset($state['RESPONSE']);
		$this->assertFalse($isset);
		$this->assertTrue($state['RESPONSE'] === null);
	}
	
	public function testInstantiationInvalid() {
		$this->setExpectedException('\\InvalidArgumentException');
		$state = new State(array(15, 'string', new \stdClass()));
	}
	
	public function testAddressing() {
		$state = new State();
		$response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER);
		$score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
		
		$state->setVariable($response);
		$state->setVariable($score);
		
		$this->assertTrue($state['foo'] === null);
		$this->assertTrue($response === $state->getVariable('RESPONSE'));
		$this->assertTrue($score === $state->getVariable('SCORE'));
		$this->assertTrue(isset($state['SCORE']));
		$this->assertFalse(isset($state['SCOREX']));
	}
	
	public function testAddressingInvalidOne() {
		$this->setExpectedException('\\OutOfBoundsException');
		$state = new State();
		$state['var'] = new ResponseDeclaration('var', BaseType::POINT, Cardinality::ORDERED);
	}
	
	public function testAdressingInvalidTwo() {
		$this->setExpectedException('\\OutOfRangeException');
		$state = new State();
		$var = $state[3];
	}
	
	public function testGetAllVariables() {
	    $state = new State();
	    $this->assertEquals(0, count($state->getAllVariables()));
	    
	    $state->setVariable(new ResponseVariable('RESPONSE1', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25)));
	    $this->assertEquals(1, count($state->getAllVariables()));
	    
	    $state->setVariable(new OutcomeVariable('SCORE1', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)));
	    $this->assertEquals(2, count($state->getAllVariables()));
	    
	    unset($state['RESPONSE1']);
	    $this->assertEquals(1, count($state->getAllVariables()));
	    
	    $this->assertInstanceOf('qtism\\runtime\\common\\VariableCollection', $state->getAllVariables());
	}
    
    /**
     * @dataProvider containsNullOnlyProvider
     * 
     * @param boolean $expected
     * @param \qtism\runtime\common\State $state
     */
    public function testContainsNullOnly($expected, State $state) {
        $this->assertEquals($expected, $state->containsNullOnly());
    }
    
    public function containsNullOnlyProvider() {
        return array(
            array(true, new State()),
            array(true, new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER)))),
            array(true, new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString(''))))),
            array(true, new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString('')), new ResponseVariable('RESPONSE3', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER))))),
            
            array(false, new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0))))),
            array(false, new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0)), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25))))),
            array(false, new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25))))),
            
            array(false, new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0)), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::INTEGER)))),
        );
    }
    
    /**
     * @dataProvider containsValuesEqualToVariableDefaultOnlyProvider
     * 
     * @param boolean $expected
     * @param \qtism\runtime\common\State $state
     */
    public function testContainsValuesEqualToVariableDefaultOnly($expected, State $state) {
        $this->assertEquals($expected, $state->containsValuesEqualToVariableDefaultOnly());
    }
    
    public function containsValuesEqualToVariableDefaultOnlyProvider() {
        $booleanNotDefault = new ResponseVariable('BOOLEAN_NOT_DEFAULT', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true));
        $booleanNotDefault->setDefaultValue(new QtiBoolean(false));
        
        $booleanDefault = new ResponseVariable('BOOLEAN_DEFAULT', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true));
        $booleanDefault->setDefaultValue(new QtiBoolean(true));
        
        $nullDefault = new ResponseVariable('NULL_DEFAULT', Cardinality::SINGLE, BaseType::BOOLEAN);
        
        $stringDefaultEmptyString = new ResponseVariable('STRING_DEFAULT_EMPTY_STRING', Cardinality::SINGLE, BaseType::STRING);
        $stringDefaultEmptyString->setDefaultValue(new QtiString(''));
        
        $stringDefaultEmptyString2 = new ResponseVariable('STRING_DEFAULT_EMPTY_STRING2', Cardinality::SINGLE, BaseType::STRING, new QtiString(''));
        $stringDefaultEmptyString2->setDefaultValue(null);
        
        $containerDefaultEmptyContainer = new ResponseVariable('CONTAINER_DEFAULT_EMPTY_CONTAINER', Cardinality::MULTIPLE, BaseType::BOOLEAN);
        $containerDefaultEmptyContainer->setDefaultValue(new MultipleContainer(BaseType::BOOLEAN));
        
        $containerNotDefault = new ResponseVariable('CONTAINER_NOT_DEFAULT', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(false))));
        $containerNotDefault->setDefaultValue(new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(true))));
        
        $containerDefault = new ResponseVariable('CONTAINER_DEFAULT', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(true))));
        $containerDefault->setDefaultValue(new MultipleContainer(BaseType::BOOLEAN, array(new QtiBoolean(true))));
        
        return array(
            array(false, new State(array($booleanNotDefault))),
            array(false, new State(array($booleanDefault, $booleanNotDefault))),
            array(false, new State(array($booleanDefault, $booleanNotDefault, $nullDefault))),
            array(false, new State(array($booleanNotDefault, $stringDefaultEmptyString))),
            array(false, new State(array($containerNotDefault))),
            
            array(true, new State(array($booleanDefault))),
            array(true, new State(array($nullDefault))),
            array(true, new State(array($nullDefault, $booleanDefault))),
            array(true, new State(array($stringDefaultEmptyString))),
            array(true, new State(array($stringDefaultEmptyString2))),
            array(true, new State(array($stringDefaultEmptyString, $stringDefaultEmptyString2))),
            array(true, new State(array($containerDefaultEmptyContainer))),
            array(true, new State(array($containerDefault)))
        );
    }
}
