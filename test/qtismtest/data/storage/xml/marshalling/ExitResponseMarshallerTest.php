<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\rules\ExitResponse;
use qtismtest\QtiSmTestCase;

/**
 * Class ExitResponseMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class ExitResponseMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new ExitResponse();
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('exitResponse', $element->nodeName);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<exitResponse xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(ExitResponse::class, $component);
        $this->assertEquals('exitResponse', $component->getQtiClassName());
    }
}
