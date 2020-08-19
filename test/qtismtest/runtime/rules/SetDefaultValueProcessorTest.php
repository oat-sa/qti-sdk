<?php

namespace qtismtest\runtime\rules;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\RuleProcessingException;
use qtism\runtime\rules\SetDefaultValueProcessor;
use qtismtest\QtiSmTestCase;

class SetDefaultValueProcessorTest extends QtiSmTestCase
{
    public function testDefaultValueOnResponseSimple()
    {
        $rule = $this->createComponentFromXml('
			<setDefaultValue identifier="RESPONSE">
				<baseValue baseType="identifier">there</baseValue>
			</setDefaultValue>
		');

        $processor = new SetDefaultValueProcessor($rule);
        $response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
        $response->setDefaultValue(new QtiIdentifier('hello'));

        $state = new State([$response]);
        $processor->setState($state);
        $processor->process();

        $this->assertInstanceOf(QtiIdentifier::class, $state->getVariable('RESPONSE')->getDefaultValue());
        $this->assertEquals('there', $state->getVariable('RESPONSE')->getDefaultValue()->getValue());
    }

    public function testDefaultValueOnOutcomeSimple()
    {
        $rule = $this->createComponentFromXml('
			<setDefaultValue identifier="SCORE">
				<null/>
			</setDefaultValue>
		');

        $processor = new SetDefaultValueProcessor($rule);
        $response = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
        $response->setDefaultValue(new QtiFloat(0.0));

        $state = new State([$response]);
        $processor->setState($state);
        $processor->process();

        $this->assertSame(null, $state->getVariable('SCORE')->getDefaultValue());
    }

    public function testSetDefaultValueNoVariable()
    {
        $rule = $this->createComponentFromXml('
			<setDefaultValue identifier="RESPONSE">
				<null/>
			</setDefaultValue>
		');

        $processor = new SetDefaultValueProcessor($rule);
        $state = new State();
        $processor->setState($state);

        $this->setExpectedException(
            RuleProcessingException::class,
            "No variable with identifier 'RESPONSE' to be set in the current state.",
            RuleProcessingException::NONEXISTENT_VARIABLE
        );

        $processor->process();
    }

    public function testSetDefaultValueWrongBaseType()
    {
        $rule = $this->createComponentFromXml('
			<setDefaultValue identifier="RESPONSE">
				<baseValue baseType="boolean">true</baseValue>
			</setDefaultValue>
		');

        $processor = new SetDefaultValueProcessor($rule);
        $response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
        $state = new State([$response]);
        $processor->setState($state);

        $this->setExpectedException(
            RuleProcessingException::class
        );

        $processor->process();
    }
}
