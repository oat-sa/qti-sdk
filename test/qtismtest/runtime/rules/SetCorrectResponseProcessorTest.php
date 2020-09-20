<?php

namespace qtismtest\runtime\rules;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\RuleProcessingException;
use qtism\runtime\rules\SetCorrectResponseProcessor;
use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiIdentifier;

/**
 * Class SetCorrectValueProcessorTest
 */
class SetCorrectResponseProcessorTest extends QtiSmTestCase
{
    public function testSetCorrectResponseSimple()
    {
        $rule = $this->createComponentFromXml('
			<setCorrectResponse identifier="RESPONSE">
				<baseValue baseType="identifier">ChoiceA</baseValue>
			</setCorrectResponse>
		');

        $processor = new SetCorrectResponseProcessor($rule);
        $response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
        $state = new State([$response]);
        $processor->setState($state);
        $processor->process();

        $this->assertInstanceOf(QtiIdentifier::class, $state->getVariable('RESPONSE')->getCorrectResponse());
        $this->assertEquals('ChoiceA', $state->getVariable('RESPONSE')->getCorrectResponse()->getValue());
    }

    public function testSetCorrectResponseNoVariable()
    {
        $rule = $this->createComponentFromXml('
			<setCorrectResponse identifier="RESPONSEXXXX">
				<baseValue baseType="identifier">ChoiceA</baseValue>
			</setCorrectResponse>
		');

        $processor = new SetCorrectResponseProcessor($rule);
        $response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
        $state = new State([$response]);
        $processor->setState($state);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage("No variable with identifier 'RESPONSEXXXX' to be set in the current state.");
        $this->expectExceptionCode(
            RuleProcessingException::NONEXISTENT_VARIABLE
        );

        $processor->process();
    }

    public function testSetCorrectResponseWrongBaseType()
    {
        $rule = $this->createComponentFromXml('
			<setCorrectResponse identifier="RESPONSE">
				<baseValue baseType="boolean">true</baseValue>
			</setCorrectResponse>
		');

        $processor = new SetCorrectResponseProcessor($rule);
        $response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
        $state = new State([$response]);
        $processor->setState($state);

        $this->expectException(RuleProcessingException::class);

        $processor->process();
    }

    public function testSetCorrectResponseWrongCardinality()
    {
        $rule = $this->createComponentFromXml('
			<setCorrectResponse identifier="RESPONSE">
				<baseValue baseType="identifier" >true</baseValue>
			</setCorrectResponse>
		');

        $processor = new SetCorrectResponseProcessor($rule);
        $response = new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER);
        $state = new State([$response]);
        $processor->setState($state);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage('Unable to set value true to variable \'RESPONSE\' (cardinality = multiple, baseType = identifier).');

        $processor->process();
    }
}
