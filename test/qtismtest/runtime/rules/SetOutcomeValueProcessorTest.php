<?php

namespace qtismtest\runtime\rules;

use qtismtest\QtiSmTestCase;
use qtism\runtime\rules\RuleProcessingException;
use qtism\common\datatypes\QtiBoolean;
use qtism\runtime\common\State;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\rules\SetOutcomeValueProcessor;

class SetOutcomeValueProcessorTest extends QtiSmTestCase
{
    
    public function testSetOutcomeValueSimple()
    {
        $rule = $this->createComponentFromXml('
			<setOutcomeValue identifier="SCORE">
				<baseValue baseType="float">4.3</baseValue>
			</setOutcomeValue>
		');
        
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
        $state = new State(array($score));
        $processor->setState($state);
        $processor->process();
        
        // The state must be modified.
        // OutcomeVariable with identifier 'SCORE' must contain 4.3.
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $state['SCORE']);
        $this->assertEquals(4.3, $state['SCORE']->getValue());
    }
    
    public function testSetOutcomeValueJugglingFromIntToFloat()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCORE">
	            <baseValue baseType="integer">4</baseValue>
	        </setOutcomeValue>
	    ');
        
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
        $state = new State(array($score));
        $processor->setState($state);
        $processor->process();
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $state['SCORE']);
        $this->assertEquals(4.0, $state['SCORE']->getValue());
    }
    
    public function testSetOtucomeValueJugglingFromFloatToInt()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCORE">
	            <baseValue baseType="float">4.3</baseValue>
	        </setOutcomeValue>
	    ');
         
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::INTEGER);
        $state = new State(array($score));
        $processor->setState($state);
        $processor->process();
         
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $state['SCORE']);
        $this->assertEquals(4, $state['SCORE']->getValue());
    }
    
    public function testSetOutcomeValueWrongJugglingScalar()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCORE">
	            <baseValue baseType="string">String!</baseValue>
	        </setOutcomeValue>
	    ');
        
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::INTEGER);
        $state = new State(array($score));
        $processor->setState($state);
        
        $this->setExpectedException('qtism\\runtime\\rules\\RuleProcessingException');
        $processor->process();
    }
    
    public function testSetOutcomeValueWrongJugglingMultipleOne()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCORE">
	            <baseValue baseType="integer">1337</baseValue>
	        </setOutcomeValue>
	    ');
         
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::MULTIPLE, BaseType::INTEGER);
        $state = new State(array($score));
        $processor->setState($state);
         
        $this->setExpectedException('qtism\\runtime\\rules\\RuleProcessingException');
        $processor->process();
    }
    
    public function testSetOutcomeValueJugglingMultiple()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCORE">
	            <multiple>
	                <baseValue baseType="float">1337.1337</baseValue>
                    <baseValue baseType="float">7777.7777</baseValue>
	            </multiple>
	        </setOutcomeValue>
	    ');
    
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::INTEGER);
        $state = new State(array($score));
        $processor->setState($state);
        
        // In this case, juggling will put the first entry of the multiple container
        // in the target single cardinality variable. The float value is then changed into an integer value.
        $processor->process();
        $this->assertEquals(1337, $state['SCORE']->getValue());
    }
    
    public function testSetOutcomeValueJugglingOrdered()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCORE">
	            <ordered>
	                <baseValue baseType="float">1337.1337</baseValue>
                    <baseValue baseType="float">7777.7777</baseValue>
	            </ordered>
	        </setOutcomeValue>
	    ');
    
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::INTEGER);
        $state = new State(array($score));
        $processor->setState($state);
        
        // In this case, juggling will put the first entry of the multiple container
        // in the target single cardinality variable. The float value is then changed into an integer value.
        $processor->process();
        $this->assertEquals(1337, $state['SCORE']->getValue());
    }
    
    public function testSetOutcomeValueWrongJugglingMultipleBecauseWrongBaseType()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCORE">
	            <multiple>
	                <baseValue baseType="string">hello</baseValue>
                    <baseValue baseType="string">world</baseValue>
	            </multiple>
	        </setOutcomeValue>
	    ');
    
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::INTEGER);
        $state = new State(array($score));
        $processor->setState($state);
        
        $this->setExpectedException(
            'qtism\\runtime\\rules\\RuleProcessingException',
            'Unable to set value hello to variable \'SCORE\' (cardinality = single, baseType = integer).'
        );
        $processor->process();
    }
    
    public function testSetOutcomeValueModerate()
    {
        $rule = $this->createComponentFromXml('
			<setOutcomeValue identifier="myBool">
				<member>
					<baseValue baseType="string">Incredible!</baseValue>
					<multiple>
						<baseValue baseType="string">This...</baseValue>
						<baseValue baseType="string">Is...</baseValue>
						<baseValue baseType="string">Incredible!</baseValue>
					</multiple>
				</member>
			</setOutcomeValue>
		');
        
        $processor = new SetOutcomeValueProcessor($rule);
        $myBool = new OutcomeVariable('myBool', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false));
        $state = new State(array($myBool));
        $this->assertFalse($state['myBool']->getValue());
        
        $processor->setState($state);
        $processor->process();
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiBoolean', $state['myBool']);
        $this->assertTrue($state['myBool']->getValue());
    }
    
    public function testSetOutcomeValueNoVariable()
    {
        $rule = $this->createComponentFromXml('
	        <setOutcomeValue identifier="SCOREXXXX">
	            <baseValue baseType="float">1337.1337</baseValue>
	        </setOutcomeValue>
	    ');
        
        $processor = new SetOutcomeValueProcessor($rule);
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::INTEGER);
        $state = new State(array($score));
        $processor->setState($state);
        
        $this->setExpectedException(
            'qtism\\runtime\\rules\\RuleProcessingException',
            "No variable with identifier 'SCOREXXXX' to be set in the current state.",
            RuleProcessingException::NONEXISTENT_VARIABLE
        );
        
        $processor->process();
    }
}
