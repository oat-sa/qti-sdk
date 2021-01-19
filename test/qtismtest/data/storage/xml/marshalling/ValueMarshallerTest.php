<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\datatypes\QtiPair;
use qtism\common\enums\BaseType;
use qtism\data\state\Value;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class ValueMarshallerTest
 */
class ValueMarshallerTest extends QtiSmTestCase
{
    public function testMarshallBaseType()
    {
        $fieldIdentifier = 'goodIdentifier';
        $baseType = BaseType::INTEGER;
        $value = 666;

        $component = new Value($value, $baseType, $fieldIdentifier);
        $component->setPartOfRecord(true); // to get the baseType written in output.
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('value', $element->nodeName);
        $this::assertEquals($fieldIdentifier, $element->getAttribute('fieldIdentifier'));
        $this::assertEquals('integer', $element->getAttribute('baseType'));
        $this::assertEquals($value . '', $element->nodeValue);
    }

    public function testMarshallBaseTypeBoolean()
    {
        $fieldIdentifier = 'goodIdentifier';
        $baseType = BaseType::BOOLEAN;
        $value = false;

        $component = new Value($value, $baseType, $fieldIdentifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertSame('false', $element->nodeValue);
    }

    public function testMarshallNoBaseType()
    {
        $value = new QtiPair('id1', 'id2');

        $component = new Value($value);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertEquals('id1 id2', $element->nodeValue);
    }

    public function testUnmarshallNoBaseType()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">A B</value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Value::class, $component);
        $this::assertIsString($component->getValue());
        $this::assertEquals('A B', $component->getValue());
    }

    public function testUnmarshallNoBaseTypeButForced()
    {
        // Here we use the ValueMarshaller as a parametric marshaller
        // to force the Pair to be unserialized as a Pair object
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">A B</value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::PAIR]);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Value::class, $component);
        $this::assertInstanceOf(QtiPair::class, $component->getValue());
        $this::assertEquals('A', $component->getValue()->getFirst());
        $this::assertEquals('B', $component->getValue()->getSecond());
    }

    public function testUnmarshallNoBaseTypeButForcedAndEntities()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">Hello &lt;b&gt;bold&lt;/b&gt;</value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::STRING]);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Value::class, $component);
        $this::assertIsString($component->getValue());
        $this::assertSame('Hello <b>bold</b>', $component->getValue());
    }

    public function testMarshallNoBaseTypeButForcedAndEntities()
    {
        $value = 'Hello <b>bold</b>';
        $baseType = BaseType::STRING;
        $component = new Value($value, $baseType);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertSame('<value>Hello &lt;b&gt;bold&lt;/b&gt;</value>', $element->ownerDocument->saveXML($element));
    }

    public function testUnmarshallNoValueStringExpected()
    {
        // Just an empty <value>.
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"></value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::STRING]);
        $component = $marshaller->unmarshall($element);
        $this::assertEquals('', $component->getValue());

        // An empty <value>, with empty CDATA.
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"><![CDATA[]]></value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        $this::assertEquals('', $component->getValue());
    }

    public function testUnmarshallNoValueIntegerExpected()
    {
        $this->expectException(UnmarshallingException::class);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"></value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::INTEGER]);
        $component = $marshaller->unmarshall($element);
        $this::assertEquals('', $component->getValue());
    }

    public function testUnmarshallNoValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"></value>');
        $element = $dom->documentElement;
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertSame(-1, $component->getBaseType());
        $this::assertSame('', $component->getValue());
    }

    public function testUnmarshallStringBaseTypeWithNullValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="string"></value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        $this::assertSame(BaseType::STRING, $component->getBaseType());
        $this::assertSame('', $component->getValue());
    }

    public function testUnmarshallBaseTypePairWithFieldIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="pair" fieldIdentifier="fieldIdentifier1">A B</value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Value::class, $component);
        $this::assertInstanceOf(QtiPair::class, $component->getValue());
        $this::assertEquals('A', $component->getValue()->getFirst());
        $this::assertEquals('B', $component->getValue()->getSecond());
        $this::assertEquals('fieldIdentifier1', $component->getFieldIdentifier());
    }

    public function testUnmarshallBaseTypeInteger()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        // 0 value
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="integer">0</value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertSame(0, $component->getValue());

        // Positive value.
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="integer">1</value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertSame(1, $component->getValue());

        // Negative value.
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="integer">-1</value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertSame(-1, $component->getValue());
    }
}
