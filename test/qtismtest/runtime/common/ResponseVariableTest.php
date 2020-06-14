<?php

namespace qtismtest\runtime\common;

use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\ResponseVariable;
use qtismtest\QtiSmTestCase;

class ResponseVariableTest extends QtiSmTestCase
{
    public function testCreateFromVariableDeclarationExtended()
    {
        $factory = $this->getMarshallerFactory('2.1.0');
        $element = $this->createDOMElement('
			<responseDeclaration xmlns="http://www.imsglobal.org/xsd/imsqti_v2p0" 
								identifier="outcome1" 
								baseType="pair" 
								cardinality="ordered">
				<defaultValue>
					<value>A B</value>
					<value>C D</value>
					<value>E F</value>
				</defaultValue>
				<correctResponse interpretation="Up to you :)!">
					<value>A B</value>
					<value>E F</value>
				</correctResponse>
				<mapping>
					<mapEntry mapKey="A B" mappedValue="1.0" caseSensitive="true"/>
					<mapEntry mapKey="C D" mappedValue="2.0" caseSensitive="true"/>
					<mapEntry mapKey="E F" mappedValue="3.0" caseSensitive="true"/>
				</mapping>
				<areaMapping>
					<areaMapEntry shape="rect" coords="10, 20, 40, 20" mappedValue="1.0"/>
					<areaMapEntry shape="rect" coords="20, 30, 50, 30" mappedValue="2.0"/>
					<areaMapEntry shape="rect" coords="30, 40, 60, 40" mappedValue="3.0"/>
				</areaMapping>
			</responseDeclaration>
		');
        $responseDeclaration = $factory->createMarshaller($element)->unmarshall($element);
        $responseVariable = ResponseVariable::createFromDataModel($responseDeclaration);
        $this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $responseVariable);

        $this->assertEquals('outcome1', $responseVariable->getIdentifier());
        $this->assertEquals(BaseType::PAIR, $responseVariable->getBaseType());
        $this->assertEquals(Cardinality::ORDERED, $responseVariable->getCardinality());

        // Value should be NULL to begin.
        $this->assertTrue($responseVariable->isNull());

        $defaultValue = $responseVariable->getDefaultValue();
        $this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $defaultValue);
        $this->assertEquals(3, count($defaultValue));

        $mapping = $responseVariable->getMapping();
        $this->assertInstanceOf('qtism\\data\\state\\Mapping', $mapping);
        $mapEntries = $mapping->getMapEntries();
        $this->assertEquals(3, count($mapEntries));
        $this->assertInstanceOf(QtiPair::class, $mapEntries[0]->getMapKey());

        $areaMapping = $responseVariable->getAreaMapping();
        $this->assertInstanceOf('qtism\\data\\state\\AreaMapping', $areaMapping);
        $areaMapEntries = $areaMapping->getAreaMapEntries();
        $this->assertEquals(3, count($areaMapEntries));
        $this->assertInstanceOf(QtiCoords::class, $areaMapEntries[0]->getCoords());

        $this->assertTrue($responseVariable->hasCorrectResponse());
        $correctResponse = $responseVariable->getCorrectResponse();
        $this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $correctResponse);
        $this->assertEquals(2, count($correctResponse));
        $this->assertTrue($correctResponse[0]->equals(new QtiPair('A', 'B')));
        $this->assertTrue($correctResponse[1]->equals(new QtiPair('E', 'F')));

        $responseVariable->setValue(new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('E', 'F')]));
        $this->assertTrue($responseVariable->isCorrect());
        $responseVariable->setValue(new OrderedContainer(BaseType::PAIR, [new QtiPair('E', 'F'), new QtiPair('A', 'B')]));
        $this->assertFalse($responseVariable->isCorrect());

        // If I reinitialize the value, we must find a NULL container into this variable.
        $responseVariable->initialize();
        $this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $responseVariable->getValue());
        $this->assertTrue($responseVariable->isNull());

        // If I apply the default value...
        $responseVariable->applyDefaultValue();
        $this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $responseVariable->getValue());
        $this->assertEquals(3, count($responseVariable->getValue()));
        $this->assertTrue($responseVariable->getValue()->equals(new OrderedContainer(BaseType::PAIR, [new QtiPair('A', 'B'), new QtiPair('C', 'D'), new QtiPair('E', 'F')])));
    }

    public function testIsCorrectWithNullCorrectResponse()
    {
        $responseVariable = new ResponseVariable('MYVAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25));
        $this->assertFalse($responseVariable->isCorrect());
    }

    public function testGetDataModelValuesSingleCardinality()
    {
        // -- Test some Scalar datatypes
        // QtiInteger
        $responseVariable = new ResponseVariable('MYVAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(10));
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(1, $values);
        $this->assertSame(10, $values[0]->getValue());

        // QtiFloat
        $responseVariable = new ResponseVariable('MYVAR', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(10.1));
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(1, $values);
        $this->assertSame(10.1, $values[0]->getValue());

        // -- Tet some Non Scalar datatypes
        // QtiPair
        $qtiPair = new QtiPair('identifier1', 'identifier2');
        $responseVariable = new ResponseVariable('MYVAR', Cardinality::SINGLE, BaseType::PAIR, $qtiPair);
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(1, $values);
        $this->assertTrue($values[0]->getValue()->equals($qtiPair));

        // QtiPoint
        $qtiPoint = new QtiPoint(0, 0);
        $responseVariable = new ResponseVariable('MYVAR', Cardinality::SINGLE, BaseType::POINT, $qtiPoint);
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(1, $values);
        $this->assertTrue($values[0]->getValue()->equals($qtiPoint));
    }

    public function testGetDataModelValuesMultipleCardinality()
    {
        // -- Test some Scalar datatypes.
        // QtiString
        $responseVariable = new ResponseVariable(
            'MYVAR',
            Cardinality::MULTIPLE,
            BaseType::STRING,
            new MultipleContainer(BaseType::STRING, [new QtiString('Quarantine Casanova'), new QtiString('Needy Girl')])
        );
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(2, $values);
        $this->assertSame('Quarantine Casanova', $values[0]->getValue());
        $this->assertSame('Needy Girl', $values[1]->getValue());

        // QtiUri
        $responseVariable = new ResponseVariable(
            'MYVAR',
            Cardinality::MULTIPLE,
            BaseType::URI,
            new MultipleContainer(BaseType::URI, [new QtiUri('https://talk.to-me-come.on'), new QtiUri('https://old.45.s')])
        );
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(2, $values);
        $this->assertEquals('https://talk.to-me-come.on', $values[0]->getValue());
        $this->assertEquals('https://old.45.s', $values[1]->getValue());

        // -- Test some Non Scalar datatypes.
        // QtiPair
        $qtiPair1 = new QtiPair('id1', 'id2');
        $qtiPair2 = new QtiPair('id3', 'id4');
        $responseVariable = new ResponseVariable(
            'MYVAR',
            Cardinality::MULTIPLE,
            BaseType::PAIR,
            new MultipleContainer(BaseType::PAIR, [$qtiPair1, $qtiPair2])
        );
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(2, $values);
        $this->assertTrue($values[0]->getValue()->equals($qtiPair1));
        $this->assertTrue($values[1]->getValue()->equals($qtiPair2));

        // QtiPoint
        $qtiPoint1 = new QtiPoint(0, 0);
        $qtiPoint2 = new QtiPoint(1, 1);
        $responseVariable = new ResponseVariable(
            'MYVAR',
            Cardinality::MULTIPLE,
            BaseType::POINT,
            new MultipleContainer(BaseType::POINT, [$qtiPoint1, $qtiPoint2])
        );
        $values = $responseVariable->getDataModelValues();

        $this->assertCount(2, $values);
        $this->assertTrue($values[0]->getValue()->equals($qtiPoint1));
        $this->assertTrue($values[1]->getValue()->equals($qtiPoint2));
    }

    public function testClone()
    {
        // value, default value and correct response must be independent after cloning.
        $responseVariable = new ResponseVariable('MYVAR', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(25));
        $responseVariable->setDefaultValue(new QtiInteger(1));
        $responseVariable->setCorrectResponse(new QtiInteger(1337));

        $clone = clone $responseVariable;
        $this->assertNotSame($responseVariable->getValue(), $clone->getValue());
        $this->assertNotSame($responseVariable->getDefaultValue(), $clone->getDefaultValue());
        $this->assertNotSame($responseVariable->getCorrectResponse(), $clone->getCorrectResponse());
    }
}
