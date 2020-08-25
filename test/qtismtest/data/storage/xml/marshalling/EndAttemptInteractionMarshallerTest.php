<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\interactions\EndAttemptInteraction;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class EndAttemptInteractionMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class EndAttemptInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $endAttemptInteraction = new EndAttemptInteraction('BOOL_RESP', 'End the attempt now!', 'my-end', 'ending');
        $endAttemptInteraction->setXmlBase('/home/jerome');
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($endAttemptInteraction)->marshall($endAttemptInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<endAttemptInteraction id="my-end" class="ending" responseIdentifier="BOOL_RESP" title="End the attempt now!" xml:base="/home/jerome"/>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
            <endAttemptInteraction id="my-end" class="ending" responseIdentifier="BOOL_RESP" title="End the attempt now!" xml:base="/home/jerome"/>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(EndAttemptInteraction::class, $component);
        $this->assertEquals('my-end', $component->getId());
        $this->assertEquals('ending', $component->getClass());
        $this->assertEquals('BOOL_RESP', $component->getResponseIdentifier());
        $this->assertEquals('End the attempt now!', $component->getTitle());
        $this->assertEquals('/home/jerome', $component->getXmlBase());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoTitle()
    {
        $element = $this->createDOMElement('
            <endAttemptInteraction id="my-end" class="ending" responseIdentifier="BOOL_RESP"/>
        ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertEquals('', $component->getTitle());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <endAttemptInteraction id="my-end" class="ending"/>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'endAttemptInteraction' element.");

        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
