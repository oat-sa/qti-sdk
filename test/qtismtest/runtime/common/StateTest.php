<?php

namespace qtismtest\runtime\common;

use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\ResponseDeclaration;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtismtest\QtiSmTestCase;
use stdClass;
use qtism\runtime\common\VariableCollection;

/**
 * Class StateTest
 */
class StateTest extends QtiSmTestCase
{
    public function testInstantiation()
    {
        $state = new State();
        $this->assertInstanceOf(State::class, $state);
        $this->assertEquals(0, count($state));

        $varsArray = [];
        $varsArray[] = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER);
        $varsArray[] = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);

        $state = new State($varsArray);
        $this->assertEquals(2, count($state));
        $this->assertInstanceOf(ResponseVariable::class, $state->getVariable('RESPONSE'));
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
        $this->expectException(InvalidArgumentException::class);
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
        $this->expectException(OutOfBoundsException::class);
        $state = new State();
        $state['var'] = new ResponseDeclaration('var', BaseType::POINT, Cardinality::ORDERED);
    }

    public function testAdressingInvalidTwo()
    {
        $this->expectException(OutOfRangeException::class);
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

        $this->assertInstanceOf(VariableCollection::class, $state->getAllVariables());
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

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("No Variable object with identifier 'X' found in the current State object.");

        $state->unsetVariable('X');
    }

    public function testUnsetVariableWrongType()
    {
        $state = new State();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The variable argument must be a Variable object or a string, '1' given");

        $state->unsetVariable(true);
    }

    public function testOffsetSetWrongOffsetType()
    {
        $state = new State();

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('A State object can only be addressed by a valid string.');

        $state[true] = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true));
    }
}
