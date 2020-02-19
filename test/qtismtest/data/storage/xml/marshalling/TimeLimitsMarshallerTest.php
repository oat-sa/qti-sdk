<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\datatypes\QtiDuration;
use qtism\data\TimeLimits;
use qtismtest\QtiSmTestCase;

class TimeLimitsMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $minTime = new QtiDuration('PT50S');
        $maxTime = new QtiDuration('PT100S');

        $component = new TimeLimits($minTime, $maxTime);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf('\\DOMElement', $element);
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

        $this->assertInstanceOf('qtism\\data\\TimeLimits', $component);
        $this->assertTrue($component->hasMinTime());
        $this->assertEquals($component->getMinTime() . '', 'PT50S');
        $this->assertTrue($component->hasMaxTime());
        $this->assertEquals($component->getMaxTime() . '', 'PT1M40S');
        $this->assertEquals($component->doesAllowLateSubmission(), false);
    }

    public function testUnmarshallZero()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<timeLimits xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" minTime="0" maxTime="0"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf('qtism\\data\\TimeLimits', $component);
    }
}
