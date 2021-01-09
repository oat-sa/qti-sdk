<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\rules\Ordering;
use qtismtest\QtiSmTestCase;

/**
 * Class OrderingMarshallerTest
 */
class OrderingMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $shuffle = true;
        $component = new Ordering($shuffle);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('true', $element->getAttribute('shuffle'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<ordering xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shuffle="false"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Ordering::class, $component);
        $this::assertEquals(false, $component->getShuffle());
    }
}
