<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\expressions\Correct;
use qtismtest\QtiSmTestCase;

/**
 * Class CorrectMarshallerTest
 */
class CorrectMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $identifier = 'myCorrect1';

        $component = new Correct($identifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('correct', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<correct xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myCorrect1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Correct::class, $component);
        $this->assertEquals($component->getIdentifier(), 'myCorrect1');
    }
}
