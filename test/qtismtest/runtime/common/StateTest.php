<?php

namespace qtismtest\runtime\common;

use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
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
use qtism\runtime\common\VariableCollection;

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

    /**
     * @dataProvider containsNullOnlyProvider
     *
     * @param boolean $expected
     * @param State $state
     */
    public function testContainsNullOnly($expected, State $state)
    {
        $this->assertEquals($expected, $state->containsNullOnly());
    }

    public function containsNullOnlyProvider()
    {
        return [
            [true, new State()],
            [true, new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER)])],
            [true, new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString(''))])],
            [
                true,
                new State([
                    new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER),
                    new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString('')),
                    new ResponseVariable('RESPONSE3', Cardinality::MULTIPLE, BaseType::INTEGER, new MultipleContainer(BaseType::INTEGER)),
                ]),
            ],

            [false, new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0))])],
            [false, new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0)), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25))])],
            [false, new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25))])],

            [false, new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(0)), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::INTEGER)])],
        ];
    }

    /**
     * @dataProvider containsValuesEqualToVariableDefaultOnlyProvider
     *
     * @param boolean $expected
     * @param State $state
     */
    public function testContainsValuesEqualToVariableDefaultOnly($expected, State $state)
    {
        $this->assertEquals($expected, $state->containsValuesEqualToVariableDefaultOnly());
    }

    public function containsValuesEqualToVariableDefaultOnlyProvider()
    {
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

        $containerNotDefault = new ResponseVariable('CONTAINER_NOT_DEFAULT', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(false)]));
        $containerNotDefault->setDefaultValue(new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true)]));

        $containerDefault = new ResponseVariable('CONTAINER_DEFAULT', Cardinality::MULTIPLE, BaseType::BOOLEAN, new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true)]));
        $containerDefault->setDefaultValue(new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true)]));

        return [
            [false, new State([$booleanNotDefault])],
            [false, new State([$booleanDefault, $booleanNotDefault])],
            [false, new State([$booleanDefault, $booleanNotDefault, $nullDefault])],
            [false, new State([$booleanNotDefault, $stringDefaultEmptyString])],
            [false, new State([$containerNotDefault])],

            [true, new State([$booleanDefault])],
            [true, new State([$nullDefault])],
            [true, new State([$nullDefault, $booleanDefault])],
            [true, new State([$stringDefaultEmptyString])],
            [true, new State([$stringDefaultEmptyString2])],
            [true, new State([$stringDefaultEmptyString, $stringDefaultEmptyString2])],
            [true, new State([$containerDefaultEmptyContainer])],
            [true, new State([$containerDefault])],
        ];
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
        $this->expectExceptionMessage("A State object can only be adressed by a valid string.");

        $state[true] = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true));
    }
}
