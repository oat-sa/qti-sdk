<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\ItemSessionControl;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\ItemSessionControlMarshaller;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtismtest\QtiSmTestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

/**
 * Class MarshallerTest
 */
class MarshallerTest extends QtiSmTestCase
{
    public function testCradle()
    {
        // Set cradle method accessible
        $class = new ReflectionClass(Marshaller::class);
        $method = $class->getMethod('getDOMCradle');
        $method->setAccessible(true);

        $this::assertInstanceOf(DOMDocument::class, $method->invoke(null));
    }

    public function testGetMarshaller()
    {
        $component = new ItemSessionControl();
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $this::assertInstanceOf(ItemSessionControlMarshaller::class, $marshaller);
    }

    public function testGetUnmarshaller()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<itemSessionControl xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" maxAttempts="1" validateResponses="true"/>');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($dom->documentElement);
        $this::assertInstanceOf(ItemSessionControlMarshaller::class, $marshaller);
    }

    public function testGetFirstChildElement()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<parent>some text <child/> <![CDATA[function() { alert("go!"); }]]></parent>');
        $element = $dom->documentElement;

        $child = Marshaller::getFirstChildElement($element);
        $this::assertInstanceOf(DOMElement::class, $child);
        $this::assertEquals('child', $child->nodeName);
    }

    public function testGetFirstChildElementNotFound()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<parent>some text <![CDATA[function() { alert("stop!"); }]]></parent>');
        $element = $dom->documentElement;

        $this::assertFalse(Marshaller::getFirstChildElement($element));
    }

    public function testGetChildElements()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<parent>some text <child/><anotherChild/> <![CDATA[function() { alert("go!"); }]]></parent>');
        $element = $dom->documentElement;

        $childElements = Marshaller::getChildElements($element);

        $this::assertIsArray($childElements);
        $this::assertCount(2, $childElements);
        $this::assertEquals('child', $childElements[0]->nodeName);
        $this::assertEquals('anotherChild', $childElements[1]->nodeName);
    }

    public function testGetChildElementsByTagName()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        // There are 3 child elements. 2 at the first level, 1 at the second.
        // We should find only 2 direct child elements.
        $dom->loadXML('<parent><child/><child/><parent><child/></parent></parent>');
        $element = $dom->documentElement;
        $marshaller = new FakeMarshaller('2.1.0');

        $this::assertCount(2, $marshaller->getChildElementsByTagName($element, 'child'));
    }

    public function testGetChildElementsByTagNameMultiple()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<parent><child/><child/><grandChild/><uncle/></parent>');
        $element = $dom->documentElement;
        $marshaller = new FakeMarshaller('2.1.0');

        $this::assertCount(3, $marshaller->getChildElementsByTagName($element, ['child', 'grandChild']));
    }

    public function testGetChildElementsByTagNameEmpty()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        // There is only 1 child but at the second level. Nothing
        // should be found.
        $dom->loadXML('<parent><parent><child/></parent></parent>');
        $element = $dom->documentElement;
        $marshaller = new FakeMarshaller('2.1.0');

        $this::assertCount(0, $marshaller->getChildElementsByTagName($element, 'child'));
    }

    public function testGetXmlBase()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<foo xml:base="http://forge.qtism.com"><bar>2000</bar><baz base="http://nowhere.com">fucked up beyond all recognition</baz></foo>');

        $foo = $dom->getElementsByTagName('foo')->item(0);
        $bar = $dom->getElementsByTagName('bar')->item(0);
        $baz = $dom->getElementsByTagName('baz')->item(0);

        $this::assertEquals('http://forge.qtism.com', Marshaller::getXmlBase($foo));
        $this::assertFalse(Marshaller::getXmlBase($bar));
        $this::assertFalse(Marshaller::getXmlBase($baz));
    }

    /**
     * @depends testGetXmlBase
     */
    public function testSetXmlBase()
    {
        $dom = new DOMDocument('1.0');
        $dom->loadXML('<foo><bar>2000</bar><baz>fucked up beyond all recognition</baz></foo>');

        $foo = $dom->getElementsByTagName('foo')->item(0);
        $bar = $dom->getElementsByTagName('bar')->item(0);
        $baz = $dom->getElementsByTagName('baz')->item(0);

        $this::assertFalse(Marshaller::getXmlBase($foo));
        $this::assertFalse(Marshaller::getXmlBase($bar));
        $this::assertFalse(Marshaller::getXmlBase($baz));

        Marshaller::setXmlBase($bar, 'http://my-new-base.com');

        $this::assertFalse(Marshaller::getXmlBase($foo));
        $this::assertEquals('http://my-new-base.com', Marshaller::getXmlBase($bar));
        $this::assertFalse(Marshaller::getXmlBase($baz));
    }

    public function testNoSuchMarshallerWhileUnmarshalling()
    {
        $dom = new DOMDocument('1.0');
        $dom->loadXML('<foo><bar>2000</bar><baz>fucked up beyond all recognition</baz></foo>');

        $dom2 = new DOMDocument('1.0');
        $dom2->loadXML('<baseValue baseType="boolean">true</baseValue>');
        $marshaller = $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($dom2->documentElement);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("No Marshaller implementation found while unmarshalling element ':foo'.");

        $marshaller->unmarshall($dom->documentElement);
    }

    public function testNoSuchMagicMethod()
    {
        $component1 = new BaseValue(BaseType::BOOLEAN, true);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unknown method Marshaller::'hello'.");

        $marshaller->hello();
    }
}
