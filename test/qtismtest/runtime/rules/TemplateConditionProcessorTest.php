<?php

namespace qtismtest\runtime\rules;

use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\rules\TemplateConditionProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateConditionProcessorTest
 *
 * @package qtismtest\runtime\rules
 */
class TemplateConditionProcessorTest extends QtiSmTestCase
{
    public function testTemplateConditionSimpleIf1()
    {
        $rule = $this->createComponentFromXml('
	        <templateCondition>
    	        <templateIf>
    	            <baseValue baseType="boolean">true</baseValue>
    	            <setTemplateValue identifier="TPL1">
    	                <baseValue baseType="integer">1</baseValue>
    	            </setTemplateValue>
    	        </templateIf>
	        </templateCondition>
	    ');

        $state = new State();
        $state->setVariable(new TemplateVariable('TPL1', Cardinality::SINGLE, BaseType::INTEGER));
        $processor = new TemplateConditionProcessor($rule);
        $processor->setState($state);
        $processor->process();

        $this->assertEquals(1, $state['TPL1']->getValue());
    }

    /**
     * @depends testTemplateConditionSimpleIf1
     */
    public function testTemplateConditionSimpleIf2()
    {
        $rule = $this->createComponentFromXml('
	        <templateCondition>
    	        <templateIf>
    	            <baseValue baseType="boolean">true</baseValue>
    	            <setTemplateValue identifier="TPL1">
    	                <baseValue baseType="integer">1</baseValue>
    	            </setTemplateValue>
	                <setDefaultValue identifier="RSP1">
    	                <baseValue baseType="identifier">Choice1</baseValue>
    	            </setDefaultValue>
	                <setCorrectResponse identifier="RSP1">
	                    <baseValue baseType="identifier">Choice2</baseValue>
	                </setCorrectResponse>
    	        </templateIf>
	        </templateCondition>
	    ');

        $state = new State();

        $state->setVariable(new TemplateVariable('TPL1', Cardinality::SINGLE, BaseType::INTEGER));
        $state->setVariable(new ResponseVariable('RSP1', Cardinality::SINGLE, BaseType::IDENTIFIER));
        $processor = new TemplateConditionProcessor($rule);
        $processor->setState($state);
        $processor->process();

        $this->assertEquals(1, $state['TPL1']->getValue());
        $this->assertEquals('Choice1', $state->getVariable('RSP1')->getDefaultValue()->getValue());
        $this->assertEquals('Choice2', $state->getVariable('RSP1')->getCorrectResponse()->getValue());
    }

    /**
     * @depends      testTemplateConditionSimpleIf2
     * @dataProvider templateConditionSimpleIfElseIfElseProvider
     * @param int $controlValue
     * @param int $expectedTpl1Value
     * @throws MarshallerNotFoundException
     */
    public function testTemplateConditionSimpleIfElseIfElse($controlValue, $expectedTpl1Value)
    {
        $rule = $this->createComponentFromXml('
	        <templateCondition>
    	        <templateIf>
	                <match>
    	                <variable identifier="CONTROL"/>
	                    <baseValue baseType="integer">1</baseValue>
	                </match>
    	            <setTemplateValue identifier="TPL1">
    	                <baseValue baseType="integer">1</baseValue>
    	            </setTemplateValue>
    	        </templateIf>
	            <templateElseIf>
	                <match>
                        <variable identifier="CONTROL"/>
	                    <baseValue baseType="integer">2</baseValue>
	                </match>
	                <setTemplateValue identifier="TPL1">
	                    <baseValue baseType="integer">2</baseValue>
	                </setTemplateValue>
	            </templateElseIf>
	            <templateElse>
	                <setTemplateValue identifier="TPL1">
	                    <baseValue baseType="integer">3</baseValue>
	                </setTemplateValue>
	            </templateElse>
	        </templateCondition>
	    ');

        $stateArray = [
            new OutcomeVariable('CONTROL', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger($controlValue)),
            new TemplateVariable('TPL1', Cardinality::SINGLE, BaseType::INTEGER),
        ];
        $state = new State($stateArray);
        $processor = new TemplateConditionProcessor($rule);
        $processor->setState($state);
        $processor->process();

        $this->assertEquals($expectedTpl1Value, $state['TPL1']->getValue());
    }

    /**
     * @return array
     */
    public function templateConditionSimpleIfElseIfElseProvider()
    {
        return [
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 3],
            [-1, 3],
        ];
    }
}
