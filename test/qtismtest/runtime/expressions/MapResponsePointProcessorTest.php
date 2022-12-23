<?php

namespace qtismtest\runtime\expressions;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\ExpressionProcessingException;
use qtism\runtime\expressions\MapResponsePointProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class MapResponsePointProcessorTest
 */
class MapResponsePointProcessorTest extends QtiSmTestCase
{
    public function testSingleCardinality(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="point" cardinality="single">
				<areaMapping defaultValue="666.666">
					<areaMapEntry shape="rect" coords="0,0,20,10" mappedValue="1"/>
					<areaMapEntry shape="poly" coords="0,8,7,4,2,2,8,-4,-2,1" mappedValue="2"/>
					<areaMapEntry shape="circle" coords="5,5,5" mappedValue="3"/>
				</areaMapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $variable->setValue(new QtiPoint(1, 1)); // in rect, poly

        $processor = new MapResponsePointProcessor($expr);
        $state = new State([$variable]);

        $processor->setState($state);

        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(3.0, $result->getValue());

        $state['response1'] = new QtiPoint(3, 3); // in rect, circle, poly
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(6, $result->getValue());

        $state['response1'] = new QtiPoint(19, 9); // in rect
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $state['response1'] = new QtiPoint(25, 25); // outside everything.
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(666.666, $result->getValue());
    }

    public function testMultipleCardinality(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="point" cardinality="multiple">
				<areaMapping defaultValue="666.666">
					<areaMapEntry shape="rect" coords="0,0,20,10" mappedValue="1"/>
					<areaMapEntry shape="poly" coords="0,8,7,4,2,2,8,-4,-2,1" mappedValue="2"/>
					<areaMapEntry shape="circle" coords="5,5,5" mappedValue="3"/>
				</areaMapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $points = new MultipleContainer(BaseType::POINT);
        $points[] = new QtiPoint(1, 1); // in rect, poly
        $points[] = new QtiPoint(3, 3); // in rect, circle, poly
        $variable->setValue($points);

        // because 1, 1 falls in 2 times in rect and poly, it is added to the total
        // just once only as per QTI 2.1 specification.
        // result = 1 + 2 + 3 = 6
        $processor = new MapResponsePointProcessor($expr);
        $state = new State([$variable]);
        $processor->setState($state);

        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(6, $result->getValue());

        // Nothing matches... defaultValue returned.
        $points = new MultipleContainer(BaseType::POINT);
        $points[] = new QtiPoint(-1, -1);
        $points[] = new QtiPoint(21, 20);
        $state['response1'] = $points;

        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(666.666, $result->getValue());
    }

    public function testNoVariable(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $processor = new MapResponsePointProcessor($expr);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNoVariableValue(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="point" cardinality="single">
				<areaMapping>
					<areaMapEntry shape="rect" coords="0 , 0 , 20 , 10" mappedValue="1"/>
				</areaMapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $processor->setState(new State([$variable]));
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0.0, $result->getValue());
    }

    public function testDefaultValue(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="point" cardinality="single">
				<areaMapping defaultValue="2">
					<areaMapEntry shape="rect" coords="0 , 0 , 20 , 10" mappedValue="1"/>
				</areaMapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $processor->setState(new State([$variable]));
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(2.0, $result->getValue());
    }

    public function testWrongBaseType(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="integer" cardinality="single">
				<areaMapping>
					<areaMapEntry shape="rect" coords="0 , 0 , 20 , 10" mappedValue="1"/>
				</areaMapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $processor->setState(new State([$variable]));

        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNoAreaMapping(): void
    {
        // When no areaMapping is found, we consider a default value of 0.0.
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="integer" cardinality="single"/>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $processor->setState(new State([$variable]));

        $result = $processor->process();
        $this::assertEquals(0.0, $result->getValue());
    }

    public function testWrongVariableType(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="response1" baseType="point" cardinality="single"/>
		');
        $variable = OutcomeVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $processor->setState(new State([$variable]));

        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testLowerBoundOverflow(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="point" cardinality="single">
				<areaMapping lowerBound="1">
					<areaMapEntry shape="rect" coords="0,0,20,10" mappedValue="-3"/>
					<areaMapEntry shape="circle" coords="5,5,5" mappedValue="1"/>
				</areaMapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $variable->setValue(new QtiPoint(3, 3)); // inside everything.
        $processor->setState(new State([$variable]));
        $result = $processor->process();

        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());
    }

    public function testUpperBoundOverflow(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="point" cardinality="single">
				<areaMapping lowerBound="1" upperBound="5">
					<areaMapEntry shape="rect" coords="0,0,20,10" mappedValue="4"/>
					<areaMapEntry shape="circle" coords="5,5,5" mappedValue="2"/>
				</areaMapping>
			</responseDeclaration>
		');
        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $variable->setValue(new QtiPoint(3, 3)); // inside everything.
        $processor->setState(new State([$variable]));
        $result = $processor->process();

        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(5, $result->getValue());
    }

    public function testWithRecord(): void
    {
        $expr = $this->createComponentFromXml('<mapResponsePoint identifier="response1"/>');
        $variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" cardinality="record">
	            <areaMapping lowerBound="1" upperBound="5">
					<areaMapEntry shape="rect" coords="0,0,20,10" mappedValue="4"/>
					<areaMapEntry shape="circle" coords="5,5,5" mappedValue="2"/>
				</areaMapping>
	        </responseDeclaration>
		');

        $variable = ResponseVariable::createFromDataModel($variableDeclaration);
        $processor = new MapResponsePointProcessor($expr);
        $processor->setState(new State([$variable]));

        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage('The MapResponsePoint expression cannot be applied to RECORD variables.');
        $this->expectExceptionCode(ExpressionProcessingException::WRONG_VARIABLE_CARDINALITY);
        $result = $processor->process();
    }
}
