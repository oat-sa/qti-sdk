<?php

namespace qtismtest\runtime\common;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\ResponseDeclaration;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtismtest\QtiSmTestCase;
use stdClass;

class StateTest extends QtiSmTestCase
{
    public function testInstantiation()
    {
        $state = new State();
        $this->assertInstanceOf('qtism\\runtime\\common\\State', $state);
        $this->assertEquals(0, count($state));

        $varsArray = [];
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

    public function testInstantiationInvalid()
    {
        $this->setExpectedException('\\InvalidArgumentException');
        $state = new State([15, 'string', new stdClass()]);
    }

    public function testAddressing()
    {
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

    public function testAddressingInvalidOne()
    {
        $this->setExpectedException('\\OutOfBoundsException');
        $state = new State();
        $state['var'] = new ResponseDeclaration('var', BaseType::POINT, Cardinality::ORDERED);
    }

    public function testAdressingInvalidTwo()
    {
        $this->setExpectedException('\\OutOfRangeException');
        $state = new State();
        $var = $state[3];
    }

    public function testGetAllVariables()
    {
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

    public function testUnsetVariableByString()
    {
        $state = new State(
            [
                new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)),
            ]
        );

        $this->assertCount(1, $state);
        $state->unsetVariable('RESPONSE');
        $this->assertCount(0, $state);
    }

    public function testUnsetVariableByVariableObject()
    {
        $variable = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true));
        $state = new State([$variable]);

        $this->assertCount(1, $state);
        $state->unsetVariable($variable);
        $this->assertCount(0, $state);
    }

    public function testUnsetUnexistingVariable()
    {
        $state = new State();

        $this->setExpectedException(
            '\\OutOfBoundsException',
            "No Variable object with identifier 'X' found in the current State object."
        );

        $state->unsetVariable('X');
    }

    public function testUnsetVariableWrongType()
    {
        $state = new State();

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The variable argument must be a Variable object or a string, '1' given"
        );

        $state->unsetVariable(true);
    }

    public function testOffsetSetWrongOffsetType()
    {
        $state = new State();

        $this->setExpectedException(
            '\\OutOfRangeException',
            "A State object can only be adressed by a valid string."
        );

        $state[true] = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true));
    }
}
