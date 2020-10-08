<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\expressions\RandomFloat;
use qtismtest\QtiSmTestCase;

/**
 * Class RandomFloatMarshallerTest
 */
class RandomFloatMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $min = 1;
        $max = '{tplVariable1}';
        $component = new RandomFloat($min, $max);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<randomFloat xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" min="1.3" max="{tplVariable1}"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(RandomFloat::class, $component);
        $this->assertEquals($component->getMin(), 1.3);
        $this->assertEquals($component->getMax(), '{tplVariable1}');
    }
}
