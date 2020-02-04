<?php

namespace qtismtest\runtime\rules;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiInteger;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\rules\TemplateConditionProcessor;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\common\State;

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
     * @depends testTemplateConditionSimpleIf2
     * @dataProvider templateConditionSimpleIfElseIfElseProvider
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
        
        $stateArray = array(
            new OutcomeVariable('CONTROL', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger($controlValue)),
            new TemplateVariable('TPL1', Cardinality::SINGLE, BaseType::INTEGER)
        );
        $state = new State($stateArray);
        $processor = new TemplateConditionProcessor($rule);
        $processor->setState($state);
        $processor->process();
        
        $this->assertEquals($expectedTpl1Value, $state['TPL1']->getValue());
    }
    
    public function templateConditionSimpleIfElseIfElseProvider()
    {
        return array(
            array(1, 1),
            array(2, 2),
            array(3, 3),
            array(4, 3),
            array(-1, 3)
        );
    }
}
