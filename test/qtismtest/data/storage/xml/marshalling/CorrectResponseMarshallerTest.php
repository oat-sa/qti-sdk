<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPair;
use qtism\common\enums\BaseType;
use qtism\data\state\CorrectResponse;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class CorrectResponseMarshallerTest
 */
class CorrectResponseMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $interpretation = 'It is up to you to interpret...';
        $pair = new QtiPair('id1', 'id2');
        $values = new ValueCollection();
        $values[] = new Value($pair);
        $component = new CorrectResponse($values, $interpretation);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('correctResponse', $element->nodeName);
        $this::assertEquals($interpretation, $element->getAttribute('interpretation'));
        $valueElements = $element->getElementsByTagName('value');
        $this::assertEquals(1, $valueElements->length);
        $valueElement = $valueElements->item(0);

        $this::assertEquals('value', $valueElement->nodeName);
        $this::assertEquals('id1 id2', $valueElement->nodeValue);
        $this::assertEquals('', $valueElement->getAttribute('baseType')); // no baseType attribute because not part of a record.
    }

    public function testUnmarshallOne()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<correctResponse xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" interpretation="My Interpretation">
				<value>25</value>
			</correctResponse>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::INTEGER]);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(CorrectResponse::class, $component);
        $this::assertEquals('My Interpretation', $component->getInterpretation());
        $this::assertCount(1, $component->getValues());

        $values = $component->getValues();
        $this::assertInstanceOf(Value::class, $values[0]);
        $this::assertEquals(BaseType::INTEGER, $values[0]->getBaseType());
        $this::assertFalse($values[0]->isPartOfRecord());
    }

    public function testUnmarshallTwo()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<correctResponse xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<value>A B</value>
				<value>C D</value>
			</correctResponse>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::DIRECTED_PAIR]);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(CorrectResponse::class, $component);
        $this::assertEquals('', $component->getInterpretation());
        $this::assertCount(2, $component->getValues());

        foreach ($component->getValues() as $value) {
            $this::assertInstanceOf(Value::class, $value);
            $this::assertEquals(BaseType::DIRECTED_PAIR, $value->getBaseType());
            $this::assertInstanceOf(QtiDirectedPair::class, $value->getValue());
            $this::assertFalse($value->isPartOfRecord());
        }
    }
}
