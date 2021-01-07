<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\Math;
use qtismtest\QtiSmTestCase;
use RuntimeException;

/**
 * Class MathMarshallerTest
 */
class MathMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $math = new Math('<m:math xmlns:m="http://www.w3.org/1998/Math/MathML"><m:mrow><m:mi>E</m:mi><m:mo>=</m:mo><m:mi>m</m:mi><m:msup><m:mi>c</m:mi><m:mn>2</m:mn></m:msup></m:mrow></m:math>');
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($math)->marshall($math);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<m:math xmlns:m="http://www.w3.org/1998/Math/MathML"><m:mrow><m:mi>E</m:mi><m:mo>=</m:mo><m:mi>m</m:mi><m:msup><m:mi>c</m:mi><m:mn>2</m:mn></m:msup></m:mrow></m:math>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <m:math xmlns:m="http://www.w3.org/1998/Math/MathML">
                <m:mrow>
                    <m:mi>E</m:mi>
                    <m:mo>=</m:mo>
                    <m:mi>m</m:mi>
                    <m:msup>
                        <m:mi>c</m:mi>
                        <m:mn>2</m:mn>
                    </m:msup>
                </m:mrow>
            </m:math>');

        $math = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(Math::class, $math);
        $xml = $math->getXml();
        $this->assertInstanceOf(DOMDocument::class, $xml);

        $mathElement = $xml->documentElement;
        $this->assertEquals('m', $mathElement->prefix);
        $this->assertEquals('http://www.w3.org/1998/Math/MathML', $mathElement->namespaceURI);
    }

    public function testGetXmlWrongNamespace()
    {
        $element = $this->createDOMElement('
	        <m:math xmlns:m="http://www.fruits.org/1998/Math/MathYoghourt">
                <m:mrow>
                    <m:mi>J</m:mi>
                    <m:mo>=</m:mo>
                    <m:mi>M</m:mi>
                </m:mrow>
            </m:math>');

        $math = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->expectException(RuntimeException::class);
        $xml = $math->getXml();
    }
}
