<?php

namespace qtismtest\runtime\rules;

use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiString;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\LookupOutcomeValueProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\rules\RuleProcessingException;

/**
 * Class LookupOutcomeValueProcessorTest
 */
class LookupOutcomeValueProcessorTest extends QtiSmTestCase
{
    public function testLookupOutcomeValueSimpleMatchTable()
    {
        $rule = $this->createComponentFromXml('
			<lookupOutcomeValue identifier="outcome1">
				<baseValue baseType="integer">2</baseValue>
			</lookupOutcomeValue>
		');

        $declaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="outcome1" cardinality="single" baseType="pair">
				<matchTable defaultValue="Y Z">
					<matchTableEntry sourceValue="1" targetValue="A B"/>
					<matchTableEntry sourceValue="2" targetValue="C D"/>
					<matchTableEntry sourceValue="3" targetValue="E F"/>
				</matchTable>
			</outcomeDeclaration>
		');

        $outcome = OutcomeVariable::createFromDataModel($declaration);

        $processor = new LookupOutcomeValueProcessor($rule);
        $state = new State([$outcome]);
        $processor->setState($state);

        $this::assertNull($state['outcome1']);
        $processor->process();
        $this::assertInstanceOf(QtiPair::class, $state['outcome1']);
        $this::assertTrue($state['outcome1']->equals(new QtiPair('C', 'D')));

        // Try to get the default value.
        $expr = $rule->getExpression();
        $expr->setValue(5);
        $processor->process();
        $this::assertInstanceOf(QtiPair::class, $state['outcome1']);
        $this::assertTrue($state['outcome1']->equals(new QtiPair('Y', 'Z')));
    }

    public function testLookupOutcomeValueSimpleInterpolationTable()
    {
        $rule = $this->createComponentFromXml('
			<lookupOutcomeValue identifier="outcome1">
				<baseValue baseType="float">2.0</baseValue>
			</lookupOutcomeValue>
		');

        $declaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="outcome1" cardinality="single" baseType="string">
				<interpolationTable defaultValue="What\'s going on?">
					<interpolationTableEntry sourceValue="1.0" includeBoundary="true" targetValue="Come get some!"/>
					<interpolationTableEntry sourceValue="2.0" includeBoundary="false" targetValue="Piece of cake!"/>
					<interpolationTableEntry sourceValue="3.0" includeBoundary="true" targetValue="Awesome!"/>
				</interpolationTable>
			</outcomeDeclaration>
		');

        $outcome = OutcomeVariable::createFromDataModel($declaration);
        $state = new State([$outcome]);
        $processor = new LookupOutcomeValueProcessor($rule);
        $processor->setState($state);

        $this::assertNull($state['outcome1']);
        $processor->process();
        $this::assertInstanceOf(QtiString::class, $state['outcome1']);
        $this::assertEquals('Awesome!', $state['outcome1']->getValue());

        // include the boundary for interpolationTableEntry[1]
        $table = $outcome->getLookupTable();
        $entries = $table->getInterpolationTableEntries();
        $entries[1]->setIncludeBoundary(true);

        $processor->process();
        $this::assertInstanceOf(QtiString::class, $state['outcome1']);
        $this::assertEquals('Piece of cake!', $state['outcome1']->getValue());

        // get the default value.
        $expr = $rule->getExpression();
        $expr->setValue(4.0);
        $processor->process();
        $this::assertInstanceOf(QtiString::class, $state['outcome1']);
        $this::assertEquals("What's going on?", $state['outcome1']->getValue());
    }

    public function testLookupOutcomeValueNoVariable()
    {
        $rule = $this->createComponentFromXml('
			<lookupOutcomeValue identifier="outcome1">
				<baseValue baseType="integer">2</baseValue>
			</lookupOutcomeValue>
		');

        $processor = new LookupOutcomeValueProcessor($rule);
        $state = new State();
        $processor->setState($state);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage("The variable to set 'outcome1' does not exist in the current state.");

        $processor->process();
    }

    public function testLookupOutcomeValueNoLookupTable()
    {
        $rule = $this->createComponentFromXml('
			<lookupOutcomeValue identifier="outcome1">
				<baseValue baseType="float">2.0</baseValue>
			</lookupOutcomeValue>
		');

        $declaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="outcome1" cardinality="single" baseType="string"/>
		');

        $outcome = OutcomeVariable::createFromDataModel($declaration);
        $state = new State([$outcome]);
        $processor = new LookupOutcomeValueProcessor($rule);
        $processor->setState($state);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage("No lookupTable in declaration of variable 'outcome1'.");

        $processor->process();
    }

    public function testLookupOutcomeWrongLookupValueTypeForInterpolationTable()
    {
        $rule = $this->createComponentFromXml('
			<lookupOutcomeValue identifier="outcome1">
				<baseValue baseType="boolean">true</baseValue>
			</lookupOutcomeValue>
		');

        $declaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="outcome1" cardinality="single" baseType="string">
			    <interpolationTable defaultValue="What\'s going on?">
					<interpolationTableEntry sourceValue="1.0" includeBoundary="true" targetValue="Come get some!"/>
					<interpolationTableEntry sourceValue="2.0" includeBoundary="false" targetValue="Piece of cake!"/>
					<interpolationTableEntry sourceValue="3.0" includeBoundary="true" targetValue="Awesome!"/>
				</interpolationTable>
			</outcomeDeclaration>
		');

        $outcome = OutcomeVariable::createFromDataModel($declaration);
        $state = new State([$outcome]);
        $processor = new LookupOutcomeValueProcessor($rule);
        $processor->setState($state);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage("The value of variable 'outcome1' must be integer, float or duration when used with an interpolationTable");

        $processor->process();
    }

    public function testLookupOutcomeWrongLookupValueTypeForMatchTable()
    {
        $rule = $this->createComponentFromXml('
			<lookupOutcomeValue identifier="outcome1">
				<baseValue baseType="boolean">true</baseValue>
			</lookupOutcomeValue>
		');

        $declaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="outcome1" cardinality="single" baseType="string">
			    <matchTable defaultValue="What\'s going on?">
					<matchTableEntry sourceValue="1" targetValue="Come get some!"/>
					<matchTableEntry sourceValue="2" targetValue="Piece of cake!"/>
					<matchTableEntry sourceValue="3" targetValue="Awesome!"/>
				</matchTable>
			</outcomeDeclaration>
		');

        $outcome = OutcomeVariable::createFromDataModel($declaration);
        $state = new State([$outcome]);
        $processor = new LookupOutcomeValueProcessor($rule);
        $processor->setState($state);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage("The value of the variable 'outcome1' must be integer when used with a matchTable.");

        $processor->process();
    }
}
