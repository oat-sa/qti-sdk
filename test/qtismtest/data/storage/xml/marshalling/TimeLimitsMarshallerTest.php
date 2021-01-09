<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\datatypes\QtiDuration;
use qtism\data\TimeLimits;
use qtismtest\QtiSmTestCase;

/**
 * Class TimeLimitsMarshallerTest
 */
class TimeLimitsMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $minTime = new QtiDuration('PT50S');
        $maxTime = new QtiDuration('PT100S');

        $component = new TimeLimits($minTime, $maxTime);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('timeLimits', $element->nodeName);
        $this->assertEquals(50, $element->getAttribute('minTime'));
        $this->assertEquals(100, $element->getAttribute('maxTime'));
        $this->assertEquals('false', $element->getAttribute('allowLateSubmission'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<timeLimits xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" minTime="50" maxTime="100"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(TimeLimits::class, $component);
        $this->assertTrue($component->hasMinTime());
        $this->assertEquals('PT50S', $component->getMinTime() . '');
        $this->assertTrue($component->hasMaxTime());
        $this->assertEquals('PT1M40S', $component->getMaxTime() . '');
        $this->assertEquals(false, $component->doesAllowLateSubmission());
    }

    public function testUnmarshallZero()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<timeLimits xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" minTime="0" maxTime="0"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(TimeLimits::class, $component);
    }
}
